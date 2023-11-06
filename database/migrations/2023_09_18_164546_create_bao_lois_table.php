<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaoLoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bao_lois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('u_sinh_viens')->onDelete('cascade');
            $table->string('ki_hoc');
            $table->foreignId('lop_id')->constrained('ph_lops')->onDelete('cascade');
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->string('tieu_de');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bao_lois');
    }
}
