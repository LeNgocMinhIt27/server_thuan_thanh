<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhLopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_lops', function (Blueprint $table) {
            $table->id();
            $table->string('ma')->unique();
            $table->string('ma_kem')->nullable();
            $table->string('ma_hp');
            $table->string('ten_hp');
            $table->text('phong')->nullable();
            $table->string('loai')->nullable();
            $table->string('ki_hoc');
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
        Schema::dropIfExists('ph_lops');
    }
}
