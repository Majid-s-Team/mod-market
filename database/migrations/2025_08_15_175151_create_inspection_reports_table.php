<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_request_id')->constrained()->onDelete('cascade');
            $table->enum('engine_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('engine_description')->nullable();

            $table->enum('transmission_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('transmission_description')->nullable();

            $table->enum('braking_system_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('braking_system_description')->nullable();

            $table->enum('suspension_system_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('suspension_system_description')->nullable();

            $table->enum('interior_exterior_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('interior_exterior_description')->nullable();

            $table->enum('tyre_vehicle_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('tyre_vehicle_description')->nullable();

            $table->enum('computer_electronics_test', ['poor', 'average', 'good', 'excellent', 'perfect']);
            $table->text('computer_electronics_description')->nullable();

            $table->decimal('average_score', 5, 2)->default(0);
            $table->text('final_remarks')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};
