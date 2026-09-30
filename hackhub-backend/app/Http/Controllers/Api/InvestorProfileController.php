<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvestorProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->load('investorProfile'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string',
        ]);

        $user = $request->user();

        if (isset($data['name'])) {
            $user->update(['name' => $data['name']]);
            unset($data['name']);
        }

        $user->investorProfile->update($data);

        return response()->json(['message' => 'Profile updated', 'profile' => $user->investorProfile]);
    }
}
