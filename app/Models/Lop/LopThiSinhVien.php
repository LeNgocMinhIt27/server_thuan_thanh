<?php

namespace App\Models\Lop;

use App\Models\User\SinhVien;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LopThiSinhVien extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'ph_lop_thi_sinh_viens';
    protected $fillable = [
        "lop_thi_id",
        "sinh_vien_id",
        "stt",
        "diem"
    ];
    public function lopThi()
    {
        return $this->belongsTo(LopThi::class, 'lop_thi_id');
    }
    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'sinh_vien_id');
    }
}
