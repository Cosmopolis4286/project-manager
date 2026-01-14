<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Adiciona coluna de ordenação aos projetos.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table
                ->unsignedInteger('position')
                ->nullable()
                ->after('id');
        });

        /**
         * Preenche a posição inicial baseada no ID
         * para não quebrar ordenações existentes.
         */
        DB::table('projects')
            ->orderBy('id')
            ->select('id')
            ->get()
            ->each(function ($project, $index) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['position' => $index + 1]);
            });

        Schema::table('projects', function (Blueprint $table) {
            $table
                ->unsignedInteger('position')
                ->nullable(false)
                ->change();
        });
    }

    /**
     * Remove coluna de ordenação.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
