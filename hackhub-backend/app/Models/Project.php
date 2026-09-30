<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'team_id', 'hackathon_id', 'project_name', 'description', 'tech_stack',
        'github_url', 'readme', 'demo_video_link', 'presentation_link',
        'status', 'remarks', 'submitted_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function hackathon()
    {
        return $this->belongsTo(Hackathon::class);
    }

    public function offers()
    {
        return $this->hasMany(InvestmentOffer::class);
    }
}
