<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = ['hackathon_id', 'participant_id', 'status'];

    public function hackathon()
    {
        return $this->belongsTo(Hackathon::class);
    }

    public function participant()
    {
        return $this->belongsTo(User::class, 'participant_id');
    }
}
