<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaLopFieldToPhDiemDanhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_diem_danhs', function (Blueprint $table) {
            $table->string('ma_lop')->nullable();
            $table->foreignId('lop_id')->nullable()->constrained('ph_lops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_diem_danhs', function (Blueprint $table) {
            $table->dropForeign(['lop_id']);
            $table->dropColumn('lop_id');
            $table->dropColumn('ma_lop');
        });
    }
}
