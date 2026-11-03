<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PickupRequest;
use Illuminate\Support\Facades\Auth;

class PickupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $pickups = PickupRequest::where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('client.pickups.index', compact('pickups'));
    }
}