<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamp('visited_at')->index();
            $table->string('ip')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('device')->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->text('referrer')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
