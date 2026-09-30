<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('offer_title');
            $table->decimal('investment_amount', 12, 2)->nullable();
            $table->decimal('equity_percentage', 5, 2)->nullable();
            $table->boolean('internship_offer')->default(false);
            $table->boolean('job_offer')->default(false);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_offers');
    }
};
