<?php

namespace App\Models\Lop;

use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemDanh extends Model
{
    use HasFactory;

    protected $table = 'ph_diem_danhs';
    protected $fillable = [
        'lan_diem_danh_id',
        'sinh_vien_id',
        'co_mat',
        'ghi_chu',
        'lop_id',
        'ma_lop'
    ];
    public function lanDiemDanh()
    {
        return $this->belongsTo(LanDiemDanh::class);
    }
    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class);
    }
    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }
}
