<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'difficulty')) {
                $table->string('difficulty')->default('medium')->after('question_type_id');
            }
        });
    }

    public function down(): void {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'difficulty')) {
                $table->dropColumn('difficulty');
            }
        });
    }
};
