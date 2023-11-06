<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePkSmsBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pk_sms_banks', function (Blueprint $table) {
            $table->id();
            $table->text('tin_nhan')->nullable();
            $table->dateTime('ngay_nhan')->nullable();
            $table->integer('gia')->nullable();
            $table->string('ma_thanh_toan')->foreign('ma_thanh_toan')->references('ma')->on('pk_ma_thanh_toans')->onDelete('cascade');
            $table->string('trang_thai')->nullable();
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
        Schema::dropIfExists('pk_sms_banks');
    }
}
