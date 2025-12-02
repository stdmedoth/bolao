<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Consolida balance em game_credit (soma balance ao game_credit)
        DB::statement('UPDATE users SET game_credit = game_credit + balance WHERE balance > 0');
        
        // Remove a coluna balance
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recria a coluna balance
        Schema::table('users', function (Blueprint $table) {
            $table->float('balance')->default(0)->after('game_credit');
        });
        
        // Nota: Não podemos recuperar os valores originais de balance após consolidar
        // Os valores ficarão zerados se fizer rollback
    }
};
