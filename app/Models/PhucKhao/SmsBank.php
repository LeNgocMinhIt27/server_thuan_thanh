<?php

namespace App\Models\PhucKhao;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsBank extends Model
{
    const INCLUDE = ['maThanhToan'];
    protected $table = 'pk_phuc_khaos';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'tin_nhan',
        'ngay_nhan',
        'gia',
        'lop_thi_id',
        'ma_thanh_toan',
        'trang_thai'
    ];

    protected $casts = [
        'ngay_nhan' => 'datetime'
    ];

    public function maThanhToan()
    {
        return $this->belongsTo(MaThanhToan::class, 'ma_thanh_toan', 'ma');
    }
}
