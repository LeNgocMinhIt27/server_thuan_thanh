<?php

namespace App\Models\PhucKhao;

use App\Models\Diem\Diem;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use App\Models\KiHoc;

use Illuminate\Database\Eloquent\Model;

class PhucKhao extends Model
{
    const INCLUDE = ['sinhVien', 'lop', 'lopThi', 'maThanhToan'];
    protected $table = 'pk_phuc_khaos';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'sinh_vien_id',
        'ki_hoc',
        'lop_id',
        'lop_thi_id',
        'trang_thai',
        'ma_thanh_toan'
    ];

    public function sinhVien()
    {
        return $this->belongsTo(sinhVien::class);
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }

    public function lopThi()
    {
        return $this->belongsTo(LopThi::class);
    }

    public function maThanhToanInfo()
    {
        return $this->belongsTo(MaThanhToan::class, 'ma_thanh_toan', 'ma');
    }
    public function kiHoc()
    {
        return $this->belongsTo(KiHoc::class, 'ki_hoc', 'name');
    }
}
