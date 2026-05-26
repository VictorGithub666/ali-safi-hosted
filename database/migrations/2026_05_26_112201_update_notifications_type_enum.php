<?php
// database/migrations/2026_05_26_000000_update_notifications_type_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, convert ENUM to VARCHAR temporarily
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }

    public function down()
    {
        // Revert back to ENUM if needed
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', ['order', 'payment', 'system', 'promotion'])->change();
        });
    }
};