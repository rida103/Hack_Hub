<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hackathon;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Organizer: view all project submissions for their hackathons
    public function index(Request $request)
    {
        $hackathonIds = Hackathon::where('organizer_id', $request->user()->id)->pluck('id');

        $query = Project::with(['team.members.user', 'hackathon'])->whereIn('hackathon_id', $hackathonIds);

        if ($request->has('hackathon_id')) {
            $query->where('hackathon_id', $request->hackathon_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    // Investor: browse all submitted projects, with tech/category filter
    public function browse(Request $request)
    {
        $query = Project::with(['team.members.user.participantProfile', 'hackathon']);

        if ($request->has('tech')) {
            $query->where('tech_stack', 'like', '%' . $request->tech . '%');
        }

        if ($request->has('search')) {
            $query->where('project_name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->paginate(9));
    }

    public function show($id)
    {
        $project = Project::with(['team.members.user.participantProfile', 'hackathon'])->findOrFail($id);

        return response()->json($project);
    }

    // Team leader submits the project
    public function store(Request $request)
    {
        $data = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'hackathon_id' => 'required|exists:hackathons,id',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tech_stack' => 'nullable|string',
            'github_url' => 'required|string',
            'readme' => 'nullable|string',
            'demo_video_link' => 'nullable|string',
            'presentation_link' => 'nullable|string',
        ]);

        $team = Team::where('leader_id', $request->user()->id)->findOrFail($data['team_id']);

        $data['submitted_at'] = now();
        $project = Project::updateOrCreate(['team_id' => $team->id], $data);

        return response()->json(['message' => 'Project submitted', 'project' => $project], 201);
    }

    // Participant: submission history for teams they belong to
    public function history(Request $request)
    {
        $teamIds = \App\Models\TeamMember::where('user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->pluck('team_id');

        $projects = Project::with('hackathon')->whereIn('team_id', $teamIds)->latest()->get();

        return response()->json($projects);
    }

    // Organizer accepts/rejects a submission with optional remarks
    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'remarks' => 'nullable|string',
        ]);

        $project = Project::findOrFail($id);
        $hackathon = Hackathon::where('organizer_id', $request->user()->id)->findOrFail($project->hackathon_id);

        $project->update($data);

        return response()->json(['message' => 'Project status updated', 'project' => $project]);
    }
}
