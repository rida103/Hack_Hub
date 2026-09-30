<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('hackathon_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->string('tech_stack')->nullable();
            $table->string('github_url');
            $table->longText('readme')->nullable();
            $table->string('demo_video_link')->nullable();
            $table->string('presentation_link')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
