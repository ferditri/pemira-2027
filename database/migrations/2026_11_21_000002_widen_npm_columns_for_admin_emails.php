<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('npm', 64)->change();
        });

        Schema::table('whitelists', function (Blueprint $table) {
            $table->string('npm', 64)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('npm', 11)->change();
        });

        Schema::table('whitelists', function (Blueprint $table) {
            $table->char('npm', 11)->change();
        });
    }
};
