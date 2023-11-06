<?php

namespace App\Models\Lop;

use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use DB;
use Illuminate\Database\Eloquent\Model;

class Lop extends Model
{
    use \Awobaz\Compoships\Compoships;
    const INCLUDE = ['giaoViens', 'sinhViens', 'lanDiemDanhs', 'children', 'parent'];
    protected $table = 'ph_lops';
    protected $fillable = [
        'ma',
        'ma_kem',
        'ma_hp',
        'ten_hp',
        'phong',
        'loai',
        'tuan_hoc',
        'ki_hoc',
        'ghi_chu',
        'tuan_hoc',
        'is_dai_cuong'
    ];
    protected $casts = [
        'is_dai_cuong' => 'boolean',
    ];
    public function giaoViens()
    {
        return $this->belongsToMany(GiaoVien::class, 'ph_lop_giao_viens')->withPivot('ghi_chu');
    }
    public function sinhViens()
    {
        return $this->belongsToMany(SinhVien::class, 'ph_lop_sinh_viens')->withPivot('stt', 'diem_y_thuc', 'nhom', 'diem')->orderByPivot('stt');
    }
    public function lanDiemDanhs()
    {
        return $this->hasMany(LanDiemDanh::class)->orderBy('lan');
    }
    public function lanDiemDanhMoiNhat()
    {
        return $this->hasOne(LanDiemDanh::class)->ofMany([
            'created_at' => 'max',
            'id' => 'max',
        ]);
    }
    public function children()
    {
        return $this->hasMany(Lop::class,  ['ma_kem', 'ki_hoc'], ['ma', 'ki_hoc'])->whereRaw('ma != ma_kem');
    }
    public function parent()
    {
        return $this->belongsTo(Lop::class, ['ma_kem', 'ki_hoc'], ['ma', 'ki_hoc']);
    }
    public function lopGiaoVien()
    {
        return $this->hasMany(LopGiaoVien::class);
    }
}
