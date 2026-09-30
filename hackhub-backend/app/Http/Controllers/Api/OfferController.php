<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentOffer;
use App\Models\Notification;
use App\Models\Project;
use App\Models\SavedProject;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    // Investor: offers they've sent
    public function sent(Request $request)
    {
        $offers = InvestmentOffer::with('project.team')
            ->where('investor_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($offers);
    }

    // Participant: offers received by their team's projects
    public function received(Request $request)
    {
        $teamIds = TeamMember::where('user_id', $request->user()->id)->pluck('team_id');
        $projectIds = Project::whereIn('team_id', $teamIds)->pluck('id');

        $offers = InvestmentOffer::with('investor', 'project')
            ->whereIn('project_id', $projectIds)
            ->latest()
            ->get();

        return response()->json($offers);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'company_name' => 'required|string|max:255',
            'offer_title' => 'required|string|max:255',
            'investment_amount' => 'nullable|numeric',
            'equity_percentage' => 'nullable|numeric',
            'internship_offer' => 'boolean',
            'job_offer' => 'boolean',
            'message' => 'nullable|string',
        ]);

        $data['investor_id'] = $request->user()->id;
        $offer = InvestmentOffer::create($data);

        // Notify every accepted member of the team that owns the project
        $project = Project::findOrFail($data['project_id']);
        $memberIds = TeamMember::where('team_id', $project->team_id)->where('status', 'accepted')->pluck('user_id');

        foreach ($memberIds as $memberId) {
            Notification::create([
                'user_id' => $memberId,
                'title' => 'New Investment Offer',
                'message' => $data['offer_title'] . ' from ' . $data['company_name'],
            ]);
        }

        return response()->json(['message' => 'Offer sent', 'offer' => $offer], 201);
    }

    // Participant accepts/rejects an offer for their team's project
    public function respond(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:accepted,rejected']);

        $offer = InvestmentOffer::findOrFail($id);
        $offer->update($data);

        return response()->json(['message' => 'Offer updated', 'offer' => $offer]);
    }

    public function save(Request $request, $projectId)
    {
        $saved = SavedProject::firstOrCreate([
            'investor_id' => $request->user()->id,
            'project_id' => $projectId,
        ]);

        return response()->json(['message' => 'Project saved', 'saved' => $saved]);
    }

    public function unsave(Request $request, $projectId)
    {
        SavedProject::where('investor_id', $request->user()->id)->where('project_id', $projectId)->delete();

        return response()->json(['message' => 'Project removed from saved list']);
    }

    public function savedList(Request $request)
    {
        $saved = SavedProject::with('project.team.members.user', 'project.hackathon')
            ->where('investor_id', $request->user()->id)
            ->get();

        return response()->json($saved);
    }

    // Investor dashboard summary
    public function dashboard(Request $request)
    {
        $investorId = $request->user()->id;

        return response()->json([
            'available_projects' => Project::count(),
            'new_submissions' => Project::where('created_at', '>=', now()->subDays(7))->count(),
            'interested_teams' => InvestmentOffer::where('investor_id', $investorId)->distinct('project_id')->count('project_id'),
            'offers_sent' => InvestmentOffer::where('investor_id', $investorId)->count(),
        ]);
    }
}
