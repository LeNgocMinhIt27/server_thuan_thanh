<?php

namespace App\Models\Diem;

use App\Models\Lop\Diem;
use App\Models\Lop\DiemPhucKhao;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BangDiem extends Model
{
    const INCLUDE = ['diemNhanhDien', 'diem', 'diemPhucKhao'];
    protected $table = 'd_bang_diems';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'ma_hp',
        'ten_hp',
        'ghi_chu',
        'ki_hoc',
        'ki_thi',
        'loai',
        'duong_dan_tap_tin',
        'trang_thai_nhan_dien',
        'ngay_cong_khai',
        'ngay_ket_thuc_phuc_khao',
        'meta',
        'nguoi_tao_id'
    ];
    protected $casts = [
        'meta' => 'array',
    ];

    protected $appends = ['isPhucKhao'];

    public function diemNhanhDien()
    {
        return $this->hasMany(DiemNhanDien::class);
    }

    public function diem()
    {
        return $this->hasMany(Diem::class);
    }

    public function diemPhucKhao()
    {
        return $this->hasMany(DiemPhucKhao::class);
    }

    public function getIsPhucKhaoAttribute()
    {
        return $this->ngay_ket_thuc_phuc_khao >= Carbon::now()->format('Y-m-d');
    }
}
