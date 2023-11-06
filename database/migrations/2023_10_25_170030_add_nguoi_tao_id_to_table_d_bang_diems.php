<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNguoiTaoIdToTableDBangDiems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_bang_diems', function (Blueprint $table) {
            $table->foreignId('nguoi_tao_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_bang_diems', function (Blueprint $table) {
            $table->dropForeign(['nguoi_tao_id']);
            $table->dropColumn('nguoi_tao_id');
        });
    }
}
