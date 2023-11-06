<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';
    protected $fillable = [
        'name',
        'status', //0-created, 1-processing, 2-completed, 3-failed, 4-cancelled
        'start_at',
        'end_at',
        'error',
        'job_id',
        'task_type',
        'user_id',
        'reference'
    ];
    protected $casts = [
        'status' => 'string',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'reference' => 'array'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getCauserDisplay()
    {
        return $this->name;
    }
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
