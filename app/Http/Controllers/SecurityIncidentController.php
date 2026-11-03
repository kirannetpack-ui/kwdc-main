<?php

namespace App\Http\Controllers;

use App\Models\SecurityIncident;
use App\Models\SecurityAgency;
use Illuminate\Support\Facades\Auth;

class SecurityIncidentController extends Controller
{
    protected function getAgency(): SecurityAgency
    {
        $agency = Auth::user()->securityAgency;

        if (!$agency) {
            abort(403, 'Security agency profile not found. Please complete your agency profile first.');
        }

        return $agency;
    }

    public function index(?SecurityAgency $agency = null)
    {
        $agency ??= $this->getAgency();

        $incidents = $agency->incidents()
            ->with(['warehouse', 'reportedBy', 'assignment'])
            ->latest('incident_time')
            ->paginate(10);

        return view('security.incidents.index', compact('agency', 'incidents'));
    }
}
