<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('asset_types', function (Blueprint $table) {
            if (Schema::hasColumn('asset_types', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });
    }

    public function down()
    {
        Schema::table('asset_types', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_types', 'code')) {
                $table->string('code')->unique()->after('id');
            }
        });
    }
};
