<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('agent_code')->nullable()->unique()->after('user_id');
        });

        // Auto-generate codes for existing agents
        $agents = DB::table('agents')->orderBy('id')->get();
        foreach ($agents as $index => $agent) {
            DB::table('agents')->where('id', $agent->id)->update([
                'agent_code' => 'AGT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('agent_code');
        });
    }
};
