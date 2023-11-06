<?php

use App\Models\Diem\Diem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeToFloatToDiemColumnOfDiemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ph_diems', function (Blueprint $table) {
            DB::statement("CREATE OR REPLACE FUNCTION ISNUMERIC(text) RETURNS BOOLEAN AS $$
            DECLARE x NUMERIC;
            BEGIN
                x = $1::NUMERIC;
                RETURN TRUE;
            EXCEPTION WHEN others THEN
                RETURN FALSE;
            END;
            $$
            STRICT
            LANGUAGE plpgsql IMMUTABLE;");
            DB::statement("UPDATE ph_diems SET diem = NULL WHERE diem IS NOT NULL AND diem != '0' AND ISNUMERIC(diem) = '0'");
            DB::statement("ALTER TABLE ph_diems ALTER COLUMN diem TYPE float USING (diem::float);");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ph_diems', function (Blueprint $table) {
            DB::statement("DROP FUNCTION IF EXISTS ISNUMERIC");
        });
    }
}
