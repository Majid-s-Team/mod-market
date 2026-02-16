<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('contact_number')->nullable();
            $table->string('password');
            $table->boolean('is_term_accept')->default(false);
            $table->string('business_license_image')->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->decimal('service_rate', 8, 2)->nullable();
            $table->string('certificate')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expire_at')->nullable();
            $table->string('gateway_customer_id',200)->nullable();
            $table->string('gateway_connect_id',200)->nullable();
            $table->string('device_token')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('deviece_type')->nullable();
            $table->string('role')->default('user');
            $table->tinyIncrements('gateway_charges_enabled')->default(0);
            $table->tinyIncrements('gateway_payouts_enabled')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
