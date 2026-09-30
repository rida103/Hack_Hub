<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    // Organizer: view participants registered for one of their hackathons
    public function index(Request $request, $hackathonId)
    {
        $query = Registration::with('participant.participantProfile')
            ->where('hackathon_id', $hackathonId);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('participant', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
            });
        }

        return response()->json($query->latest()->get());
    }

    public function approve(Request $request, $registrationId)
    {
        $registration = Registration::findOrFail($registrationId);
        $registration->update(['status' => 'approved']);

        return response()->json(['message' => 'Participant approved', 'registration' => $registration]);
    }

    public function reject(Request $request, $registrationId)
    {
        $registration = Registration::findOrFail($registrationId);
        $registration->update(['status' => 'rejected']);

        return response()->json(['message' => 'Participant rejected', 'registration' => $registration]);
    }

    // Participant: view / update own profile
    public function profile(Request $request)
    {
        return response()->json($request->user()->load('participantProfile'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'profile_picture' => 'nullable|string',
            'university' => 'nullable|string',
            'degree' => 'nullable|string',
            'skills' => 'nullable|string',
            'bio' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'github_url' => 'nullable|string',
            'linkedin_url' => 'nullable|string',
        ]);

        $user = $request->user();

        if (isset($data['name'])) {
            $user->update(['name' => $data['name']]);
            unset($data['name']);
        }

        $profile = $user->participantProfile;
        $profile->update($data);

        return response()->json(['message' => 'Profile updated', 'profile' => $profile]);
    }

    // Investor / Organizer: search participants by skill, university, name
    public function search(Request $request)
    {
        $query = \App\Models\User::where('role', 'participant')->with('participantProfile');

        if ($request->has('skill')) {
            $query->whereHas('participantProfile', function ($q) use ($request) {
                $q->where('skills', 'like', '%' . $request->skill . '%');
            });
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        return response()->json($query->get());
    }
}
