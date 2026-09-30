<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParticipantProfile;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'hackathon_id' => 'required|exists:hackathons,id',
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create([
            'hackathon_id' => $data['hackathon_id'],
            'name' => $data['name'],
            'leader_id' => $request->user()->id,
        ]);

        // Leader is automatically an accepted member
        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'status' => 'accepted',
        ]);

        return response()->json(['message' => 'Team created', 'team' => $team], 201);
    }

    // Teams the logged-in participant belongs to
    public function myTeams(Request $request)
    {
        $teamIds = TeamMember::where('user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->pluck('team_id');

        $teams = Team::with(['members.user.participantProfile', 'hackathon'])
            ->whereIn('id', $teamIds)
            ->get();

        return response()->json($teams);
    }

    public function show($id)
    {
        $team = Team::with(['members.user.participantProfile', 'hackathon', 'leader'])->findOrFail($id);

        return response()->json($team);
    }

    // Suggest teammates based on complementary/matching skills for a given hackathon
    public function suggestions(Request $request, $hackathonId)
    {
        $myProfile = $request->user()->participantProfile;
        $mySkills = $myProfile ? $myProfile->skills_array : [];

        // Everyone else registered as a participant, not already teamed up on this hackathon
        $alreadyTeamed = TeamMember::whereHas('team', function ($q) use ($hackathonId) {
            $q->where('hackathon_id', $hackathonId);
        })->pluck('user_id');

        $candidates = User::where('role', 'participant')
            ->where('id', '!=', $request->user()->id)
            ->whereNotIn('id', $alreadyTeamed)
            ->with('participantProfile')
            ->get();

        // Score candidates: shared skills score low priority, complementary (different) skills score higher,
        // since the goal is a well-rounded team. We simply rank by number of NEW skills they'd add.
        $ranked = $candidates->map(function ($user) use ($mySkills) {
            $theirSkills = $user->participantProfile ? $user->participantProfile->skills_array : [];
            $newSkills = array_diff($theirSkills, $mySkills);
            $user->match_score = count($newSkills);
            $user->complementary_skills = array_values($newSkills);
            return $user;
        })->sortByDesc('match_score')->values();

        return response()->json($ranked);
    }

    public function invite(Request $request)
    {
        $data = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $existing = TeamMember::where('team_id', $data['team_id'])->where('user_id', $data['user_id'])->first();
        if ($existing) {
            return response()->json(['message' => 'This user already has a pending or existing invite'], 409);
        }

        $member = TeamMember::create([
            'team_id' => $data['team_id'],
            'user_id' => $data['user_id'],
            'status' => 'invited',
        ]);

        \App\Models\Notification::create([
            'user_id' => $data['user_id'],
            'title' => 'Team Invite',
            'message' => 'You have been invited to join a team.',
        ]);

        return response()->json(['message' => 'Invite sent', 'member' => $member], 201);
    }

    // Invites for the logged-in participant
    public function myInvites(Request $request)
    {
        $invites = TeamMember::with('team.hackathon')
            ->where('user_id', $request->user()->id)
            ->where('status', 'invited')
            ->get();

        return response()->json($invites);
    }

    public function respondInvite(Request $request, $memberId)
    {
        $data = $request->validate(['status' => 'required|in:accepted,rejected']);

        $member = TeamMember::where('user_id', $request->user()->id)->findOrFail($memberId);
        $member->update(['status' => $data['status']]);

        return response()->json(['message' => 'Invite updated', 'member' => $member]);
    }

    public function leave(Request $request, $teamId)
    {
        $member = TeamMember::where('team_id', $teamId)->where('user_id', $request->user()->id)->firstOrFail();
        $member->delete();

        return response()->json(['message' => 'You left the team']);
    }

    // Team leader removes a member
    public function removeMember(Request $request, $teamId, $userId)
    {
        $team = Team::where('leader_id', $request->user()->id)->findOrFail($teamId);
        TeamMember::where('team_id', $team->id)->where('user_id', $userId)->delete();

        return response()->json(['message' => 'Member removed']);
    }
}
