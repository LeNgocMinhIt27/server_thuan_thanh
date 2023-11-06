<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhLanDiemDanhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_lan_diem_danhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_id')->constrained('ph_lops')->onDelete('cascade');
            $table->integer('lan')->nullable();
            $table->timestamp('ngay_diem_danh')->nullable();
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
        Schema::dropIfExists('ph_lan_diem_danhs');
    }
}
