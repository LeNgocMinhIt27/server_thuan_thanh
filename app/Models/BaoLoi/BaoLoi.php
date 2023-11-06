<?php

namespace App\Models\BaoLoi;

use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Model;

class BaoLoi extends Model
{
    const INCLUDE = ['sinhVien', 'lop', 'lopThi'];
    protected $table = 'bao_lois';
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
        'tieu_de',
        'ghi_chu',
        'trang_thai',
        'ly_do'
    ];


    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class);
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }

    public function lopThi()
    {
        return $this->belongsTo(LopThi::class);
    }
}
