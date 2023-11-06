<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDDiemNhanDiensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_diem_nhan_diens', function (Blueprint $table) {
            $table->foreignId('bang_diem_id')->constrained('d_bang_diems')->onDelete('cascade');
            $table->integer('page')->nullable();
            $table->string('mssv')->nullable();
            $table->string('stt')->nullable();
            $table->string('diem')->nullable();
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
        Schema::dropIfExists('d_diem_nhan_diens');
    }
}
