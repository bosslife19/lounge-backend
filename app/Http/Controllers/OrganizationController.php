<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function getOrganizations()
    {
        $organizations = \App\Models\Organization::where('status', 'approved')->get();

        return response()->json(['status' => true, 'organizations' => $organizations]);
    }
}
