<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhLopThisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_lop_this', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lop_id')->constrained('ph_lops')->onDelete('cascade');
            $table->string('ma')->nullable();
            $table->string('loai')->nullable();
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
        Schema::dropIfExists('ph_lop_this');
    }
}
