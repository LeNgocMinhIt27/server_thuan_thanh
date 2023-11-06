<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhDiemDanhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_diem_danhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lan_diem_danh_id')->constrained('ph_lan_diem_danhs')->onDelete('cascade');
            $table->foreignId('sinh_vien_id')->constrained('u_sinh_viens')->onDelete('cascade');
            $table->boolean('co_mat')->nullable();
            $table->text('ghi_chu');
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
        Schema::dropIfExists('ph_diem_danhs');
    }
}
