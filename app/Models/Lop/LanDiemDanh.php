<?php

namespace App\Models\Lop;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\GiaoVien;

class LanDiemDanh extends Model
{
    use HasFactory;
    protected $table = 'ph_lan_diem_danhs';
    protected $fillable = [
        'lan',
        'lop_id',
        'ngay_diem_danh',
        'ngay_dong_diem_danh',
        'ngay_mo_diem_danh'

    ];
    // protected $hidden = [
    //     'ngay_dong_diem_danh',
    //     'ngay_mo_diem_danh',
    // ];
    protected $casts = [
        'ngay_dong_diem_danh' => 'date',
        'ngay_mo_diem_danh' => 'date',
    ];
    public function diemDanhs()
    {
        return $this->hasMany(DiemDanh::class);
    }
    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }

    public function getIsQuaHanAttribute()
    {
        if (empty($this->ngay_dong_diem_danh)) {
            return false;
        }
        return Carbon::now()->greaterThan($this->ngay_dong_diem_danh->endOfDay());
    }
}
