<?php

namespace App\Models\Lop;

use App\Models\Auth\User;
use App\Models\Diem\BangDiem;
use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Model;

class DiemPhucKhao extends Model
{
    const INCLUDE = ['bangDiem', 'lopThi', 'sinhVien', 'user'];
    protected $table = 'ph_diem_phuc_khaos';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'lop_thi_id',
        'sinh_vien_id',
        'diem',
        'ghi_chu',
        'nguoi_nhap_id'
    ];

    public function bangDiem()
    {
        return $this->belongsTo(BangDiem::class);
    }

    public function lopThi()
    {
        return $this->belongsTo(LopThi::class);
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'nguoi_nhap_id');
    }
}
