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
        Schema::create('atis_audio_files', function (Blueprint $table) {
            $table->id();
            // Important information
            $table->string('icao', 4);
            $table->string('ident');
            $table->text('atis');
            // Zulu time
            $table->string('zulu')->nullable();
            // Download url and file name
            $table->string('url')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atis_audio_files');
    }
};
