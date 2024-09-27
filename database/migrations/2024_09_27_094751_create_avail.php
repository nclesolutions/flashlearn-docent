<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_guide_id');
            $table->unsignedBigInteger('student_id'); // Gebruik 'student_id' hier
            $table->timestamps();

            // Verwijzing naar study_guides tabel
            $table->foreign('study_guide_id')->references('id')->on('study_guides')->onDelete('cascade');
            // Verwijzing naar students tabel
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('availabilities');
    }

};
