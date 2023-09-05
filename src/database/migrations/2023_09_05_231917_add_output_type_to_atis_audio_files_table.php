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
        Schema::table('atis_audio_files', function (Blueprint $table) {
            $table->string('output_type')->default('mp3')->after('atis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atis_audio_files', function (Blueprint $table) {
            $table->dropColumn('output_type');
        });
    }
};
