<?php

namespace App\Models\Diem;

use App\Models\Auth\User;
use App\Models\Diem\BangDiem;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Model;

class Diem extends Model
{
    const INCLUDE = ['bangDiem', 'lopThi', 'sinhVien', 'user'];
    protected $table = 'ph_diems';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'bang_diem_id',
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
        return $this->belongsTo(User::class, 'sinh_vien_id', 'info_id');
    }
}
