<?php

namespace App\Models\Auth;

use App\Constants\RoleCode;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use App\Traits\Auth\RoleTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use RoleTrait;
    use Notifiable, HasApiTokens;
    protected static $ignoreChangedAttributes = ['password', 'updated_at', 'created_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password',  'username', 'inactive',
        'avatar_url', 'count_login', 'last_login_at', 'role_code', 'info_id', 'info_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot', 'role_code'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inactive' => 'boolean',
        'last_login_at' => 'datetime',
    ];
    protected $appends = ['roles'];
    public static function boot()
    {
        parent::boot();
    }
    public function isActive()
    {
        return !$this->inactive;
    }
    public function isSysAdmin()
    {
        return $this->username == 'administrator';
    }
    public function getCauserDisplay()
    {
        return $this->username;
    }
    public function info(): MorphTo
    {
        return $this->morphTo();
    }
    public function getIsGiaoVienAttribute($value)
    {
        return $this->info_type === (new GiaoVien())->getMorphClass() || $this->role_code == RoleCode::STUDENT;
    }
    public function getIsSinhVienAttribute($value)
    {
        return $this->info_type === (new SinhVien())->getMorphClass();
    }
    public function getRolesAttribute($value)
    {
        return explode(",", $this->role_code);
    }
}
