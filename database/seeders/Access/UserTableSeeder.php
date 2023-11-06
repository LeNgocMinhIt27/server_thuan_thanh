<?php

namespace Database\Seeders;

use App\Constants\RoleCode;
use App\Models\Auth\User;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use Carbon\Carbon as Carbon;
use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder.
 */
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        // User::truncate();
        $users = [
            [
                'username' => 'sysadmin@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::ADMIN
            ],
            [
                'username' => 'troly@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::ASSISTANT,
            ],
        ];

        foreach ($users as $user) {
            $check = User::where('username', $user['username'])->first();
            $sinhvien_info = $user['student'] ?? null;
            unset($user['student']);
            $giaovien_info = $user['giaovien'] ?? null;
            unset($user['giaovien']);
            if (!empty($check)) {
                $check->update($user);
            } else {
                $check = User::create($user);
            }
            if (isset($sinhvien_info)) {
                $sinhvien = SinhVien::updateOrCreate(['email' => $sinhvien_info['email']], $sinhvien_info);
                $check->update(['info_id' => $sinhvien->getKey(), 'info_type' => $sinhvien->getMorphClass()]);
            }
            if (isset($giaovien_info)) {
                $giaovien = GiaoVien::updateOrCreate(['email' => $giaovien_info['email']], $giaovien_info);
                $check->update(['info_id' => $giaovien->getKey(), 'info_type' => $giaovien->getMorphClass()]);
            }
        }
    }
}
