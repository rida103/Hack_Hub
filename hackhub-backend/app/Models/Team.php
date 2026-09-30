<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['hackathon_id', 'name', 'leader_id'];

    public function hackathon()
    {
        return $this->belongsTo(Hackathon::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }
}
