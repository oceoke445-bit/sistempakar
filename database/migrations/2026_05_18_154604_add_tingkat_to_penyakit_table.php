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
        if (Schema::hasColumn('penyakit', 'tingkat')) {
            return;
        }

        Schema::table('penyakit', function (Blueprint $table) {
            $table->string('tingkat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyakit', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });
    }
};
