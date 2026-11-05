<?php

namespace App\Http\Controllers;

use App\Models\EquipmentRequest;
use App\Models\Invoice;
use App\Models\Equipment;
use App\Models\Notification;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EquipmentRequestController extends Controller
{
    public function __construct(protected AIService $aiService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $query = EquipmentRequest::with(['client', 'equipment', 'assignedEquipment']);
        if ($user->role === 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role === 'equipment_owner') {
            $query->where(function ($scope) use ($user) {
                $scope->whereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $user->id))
                    ->orWhereHas('assignedEquipment', fn ($equipment) => $equipment->where('owner_id', $user->id));
            });
        } else {
            abort_unless($user->isAdmin(), 403);
        }
        $requests = $query->latest()->paginate(20);
        return view('equipment-requests.index', compact('requests'));
    }

    public function create()
    {
        abort_unless(Auth::user()->role === 'client' || Auth::user()->isAdmin(), 403);
        $equipment = Equipment::where('status', 'available')->get();
        return view('equipment-requests.create', compact('equipment'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'equipment_type' => 'required|string|max:255',
            'equipment_id' => 'nullable|integer|exists:equipment,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'special_requirements' => 'nullable|string|max:3000',
            'budget_range' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'commodity_name' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'loading_site' => 'nullable|string|max:500',
            'unloading_site' => 'nullable|string|max:500',
            'specific_equipment' => 'nullable|string|max:255',
        ]);
    }

    private function recordData(array $data): array
    {
        $notes = [];
        foreach (['commodity_name', 'weight', 'dimensions', 'loading_site', 'unloading_site', 'specific_equipment'] as $key) {
            if (isset($data[$key]) && $data[$key] !== '') $notes[] = str_replace('_', ' ', ucfirst($key)).': '.$data[$key];
            unset($data[$key]);
        }
        if (isset($data['budget'])) $data['budget_range'] = (string) $data['budget'];
        unset($data['budget']);
        $data['notes'] = implode("\n", $notes);
        return $data;
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'client' || Auth::user()->isAdmin(), 403);
        $data = $this->recordData($this->validated($request));
        if (!empty($data['equipment_id'])) {
            $equipment = Equipment::available()->findOrFail($data['equipment_id']);
            $data['assigned_equipment_id'] = $equipment->id;
        }
        $record = DB::transaction(function () use ($data) {
            $record = EquipmentRequest::create($data + ['client_id' => Auth::id(), 'status' => 'pending']);
            $recipients = User::where('is_admin', true)->orWhere('role', 'admin')->pluck('id');
            if ($record->equipment) $recipients->push($record->equipment->owner_id);
            if ($record->assignedEquipment) $recipients->push($record->assignedEquipment->owner_id);
            foreach ($recipients->filter()->unique() as $recipient) {
                Notification::create([
                    'user_id' => $recipient, 'notification_number' => 'EQ-'.Str::uuid(),
                    'type' => 'equipment_request', 'title' => 'Equipment request',
                    'message' => 'A new '.$record->equipment_type.' request is ready for review.',
                    'related_type' => EquipmentRequest::class, 'related_id' => $record->id,
                    'is_read' => false,
                ]);
            }
            return $record;
        });
        $url = route('equipment-requests.show', $record);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipment request created.', 'redirect_url' => $url], 201);
        }
        return redirect($url)->with('success', 'Equipment request created.');
    }

    public function show($id)
    {
        $request = EquipmentRequest::with(['client', 'equipment', 'assignedEquipment'])->findOrFail($id);
        $user = Auth::user();
        abort_unless($user->isAdmin() || $request->client_id === $user->id || $request->equipment?->owner_id === $user->id || $request->assignedEquipment?->owner_id === $user->id, 403);

        $availableEquipment = collect();
        if ($user->isAdmin()) {
            $availableEquipment = Equipment::available()->orderBy('type')->orderBy('name')->get();
        } elseif ($user->role === 'equipment_owner') {
            $availableEquipment = Equipment::available()
                ->where('owner_id', $user->id)
                ->orderBy('type')
                ->orderBy('name')
                ->get();
        }

        return view('equipment-requests.show', compact('request', 'availableEquipment'));
    }

    public function update(Request $request, $id)
    {
        $record = EquipmentRequest::where('client_id', Auth::id())->where('status', 'pending')->findOrFail($id);
        DB::transaction(function () use ($record, $request) {
            $locked = EquipmentRequest::whereKey($record->id)->where('status', 'pending')->lockForUpdate()->firstOrFail();
            $data = $this->recordData($this->validated($request));
            $locked->update($data + ['quoted_price' => null, 'assigned_equipment_id' => $data['equipment_id'] ?? null]);
        });
        return redirect()->route('equipment-requests.show', $record)->with('success', 'Request updated.');
    }

    public function destroy($id)
    {
        $record = EquipmentRequest::where('client_id', Auth::id())->where('status', 'pending')->findOrFail($id);
        $record->update(['status' => 'cancelled']);
        return redirect()->route('equipment-requests.index')->with('success', 'Request cancelled.');
    }

    public function acceptQuote(int $id)
    {
        DB::transaction(function () use ($id) {
            $record = EquipmentRequest::where('client_id', Auth::id())->lockForUpdate()->findOrFail($id);
            abort_unless($record->status === 'pending' && $record->quoted_price > 0 && $record->assigned_equipment_id, 409);
            $equipment = Equipment::lockForUpdate()->findOrFail($record->assigned_equipment_id);
            abort_unless($equipment->status === 'available' && $record->start_date?->gte(today()), 409);
            $this->requireAvailableDates($record, $equipment);

            $record->update(['status' => 'approved']);
            $invoice = Invoice::create([
                'invoice_number' => 'KWDC-EQ-'.$record->id,
                'order_type' => 'equipment',
                'order_id' => $record->id,
                'client_id' => $record->client_id,
                'user_id' => $record->client_id,
                'amount' => $record->quoted_price,
                'subtotal' => $record->quoted_price,
                'grand_total' => $record->quoted_price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'due_date' => $record->start_date,
                'payment_due_date' => $record->start_date,
                'notes' => 'Agreed equipment rental for '.$equipment->name,
            ]);

            if ($record->client?->email) {
                \Illuminate\Support\Facades\Mail::to($record->client->email)
                    ->queue((new \App\Mail\InvoiceGenerated($invoice))->afterCommit());
            }
        }, 3);

        return back()->with('success', 'Quote accepted. The booking invoice is available under Invoices.');
    }

    private function requireAvailableDates(EquipmentRequest $record, Equipment $equipment): void
    {
        $overlaps = EquipmentRequest::where('assigned_equipment_id', $equipment->id)
            ->whereKeyNot($record->id)
            ->whereIn('status', ['approved', 'fulfilled'])
            ->whereDate('start_date', '<=', $record->end_date)
            ->whereDate('end_date', '>=', $record->start_date)
            ->exists();

        abort_if($overlaps, 409, 'This equipment is already reserved for the requested dates.');
    }

    public function approve(Request $request, $id)
    {
        return $this->transition($id, 'pending', 'approved', $request);
    }
    public function reject($id) { return $this->transition($id, 'pending', 'rejected'); }
    public function fulfill($id) { return $this->transition($id, 'approved', 'fulfilled'); }
    public function return($id) { return $this->transition($id, 'fulfilled', 'returned'); }

    private function transition($id, string $from, string $to, ?Request $request = null)
    {
        DB::transaction(function () use ($id, $from, $to, $request) {
            $record = EquipmentRequest::lockForUpdate()->findOrFail($id);
            $user = Auth::user();
            $equipmentId = $record->assigned_equipment_id ?: $record->equipment_id;

            abort_unless($user->isAdmin() || $user->role === 'equipment_owner', 403);

            if ($to === 'approved') {
                $validated = $request?->validate([
                    'assigned_equipment_id' => 'required|integer|exists:equipment,id',
                    'quoted_price' => 'nullable|numeric|min:0.01|max:99999999.99',
                ]) ?? [];
                $equipmentId = (int) $validated['assigned_equipment_id'];
            }

            $equipment = $equipmentId ? Equipment::lockForUpdate()->find($equipmentId) : null;
            abort_unless($user->isAdmin() || $equipment?->owner_id === $user->id, 403);
            abort_unless($record->status === $from, 409, 'This request has already changed. Refresh and try again.');

            if ($to === 'approved') {
                abort_unless($equipment && $equipment->status === 'available', 422, 'Choose available equipment.');
                $this->requireAvailableDates($record, $equipment);
                $record->fill([
                    'equipment_id' => $equipment->id,
                    'assigned_equipment_id' => $equipment->id,
                ]);
                if (isset($validated['quoted_price'])) {
                    $record->quoted_price = $validated['quoted_price'];
                    $record->save();
                    return;
                }
                abort_if($record->quoted_price, 409, 'The client must accept the outstanding quote.');
            }

            if ($to === 'fulfilled') {
                abort_unless($equipment && $equipment->status === 'available', 422, 'Choose available equipment before fulfillment.');
                if ($record->quoted_price) {
                    abort_unless(Invoice::where('order_type', 'equipment')->where('order_id', $record->id)
                        ->where('payment_status', 'paid')->exists(), 409, 'The booking invoice must be paid before handover.');
                }
                $equipment->update(['status' => 'rented']);
            }
            if ($to === 'returned' && $equipment) $equipment->update(['status' => 'available']);
            $record->status = $to;
            $record->save();
        });
        return back()->with('success', 'Request updated.');
    }

    public function recommendEquipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'equipment_type' => 'required|string',
            'duration' => 'required|integer|min:1',
            'location' => 'required|string',
            'description' => 'required|string',
            'budget' => 'nullable|numeric|min:0',
            'commodity_name' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:255',
            'loading_site' => 'nullable|string|max:500',
            'unloading_site' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        $result = $this->aiService->recommendEquipmentForJob(
            $request->equipment_type,
            $request->duration,
            $request->location,
            $request->description,
            $request->budget,
            $request->commodity_name,
            $request->weight,
            $request->dimensions,
            $request->loading_site,
            $request->unloading_site
        );

        return response()->json([
            'success' => true,
            'recommendation' => $result
        ]);
    }
}
