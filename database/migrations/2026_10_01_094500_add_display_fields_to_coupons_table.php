<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('title')->nullable()->after('code');
            $table->string('badge')->nullable()->after('title');
            $table->string('icon')->nullable()->after('badge');
            $table->string('color')->nullable()->after('icon');
            $table->text('description')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['title', 'badge', 'icon', 'color', 'description']);
        });
    }
};
