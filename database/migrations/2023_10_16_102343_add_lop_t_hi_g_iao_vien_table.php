<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLopTHiGIaoVienTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('ph_lop_thi_giao_viens', function (Blueprint $table) {
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->foreignId('giao_vien_id')->constrained('u_giao_viens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ph_lop_thi_giao_viens');
    }
}
