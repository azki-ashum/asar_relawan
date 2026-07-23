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
        Schema::table('users', function (Blueprint $table) {
            // Kolom password dibuat tidak wajib (nullable)
            $table->string('password')->nullable()->change();

            // ID unik dari Google, disebut 'sub' (subject)
            $table->string('google_sub')->nullable()->unique();

            // Tambahkan kolom role dengan tipe enum dan default 'user'
            $table->enum('role', ['user', 'admin'])->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan kolom password menjadi wajib jika migrasi di-rollback
            $table->string('password')->nullable(false)->change();

            // Hapus kolom yang ditambahkan
            $table->dropColumn(['google_sub', 'role']);
        });
    }
};
