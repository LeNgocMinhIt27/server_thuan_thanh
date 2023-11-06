<?php

namespace App\Models\User;

use App\Models\Auth\User;
use App\Models\Lop\LopGiaoVien;
use App\Models\Lop\LanDiemDanh;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;


class GiaoVien extends Model
{
    protected $table = 'u_giao_viens';
    protected $fillable = [
        'name',
        'email',
    ];

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'info');
    }
    public function lopGiaoVien()
    {
        return $this->hasMany(LopGiaoVien::class);
    }
}
