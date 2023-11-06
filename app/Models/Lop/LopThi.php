<?php

namespace App\Models\Lop;

use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LopThi extends Model
{
    const INCLUDE = ['giaoViens'];
    protected $table = 'ph_lop_this';
    protected $fillable = [
        "lop_id",
        "ma",
        "loai",
        'ngay_thi',
        'kip_thi',
        'phong_thi',
        'ki_hoc'
    ];
    protected $casts = [
        'ngay_thi' => 'date',
    ];
    public function lopThiSinhVien()
    {
        return $this->hasMany(LopThiSinhVien::class);
    }
    public function sinhViens()
    {
        return $this->belongsToMany(SinhVien::class, 'ph_lop_thi_sinh_viens')->withPivot('stt');
    }
    public function giaoViens()
    {
        return $this->belongsToMany(GiaoVien::class, 'ph_lop_thi_giao_viens');
    }
    public function lop()
    {
        return $this->belongsTo(Lop::class, 'lop_id');
    }
    public function diemNhanDienLopThi()
    {
        return $this->hasMany(DiemNhanDienLopThi::class);
    }
}
