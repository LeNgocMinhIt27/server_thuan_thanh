<?php

namespace App\Models\Diem;

use Illuminate\Database\Eloquent\Model;

class DiemNhanDien extends Model
{
    const INCLUDE = ['bangDiem'];
    protected $table = 'd_diem_nhan_diens';
    protected $guard = [
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'bang_diem_id',
        'page',
        'mssv',
        'stt',
        'diem'
    ];
    public function bangDiem()
    {
        return $this->belongsTo(BangDiem::class);
    }
}
