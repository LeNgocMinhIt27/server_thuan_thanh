<?php

namespace App\Models\Diem;

use App\Models\Lop\LopThi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemNhanDienLopThi extends Model
{
    use HasFactory;
    protected $table = 'd_diem_nhan_dien_lop_this';
    protected $fillable = [
        'lop_thi_id',
        'bang_diem_id',
        'duong_dan_anh',
        'page',
    ];
    public function lopThi()
    {
        return $this->belongsTo(LopThi::class, 'lop_thi_id', 'id');
    }
    public function bangDiem()
    {
        return $this->belongsTo(BangDiem::class);
    }
    public function diems()
    {
        return $this->hasMany(Diem::class, 'lop_thi_id', 'lop_thi_id');
    }
    public function getPagesAttribute()
    {
        if (!empty($this->page)) {
            return explode(",", $this->page);
        }
        return $this->page;
    }
}
