<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspection_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vehicle_ad_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_number');
            $table->foreignId('city_id')->constrained('vehicle_cities')->onDelete('cascade');
            $table->foreignId('state_id')->constrained('vehicle_states')->onDelete('cascade');
            $table->date('inspection_date')->nullable();
            $table->time('inspection_time')->nullable();
            $table->date('inspection_date_start')->nullable();
            $table->date('inspection_date_end')->nullable();
            $table->time('inspection_time_start')->nullable();
            $table->time('inspection_time_end')->nullable();
            $table->boolean('want_test_drive')->nullable();
            $table->text('description')->nullable();
            $table->decimal('inspector_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])->default('pending');
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->nullable();
            $table->enum('type', ['self', 'vendor']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_requests');
    }
};
