<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhLopGiaoViensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_lop_giao_viens', function (Blueprint $table) {
            $table->foreignId('lop_id')->constrained('ph_lops')->onDelete('cascade');
            $table->foreignId('giao_vien_id')->constrained('u_giao_viens')->onDelete('cascade');
            $table->text('ghi_chu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ph_lop_giao_viens');
    }
}
