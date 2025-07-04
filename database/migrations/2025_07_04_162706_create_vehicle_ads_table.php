<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleAdsTable extends Migration
{
    public function up()
    {
        Schema::create('vehicle_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->integer('mileage');
            $table->string('fuel_type');
            $table->string('transmission_type');
            $table->string('city');
            $table->string('state');
            $table->string('registration_status');
            $table->boolean('is_modified')->default(false);
            $table->text('modification_details')->nullable();
            $table->text('engine_modification')->nullable();
            $table->text('exhaust_system')->nullable();
            $table->text('suspension')->nullable();
            $table->text('wheels_tires')->nullable();
            $table->text('brakes')->nullable();
            $table->text('body_kit')->nullable();
            $table->text('interior_upgrade')->nullable();
            $table->text('performance_tuning')->nullable();
            $table->text('electronics')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });

        Schema::create('vehicle_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_ad_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_attachments');
        Schema::dropIfExists('vehicle_ads');
    }
}