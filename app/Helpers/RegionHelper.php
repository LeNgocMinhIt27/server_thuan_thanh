<?php

namespace App\Helpers;

use App\Models\Region\District;
use App\Models\Region\Military;
use App\Models\Region\Province;
use Illuminate\Support\Facades\DB;

class RegionHelper
{
    public static function updateProvinceGeometry(Province $model)
    {
        // $model->update([
        //     'geometry' =>   DB::raw('foo.geometry'),
        //     'center_point' => DB::raw('foo.center_point FROM (select ST_AsGeoJSON(ST_Centroid(ST_union(geometry)))::json as center_point,ST_union(geometry) as geometry from public.districts where province_id = ' . $model->id . ') foo')
        // ]);
    }
    public static function updateDistrictGeometry(District $model)
    {
        // $info = [
        //     'geometry' =>  DB::raw('foo.geometry'),
        //     'center_point' => DB::raw('foo.center_point  FROM (select ST_AsGeoJSON(ST_Centroid(ST_union(geometry)))::json as center_point,ST_union(geometry) as geometry  from public.communes where district_id = ' . $model->id . ') foo')
        // ];
        // $model->update($info);
    }
}
