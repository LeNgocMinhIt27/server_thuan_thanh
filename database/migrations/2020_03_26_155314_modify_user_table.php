<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //system
            $table->boolean('inactive')->default(false);
            //info
            $table->string('username')->unique()->nullable();
            $table->string('avatar_url')->nullable();
            $table->bigInteger('count_login')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->string('role_code');
            $table->integer('info_id')->nullable();
            $table->string('info_type')->nullable();
            $table->string('fts')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('inactive');
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->dropColumn('avatar_url');
            $table->dropColumn('count_login');
            $table->dropColumn('last_login_at');
            $table->dropColumn('role_code');
            $table->dropColumn('info_id');
            $table->dropColumn('info_type');
            $table->dropColumn('fts');
        });
    }
}
