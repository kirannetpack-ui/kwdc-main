<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Box;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class BoxController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $boxes = Box::with('warehouse')
            ->where(function ($query) use ($user) {
                if (($user->role ?? null) === 'admin') {
                    return;
                }

                $query->where('client_id', $user->id)
                    ->orWhereHas('warehouse', function ($warehouseQuery) use ($user) {
                        $this->whereWarehouseOwner($warehouseQuery, $user->id);
                    });
            })
            ->latest()
            ->paginate(20);
        
        return view('client.boxes.index', compact('boxes'));
    }
    
    public function create()
    {
        $warehouses = Warehouse::where('status', 'approved')->get();
        return view('client.boxes.create', compact('warehouses'));
    }
    
public function store(Request $request)
{
    $request->validate([
        'invoice_number' => 'required|string',
        'shipper_name' => 'required|string',
        'warehouse_id' => 'required|exists:warehouses,id',
        'total_boxes' => 'required|integer|min:1|max:100',
        'notes' => 'nullable|string',
        'invoice_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'packing_list_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'insurance_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'other_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);
    
    // Generate a unique batch ID for this batch
    $batchId = 'BATCH-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    // Handle file uploads
    $invoicePath = $request->file('invoice_document')->store('boxes/documents/invoices', 'private_uploads');
    $packingPath = null;
    $insurancePath = null;
    $otherPaths = [];
    
    if ($request->hasFile('packing_list_document')) {
        $packingPath = $request->file('packing_list_document')->store('boxes/documents/packing_lists', 'private_uploads');
    }
    if ($request->hasFile('insurance_document')) {
        $insurancePath = $request->file('insurance_document')->store('boxes/documents/insurance', 'private_uploads');
    }
    if ($request->hasFile('other_documents')) {
        foreach ($request->file('other_documents') as $file) {
            $otherPaths[] = $file->store('boxes/documents/others', 'private_uploads');
        }
    }
    
    $boxes = [];
    
    for ($i = 1; $i <= $request->total_boxes; $i++) {
        $boxNumber = $i;
        $uniqueBoxId = $batchId . '-BOX-' . str_pad($boxNumber, 3, '0', STR_PAD_LEFT);
        
        $qrData = json_encode([
            'batch' => $batchId,
            'box' => $boxNumber,
            'total' => $request->total_boxes,
            'warehouse_id' => $request->warehouse_id,
            'shipper' => $request->shipper_name,
            'invoice' => $request->invoice_number,
            'date' => now()->format('Y-m-d')
        ]);
        
        $box = Box::create([
            'batch_number' => $uniqueBoxId,  // Unique per box
            'qr_code' => hash('sha256', $qrData . $boxNumber . microtime()),
            'barcode' => 'BAR-' . $batchId . '-' . str_pad($boxNumber, 3, '0', STR_PAD_LEFT),
            'entry_date' => now(),
            'invoice_number' => $request->invoice_number,
            'shipper_name' => $request->shipper_name,
            'warehouse_id' => $request->warehouse_id,
            'total_boxes' => $request->total_boxes,
            'box_number' => $boxNumber,
            'status' => 'pending',
            'notes' => $request->notes,
            'client_id' => auth()->id(),
            'invoice_document' => $invoicePath,
            'packing_list_document' => $packingPath,
            'insurance_document' => $insurancePath,
            'other_documents' => json_encode($otherPaths),
        ]);
        
        $boxes[] = $box;
    }
    
    return redirect()->route('boxes.index')
        ->with('success', "{$request->total_boxes} boxes created successfully. Batch ID: {$batchId}");
}    

    public function getQR($id)
    {
        $box = $this->findAccessibleBox($id);
        
        return response()->json([
            'is_banned' => $box->isBanned(),
            'ban_reason' => $box->ban_reason,
            'banned_at' => $box->banned_at?->toIso8601String(),
            'qr_data' => $box->isBanned() ? null : $box->generateQRData(),
            'batch_number' => $box->batch_number,
            'box_number' => $box->box_number,
            'total_boxes' => $box->total_boxes,
            'invoice_number' => $box->invoice_number,
            'shipper_name' => $box->shipper_name,
            'warehouse_name' => $box->warehouse->name ?? 'N/A',
            'entry_date' => $box->entry_date->format('Y-m-d'),
            'status' => $box->isBanned() ? 'banned' : $box->status,
        ]);
    }
    
    public function track($id)
    {
        $box = $this->findAccessibleBox($id);
        return view('client.boxes.track', compact('box'));
    }

    public function banQR(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin'), Response::HTTP_FORBIDDEN);

        $request->validate([
            'ban_reason' => 'required|string|max:500',
        ]);

        $box = Box::findOrFail($id);
        $box->banQR($request->input('ban_reason'));

        return redirect()->back()->with('success', "Box QR code for {$box->batch_number} has been revoked and banned.");
    }

    public function unbanQR($id)
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin'), Response::HTTP_FORBIDDEN);

        $box = Box::findOrFail($id);
        $box->unbanQR();

        return redirect()->back()->with('success', "Box QR code for {$box->batch_number} has been restored and unbanned.");
    }

    
public function getDocuments($id)
{
    $box = $this->findAccessibleBox($id);

    return response()->json([
        'invoice_document' => $this->privateDocumentUrl($box->invoice_document),
        'packing_list_document' => $this->privateDocumentUrl($box->packing_list_document),
        'insurance_document' => $this->privateDocumentUrl($box->insurance_document),
        'other_documents' => collect($box->other_documents ?? [])
            ->filter()
            ->map(fn (string $path) => $this->privateDocumentUrl($path))
            ->values(),
    ]);
}
    public function printLabel($id)
{
    $box = $this->findAccessibleBox($id);
    $qrData = $box->generateQRData();
    
    return view('client.boxes.print', compact('box', 'qrData'));
}

    private function findAccessibleBox($id): Box
    {
        $box = Box::with('warehouse')->findOrFail($id);

        abort_unless($this->canAccessBox(auth()->user(), $box), Response::HTTP_FORBIDDEN);

        return $box;
    }

    private function canAccessBox($user, Box $box): bool
    {
        if (! $user) {
            return false;
        }

        if (($user->role ?? null) === 'admin') {
            return true;
        }

        if ((int) $box->client_id === (int) $user->id) {
            return true;
        }

        return (int) ($box->warehouse?->owner_id ?? 0) === (int) $user->id
            || (int) ($box->warehouse?->user_id ?? 0) === (int) $user->id;
    }

    private function privateDocumentUrl(?string $path): ?string
    {
        return $path ? route('documents.private.show', ['path' => $path]) : null;
    }

    private function whereWarehouseOwner($query, int $userId): void
    {
        $query->where('user_id', $userId);

        if (Schema::hasColumn('warehouses', 'owner_id')) {
            $query->orWhere('owner_id', $userId);
        }
    }

}
