<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ballots', function (Blueprint $table) {
            $table->unsignedTinyInteger('active_lock')->nullable()->default(1)->after('user_id');
        });

        DB::table('ballots')->whereNotNull('deleted_at')->update(['active_lock' => null]);
        DB::table('ballots')->whereNull('deleted_at')->update(['active_lock' => 1]);

        Schema::table('ballots', function (Blueprint $table) {
            $table->unique(['organization_id', 'user_id', 'active_lock'], 'ballots_org_user_active_unique');
            $table->dropUnique(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ballots', function (Blueprint $table) {
            $table->unique(['organization_id', 'user_id']);
            $table->dropUnique('ballots_org_user_active_unique');
            $table->dropColumn('active_lock');
        });
    }
};
