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
        Schema::create('api_fetches', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('external_id')->nullable()->index();
            $table->json('payload');
            $table->timestamp('fetched_at')->index();
            $table->timestamps();

            $table->index(['source', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_fetches');
    }
};
