<?php

namespace App\Models\User;

use App\Models\Auth\User;
use App\Models\Diem\Diem;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThiSinhVien;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SinhVien extends Model
{
    const INCLUDE = ['user', 'lops', 'lopThiSinhVien', 'diem'];
    protected $table = 'u_sinh_viens';
    protected $guard = [
        'id',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'name',
        'email',
        'mssv',
        'group',
        'birthday'
    ];
    protected $casts = [
        'birthday' => 'date',
    ];

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'info');
    }
    public function lops()
    {
        return $this->belongsToMany(Lop::class, 'ph_lop_sinh_viens')->withPivot('stt', 'diem_y_thuc');
    }
    public function lopThiSinhVien()
    {
        return $this->hasMany(LopThiSinhVien::class);
    }

    public function diem()
    {
        return $this->hasMany(Diem::class);
    }
}
