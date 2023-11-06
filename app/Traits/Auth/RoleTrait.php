<?php

namespace App\Traits\Auth;

use App\Constants\RoleCode;
use App\Models\Auth\Role;

trait RoleTrait
{
    public function isTeacher()
    {
        return $this->is_giao_vien;
    }
    public function isStudent()
    {
        return $this->is_sinh_vien;
    }
    public function isAdmin()
    {
        return $this->role_code === RoleCode::ADMIN;
    }
    public function allow($code)
    {
        return str_contains($this->role_code, $code);
    }
    public function allowMultiple($codes, $needsAll = false)
    {
        if ($needsAll) {
            foreach ($codes as $code) {
                if (!str_contains($this->role_code, $code)) {
                    return false;
                }
            }

            return true;
        }
        foreach ($codes as $code) {
            if (str_contains($this->role_code, $code)) {
                return true;
            }
        }

        return false;
    }
}
