<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hackathon extends Model
{
    protected $fillable = [
        'organizer_id', 'title', 'description', 'theme', 'start_date', 'end_date',
        'registration_deadline', 'prize_pool', 'rules', 'venue', 'banner_image',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
