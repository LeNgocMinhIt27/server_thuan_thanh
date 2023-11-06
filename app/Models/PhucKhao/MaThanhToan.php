<?php

namespace App\Models\PhucKhao;

use Illuminate\Database\Eloquent\Model;

class MaThanhToan extends Model
{
    public $timestamps = false;
    const INCLUDE = ['phucKhao', 'smsBank'];
    protected $table = 'pk_ma_thanh_toans';
    protected $fillable = [
        'ma',
        'trang_thai'
    ];
    protected $primaryKey = 'ma';
    public $incrementing = false;


    protected $casts = [
        'trang_thai' => 'boolean'
    ];

    public function phucKhao()
    {
        return $this->hasOne(PhucKhao::class, 'ma_thanh_toan', 'ma');
    }

    public function smsBank()
    {
        return $this->hasOne(PhucKhao::class, 'ma_thanh_toan', 'ma');
    }
}
