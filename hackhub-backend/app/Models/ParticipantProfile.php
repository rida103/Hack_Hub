<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantProfile extends Model
{
    protected $fillable = [
        'user_id', 'profile_picture', 'university', 'degree',
        'skills', 'bio', 'experience_level', 'github_url', 'linkedin_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: turn "React,Laravel,AI" into ["React", "Laravel", "AI"]
    public function getSkillsArrayAttribute()
    {
        return $this->skills ? array_map('trim', explode(',', $this->skills)) : [];
    }
}
