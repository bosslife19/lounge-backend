<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function getOrganizations()
    {
        $organizations = \App\Models\Organization::with('users')->where('status', 'approved')->get();

        return response()->json(['status' => true, 'organizations' => $organizations]);
    }
}
