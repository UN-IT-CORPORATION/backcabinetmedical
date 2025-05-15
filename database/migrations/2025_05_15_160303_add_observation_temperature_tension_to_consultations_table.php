<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->text('observation')->nullable()->after('total');
            $table->decimal('temperature', 5, 2)->nullable()->after('observation'); // ex. 37.50
            $table->string('tension', 15)->nullable()->after('temperature');        // ex. "120/80"
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['observation', 'temperature', 'tension']);
        });
    }
};
