<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhDiemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_diems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bang_diem_id')->constrained('d_bang_diems')->onDelete('cascade');
            $table->foreignId('lop_thi_id')->constrained('ph_lop_this')->onDelete('cascade');
            $table->foreignId('sinh_vien_id')->constrained('u_sinh_viens')->onDelete('cascade');
            $table->string('diem')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->foreignId('nguoi_nhap_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('ph_diems');
    }
}
