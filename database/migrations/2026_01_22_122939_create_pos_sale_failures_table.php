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
        Schema::create('inv_pos_sales_failures', function (Blueprint $table) {
            $table->id();
            $table->string('sync_batch_id')->nullable();
            $table->string('device_id')->nullable();
            $table->json('sale_data');
            $table->text('error_message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sale_failures');
    }
};
