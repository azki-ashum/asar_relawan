<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // add columns first (without type)
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('asset_id')->nullable()->after('room_id')->constrained('assets')->nullOnDelete();
            $table->foreignId('pic_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            // destination for asset bookings: structured JSON + short text
            $table->json('destination')->nullable()->after('purpose');
            $table->string('destination_text')->nullable()->after('destination');
            // store coordinates separately for faster geo queries / indexing
            $table->decimal('destination_lat', 10, 7)->nullable()->after('destination');
            $table->decimal('destination_lng', 10, 7)->nullable()->after('destination_lat');
            $table->json('asset_snapshot')->nullable()->after('status');
            $table->json('personnel')->nullable()->after('asset_snapshot');
            $table->unsignedTinyInteger('fuel_level_percent')->nullable()->after('personnel');
            
            $table->index(['destination_lat', 'destination_lng']);
            $table->index(['asset_id', 'start_at', 'end_at']);
        });

        // add `type` column using Schema builder (enum if supported by DB driver)
        Schema::table('bookings', function (Blueprint $table) {
            // make type nullable by default so existing rows remain unchanged
            $table->enum('type', ['room', 'asset'])->nullable()->after('id');
            $table->index('type');
        });
    }

    public function down()
    {
        // drop type and other columns via Schema builder
        Schema::table('bookings', function (Blueprint $table) {
            // drop indexes first
            $table->dropIndex(['type']);
            // drop destination coordinate index if exists
            $table->dropIndex(['destination_lat', 'destination_lng']);
            $table->dropIndex(['asset_id', 'start_at', 'end_at']);

            // then drop columns
            $table->dropColumn('type');
            $table->dropColumn(['destination_lat', 'destination_lng']);
            $table->dropColumn(['asset_id', 'pic_id', 'asset_snapshot', 'personnel', 'fuel_level_percent', 'destination', 'destination_text']);
        });
    }
};
