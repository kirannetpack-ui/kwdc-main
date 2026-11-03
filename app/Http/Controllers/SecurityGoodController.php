<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\SecurityGood;
use App\Models\SecurityAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityGoodController extends Controller
{
    protected function getAgency()
    {
        $agency = Auth::user()->securityAgency;

        if (!$agency) {
            abort(403, 'Security agency profile not found. Please complete your agency profile first.');
        }

        return $agency;
    }

    protected function notifyAgency(SecurityAgency $agency, string $type, string $title, string $message, ?SecurityGood $good = null): void
    {
        Notification::createNotification(
            $agency->user_id ?? Auth::id(),
            $type,
            $title,
            $message,
            $good?->id,
            SecurityGood::class
        );

        $emailAddress = $agency->email ?: ($agency->user?->email ?? Auth::user()->email ?? null);

        if ($emailAddress) {
            try {
                Mail::raw($message, function ($mail) use ($emailAddress, $title) {
                    $mail->to($emailAddress)->subject($title);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to send agency security good notification email.', [
                    'agency_id' => $agency->id,
                    'email' => $emailAddress,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function ensureAgencyOwnsGood(SecurityGood $good): SecurityAgency
    {
        $agency = $this->getAgency();

        abort_unless($good->agency_id === $agency->id, 403);

        return $agency;
    }

    public function index()
    {
        $agency = $this->getAgency();
        $goods = $agency->goods()->paginate(10);

        return view('security.goods.index', compact('goods'));
    }

    public function create()
    {
        return view('security.goods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'         => 'required|string|max:255',
            'category'          => 'required|string|max:100',
            'model'             => 'nullable|string|max:100',
            'specifications'    => 'nullable|string',
            'quantity_available'=> 'required|integer|min:0',
            'unit_price'        => 'required|numeric|min:0',
            'is_rental'         => 'nullable|boolean',
            'rental_rate_per_day'=> 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|max:2048',
            'status'            => 'nullable|in:available,rented,maintenance',
        ]);

        $agency = $this->getAgency();
        $good = $agency->goods()->create($validated);

        $this->notifyAgency(
            $agency,
            'security_good_created',
            'Security good added',
            "Good '{$good->item_name}' was added successfully to your inventory.",
            $good
        );

        return redirect()->route('security.goods.index')
            ->with('success', 'Security good added successfully.');
    }

    public function show(SecurityGood $good)
    {
        $this->ensureAgencyOwnsGood($good);

        return view('security.goods.show', compact('good'));
    }

    public function edit(SecurityGood $good)
    {
        $this->ensureAgencyOwnsGood($good);

        return view('security.goods.edit', compact('good'));
    }

    public function update(Request $request, SecurityGood $good)
    {
        $agency = $this->ensureAgencyOwnsGood($good);

        $validated = $request->validate([
            'item_name'         => 'required|string|max:255',
            'category'          => 'required|string|max:100',
            'model'             => 'nullable|string|max:100',
            'specifications'    => 'nullable|string',
            'quantity_available'=> 'required|integer|min:0',
            'unit_price'        => 'required|numeric|min:0',
            'is_rental'         => 'nullable|boolean',
            'rental_rate_per_day'=> 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|max:2048',
            'status'            => 'nullable|in:available,rented,maintenance',
        ]);

        $good->update($validated);

        $this->notifyAgency(
            $agency,
            'security_good_updated',
            'Security good updated',
            "Good '{$good->item_name}' was updated successfully.",
            $good
        );

        return redirect()->route('security.goods.index')
            ->with('success', 'Security good updated successfully.');
    }

    public function destroy(SecurityGood $good)
    {
        $agency = $this->ensureAgencyOwnsGood($good);
        $goodName = $good->item_name;
        $good->delete();

        $this->notifyAgency(
            $agency,
            'security_good_deleted',
            'Security good deleted',
            "Good '{$goodName}' was deleted from your inventory.",
            $good
        );

        return redirect()->route('security.goods.index')
            ->with('success', 'Security good deleted successfully.');
    }
}
