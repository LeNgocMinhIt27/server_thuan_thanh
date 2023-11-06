<?php

namespace App\Models\TinNhan;
use Awobaz\Compoships\Compoships;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TinNhan extends Model
{
    use HasFactory;
    use Compoships;

    protected $table = 'pk_sms_banks';
    protected $fillable = [
        'tin_nhan',
        'ngay_nhan',
        'gia',
        'ma_thanh_toan',
        'trang_thai'
    ];
}
