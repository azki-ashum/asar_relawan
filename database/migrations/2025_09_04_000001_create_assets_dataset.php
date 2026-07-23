<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // asset_types: small lookup table for asset type normalization
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. vehicle, electronic, room
            $table->string('display_name')->nullable();
            $table->timestamps();
        });

        // assets table with type_id FK to asset_types
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->nullable()->constrained('asset_types')->nullOnDelete();
            $table->string('name');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'retired'])->default('active');
            // vehicle quick columns (optional)
            $table->unsignedTinyInteger('current_fuel_percent')->nullable();
            $table->timestamps();

            // light indexes
            $table->index('type_id');
            $table->index('status');
            // unique name per type to avoid duplicates within same type
            $table->unique(['type_id', 'name']);
        });
    }

    public function down()
    {
    // drop assets first, then its parent types
    Schema::dropIfExists('assets');
    Schema::dropIfExists('asset_types');
    }
};
