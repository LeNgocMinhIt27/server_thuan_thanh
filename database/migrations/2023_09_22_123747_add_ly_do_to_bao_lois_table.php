<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLyDoToBaoLoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bao_lois', function (Blueprint $table) {
            $table->string('ly_do')->default('Khác')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bao_lois', function (Blueprint $table) {
            $table->dropColumn('ly_do');
        });
    }
}
