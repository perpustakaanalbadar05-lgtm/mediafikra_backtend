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
        Schema::table('books', function (Blueprint $table) {
            $table->string('penulis')->nullable();
            $table->string('isbn')->nullable();
            $table->string('tahun_terbit')->nullable();
            $table->integer('halaman')->nullable();
            $table->string('penerbit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['penulis', 'isbn', 'tahun_terbit', 'halaman', 'penerbit']);
        });
    }
};
