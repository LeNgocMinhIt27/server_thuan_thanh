<?php

namespace App\Models\Lop;

use App\Models\User\GiaoVien;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LopGiaoVien extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'ph_lop_giao_viens';
    protected $fillable = [
        "lop_id",
        "giao_vien_id",
        "ghi_chu",
    ];
    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }
    public function giaoVien()
    {
        return $this->belongsTo(GiaoVien::class);
    }
}
