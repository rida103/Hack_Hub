<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentOffer extends Model
{
    protected $fillable = [
        'investor_id', 'project_id', 'company_name', 'offer_title',
        'investment_amount', 'equity_percentage', 'internship_offer',
        'job_offer', 'message', 'status',
    ];

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
