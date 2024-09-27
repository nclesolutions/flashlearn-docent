<?php
// database/migrations/xxxx_xx_xx_create_availabilities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilitiesTable extends Migration
{
    public function up()
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->foreignId('study_guide_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->primary(['study_guide_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('availabilities');
    }
}
