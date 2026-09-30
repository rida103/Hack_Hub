<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['hackathon_id', 'organizer_id', 'title', 'message', 'type'];

    public function hackathon()
    {
        return $this->belongsTo(Hackathon::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
