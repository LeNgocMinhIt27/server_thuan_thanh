<?php

namespace App\Models;

use App\Models\PhucKhao\PhucKhao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KiHoc extends Model
{
    use HasFactory;
    public $timestamps = false;
    const INCLUDE = ['phucKhao'];
    protected $table = 'ki_hocs';
    protected $fillable = [
        'name',
    ];
    public function phucKhao()
    {
        return $this->hasMany(PhucKhao::class, 'ki_hoc', 'name');
    }
}