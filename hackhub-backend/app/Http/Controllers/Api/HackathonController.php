<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hackathon;
use App\Models\Registration;
use Illuminate\Http\Request;

class HackathonController extends Controller
{
    // List all hackathons (used by participants browsing, and organizer's own list)
    public function index(Request $request)
    {
        $query = Hackathon::with('organizer')->withCount(['registrations', 'teams', 'projects']);

        if ($request->has('mine') && $request->user()->role === 'organizer') {
            $query->where('organizer_id', $request->user()->id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->paginate(9));
    }

    public function show($id)
    {
        $hackathon = Hackathon::with(['organizer', 'announcements'])
            ->withCount(['registrations', 'teams', 'projects'])
            ->findOrFail($id);

        return response()->json($hackathon);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before_or_equal:start_date',
            'prize_pool' => 'nullable|string',
            'rules' => 'nullable|string',
            'venue' => 'nullable|string',
            'banner_image' => 'nullable|string',
        ]);

        $data['organizer_id'] = $request->user()->id;
        $hackathon = Hackathon::create($data);

        return response()->json(['message' => 'Hackathon created', 'hackathon' => $hackathon], 201);
    }

    public function update(Request $request, $id)
    {
        $hackathon = Hackathon::where('organizer_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'theme' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'registration_deadline' => 'sometimes|required|date',
            'prize_pool' => 'nullable|string',
            'rules' => 'nullable|string',
            'venue' => 'nullable|string',
            'banner_image' => 'nullable|string',
        ]);

        $hackathon->update($data);

        return response()->json(['message' => 'Hackathon updated', 'hackathon' => $hackathon]);
    }

    public function destroy(Request $request, $id)
    {
        $hackathon = Hackathon::where('organizer_id', $request->user()->id)->findOrFail($id);
        $hackathon->delete();

        return response()->json(['message' => 'Hackathon deleted']);
    }

    // Participant registers for a hackathon
    public function register(Request $request, $id)
    {
        $hackathon = Hackathon::findOrFail($id);

        $existing = Registration::where('hackathon_id', $id)
            ->where('participant_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You already registered for this hackathon'], 409);
        }

        $registration = Registration::create([
            'hackathon_id' => $id,
            'participant_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Registered successfully', 'registration' => $registration], 201);
    }

    // Organizer: analytics for their dashboard
    public function analytics(Request $request)
    {
        $organizerId = $request->user()->id;
        $hackathonIds = Hackathon::where('organizer_id', $organizerId)->pluck('id');

        return response()->json([
            'total_events' => $hackathonIds->count(),
            'active_hackathons' => Hackathon::where('organizer_id', $organizerId)
                ->where('end_date', '>=', now())->count(),
            'registered_participants' => Registration::whereIn('hackathon_id', $hackathonIds)->count(),
            'total_teams' => \App\Models\Team::whereIn('hackathon_id', $hackathonIds)->count(),
            'submitted_projects' => \App\Models\Project::whereIn('hackathon_id', $hackathonIds)->count(),
            'pending_projects' => \App\Models\Project::whereIn('hackathon_id', $hackathonIds)->where('status', 'pending')->count(),
        ]);
    }
}
