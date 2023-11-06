<?php

namespace App\Models\Lop;

use App\Models\User\GiaoVien;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LopThiGiaoVien extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'ph_lop_thi_giao_viens';
    protected $fillable = [
        "lop_thi_id",
        "giao_vien_id",
    ];
    public function lopThi()
    {
        return $this->belongsToMany(LopThi::class);
    }
    public function giaoVien()
    {
        return $this->belongsToMany(GiaoVien::class);
    }
}
