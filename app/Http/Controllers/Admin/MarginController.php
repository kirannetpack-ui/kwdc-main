<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarginTier;
use Illuminate\Http\Request;

class MarginController extends Controller
{
    // No constructor – middleware applied via routes

    public function index()
    {
        $tiers = MarginTier::orderBy('min_amount')->get();
        return view('admin.margin-tiers.index', compact('tiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'margin_percentage' => 'required|numeric|min:0|max:100',
        ]);
        MarginTier::create($validated);
        return redirect()->route('admin.margin-tiers')->with('success', 'Tier added.');
    }

    public function update(Request $request, $id)
    {
        $tier = MarginTier::findOrFail($id);
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'margin_percentage' => 'required|numeric|min:0|max:100',
        ]);
        $tier->update($validated);
        return redirect()->route('admin.margin-tiers')->with('success', 'Tier updated.');
    }

    public function destroy($id)
    {
        $tier = MarginTier::findOrFail($id);
        $tier->delete();
        return redirect()->route('admin.margin-tiers')->with('success', 'Tier deleted.');
    }
}