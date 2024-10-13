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
        Schema::create('schedule_lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grade_id')->constrained(
                table: 'grades', indexName: 'lessons_grade_id'
            );
            $table->string('day', length: 10);
            $table->json('subjects');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_lessons');
    }
};
