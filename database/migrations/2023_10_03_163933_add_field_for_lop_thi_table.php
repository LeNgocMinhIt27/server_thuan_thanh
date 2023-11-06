<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldForLopThiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_lop_this', function (Blueprint $table) {
            $table->date('ngay_thi')->nullable();
            $table->string('kip_thi')->nullable();
            $table->string('phong_thi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_lop_this', function (Blueprint $table) {
            $table->dropColumn('ngay_thi');
            $table->dropColumn('kip_thi');
            $table->dropColumn('phong_thi');
        });
    }
}
