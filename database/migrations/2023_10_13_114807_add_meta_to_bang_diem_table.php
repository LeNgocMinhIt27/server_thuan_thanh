<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaToBangDiemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_bang_diems', function (Blueprint $table) {
            $table->json('meta')->nullable()->default(json_encode(['so_trang' => 0]));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_bang_diems', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
