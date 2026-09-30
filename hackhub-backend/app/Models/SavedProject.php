<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedProject extends Model
{
    protected $fillable = ['investor_id', 'project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
