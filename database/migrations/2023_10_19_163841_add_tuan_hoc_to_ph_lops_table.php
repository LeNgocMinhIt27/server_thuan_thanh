<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTuanHocToPhLopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_lops', function (Blueprint $table) {
            $table->string('tuan_hoc')->nullable();
            $table->boolean('is_dai_cuong')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_lops', function (Blueprint $table) {
            $table->dropIfExists('tuan_hoc');
            $table->dropIfExists('is_dai_cuong');
        });
    }
}
