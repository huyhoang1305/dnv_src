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
        Schema::create('daily_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date')->unique()->comment('Ngày ghi nhận truy cập');
            $table->unsignedInteger('page_views')->default(0)->comment('Tổng lượt xem trang');
            $table->unsignedInteger('unique_visitors')->default(0)->comment('Tổng lượt khách độc nhất');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_visits');
    }
};
