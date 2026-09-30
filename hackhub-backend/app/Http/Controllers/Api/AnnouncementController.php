<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Registration;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // Organizer: list announcements they've posted (optionally per hackathon)
    public function index(Request $request)
    {
        $query = Announcement::with('hackathon')->where('organizer_id', $request->user()->id);

        if ($request->has('hackathon_id')) {
            $query->where('hackathon_id', $request->hackathon_id);
        }

        return response()->json($query->latest()->get());
    }

    // Participant: all announcements from hackathons they registered for
    public function feed(Request $request)
    {
        $hackathonIds = Registration::where('participant_id', $request->user()->id)
            ->where('status', 'approved')
            ->pluck('hackathon_id');

        $announcements = Announcement::with('hackathon')
            ->whereIn('hackathon_id', $hackathonIds)
            ->latest()
            ->get();

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hackathon_id' => 'required|exists:hackathons,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $data['organizer_id'] = $request->user()->id;
        $announcement = Announcement::create($data);

        // Push a notification to every approved participant of that hackathon
        $participantIds = Registration::where('hackathon_id', $data['hackathon_id'])
            ->where('status', 'approved')
            ->pluck('participant_id');

        foreach ($participantIds as $participantId) {
            Notification::create([
                'user_id' => $participantId,
                'title' => $data['title'],
                'message' => $data['message'],
            ]);
        }

        return response()->json(['message' => 'Announcement posted', 'announcement' => $announcement], 201);
    }

    public function destroy(Request $request, $id)
    {
        $announcement = Announcement::where('organizer_id', $request->user()->id)->findOrFail($id);
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted']);
    }
}
