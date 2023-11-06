<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldForBangDiemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_bang_diems', function (Blueprint $table) {
            $table->string('loai')->default('nhan_dien')->nullable();
            $table->string('ki_thi')->default('GK')->nullable();
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
            $table->dropColumn('loai');
            $table->dropColumn('ki_thi');
        });
    }
}
