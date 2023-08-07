<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atis_audio_files', function (Blueprint $table) {
            $table->string('storage_location')->default('server')->after('file_name');
            // You can change the default value to 's3' if most files are stored in S3.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atis_audio_files', function (Blueprint $table) {
            $table->dropColumn('storage_location');
        });
    }
};
