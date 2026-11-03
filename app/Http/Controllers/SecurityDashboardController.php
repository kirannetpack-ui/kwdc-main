<?php

namespace App\Http\Controllers;

use App\Models\SecurityAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityDashboardController extends Controller
{
    /**
     * Get the security agency for the currently logged-in user.
     */
    protected function getAgency()
    {
        return Auth::user()->securityAgency;
    }

    /**
     * Display the security agency dashboard.
     */
    public function index()
    {
        $agency = $this->getAgency();

        if (!$agency) {
            return view('security.dashboard', [
                'agencyExists'      => false,
                'personnelCount'    => 0,
                'goodsCount'        => 0,
                'activeAssignments' => 0,
                'incidentsCount'    => 0,
                'totalAssignments'  => 0,
                'agency'            => null,
            ]);
        }

        $personnelCount    = $agency->personnel()->count();
        $goodsCount        = $agency->goods()->count();
        $activeAssignments = $agency->assignments()->where('status', 'active')->count();
        $totalAssignments  = $agency->assignments()->count();
        $incidentsCount    = $agency->incidents()->count();

        return view('security.dashboard', array_merge(compact(
            'agency',
            'personnelCount',
            'goodsCount',
            'activeAssignments',
            'totalAssignments',
            'incidentsCount'
        ), ['agencyExists' => true]));
    }
}