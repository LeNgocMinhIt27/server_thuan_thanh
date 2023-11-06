<?php

namespace Database\Seeders;

use App\Constants\RoleCode;
use App\Models\Auth\User;
use App\Models\Diem\BangDiem;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\Lop\Lop;
use App\Models\Lop\LopThi;
use App\Models\User\GiaoVien;
use App\Models\User\SinhVien;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CreateTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'username' => 'troly@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::ASSISTANT,
            ],
            [
                'username' => 'sinhvien@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::STUDENT,
                'student' => [
                    'name' => "sinhvien",
                    'email' => 'sinhvien@hp.com',
                    'mssv' => '20230002'
                ]
            ],
            [
                'username' => 'giaovien@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::TEACHER,
                'giaovien' => [
                    'name' => "giaovien",
                    'email' => 'giaovien@hp.com',
                ]
            ],
            [
                'username' => 'student@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::STUDENT,
                'student' => [
                    'name' => "student",
                    'email' => 'student@hp.com',
                    'mssv' => '20230001',
                    'group' => 'Toán tin',
                    'birthday' => '2010-01-01'
                ]
            ],
            [
                'username' => 'student2@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::STUDENT,
                'student' => [
                    'name' => "student2",
                    'email' => 'student2@hp.com',
                    'mssv' => '20230002',
                    'group' => 'Toán tin',
                    'birthday' => '2010-02-01'
                ]
            ],
            [
                'username' => 'student3@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::STUDENT,
                'student' => [
                    'name' => "student3",
                    'email' => 'student3@hp.com',
                    'mssv' => '20230003',
                    'group' => 'Toán tin',
                    'birthday' => '2010-03-01'
                ]
            ],
            [
                'username' => 'student4@hp.com',
                'password' => bcrypt('12345678'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role_code' => RoleCode::STUDENT,
                'student' => [
                    'name' => "student4",
                    'email' => 'student4@hp.com',
                    'mssv' => '20230004',
                    'group' => 'Toán tin',
                    'birthday' => '2010-04-01'
                ]
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
        $lop = Lop::updateOrCreate(['ma' => '100000'], ['ma_hp' => 'MI1144', 'ten_hp' => 'Đại số tuyến tính', 'loai' => 'LT', 'ki_hoc' => '20223', 'phong' => 'Dữ liệu test']);
        $lop2 = Lop::updateOrCreate(['ma' => '100001', 'ma_kem' => '100000'], ['ma_hp' => 'MI1144', 'ten_hp' => 'Đại số tuyến tính', 'loai' => 'BT', 'ki_hoc' => '20223', 'phong' => 'Dữ liệu test']);
        $lop3 = Lop::updateOrCreate(['ma' => '100002', 'ma_kem' => '100000'], ['ma_hp' => 'MI1144', 'ten_hp' => 'Đại số tuyến tính', 'loai' => 'BT', 'ki_hoc' => '20223', 'phong' => 'Dữ liệu test']);
        $giao_vien = GiaoVien::where('email', 'giaovien@hp.com')->first();
        $lop->giaoViens()->sync([$giao_vien->getKey()]);
        $lop2->giaoViens()->sync([$giao_vien->getKey()]);
        $lop3->giaoViens()->sync([$giao_vien->getKey()]);
        $sinh_vien = SinhVien::where('email', 'student@hp.com')->first();
        $sinh_vien2 = SinhVien::where('email', 'student2@hp.com')->first();
        $sinh_vien3 = SinhVien::where('email', 'student3@hp.com')->first();
        $sinh_vien4 = SinhVien::where('email', 'student4@hp.com')->first();
        $lop->sinhViens()->sync([]);
        $lop2->sinhViens()->sync([
            $sinh_vien->getKey() => ['stt' => 1],
            $sinh_vien2->getKey() => ['stt' => 2]
        ]);
        $lop3->sinhViens()->sync([
            $sinh_vien3->getKey() => ['stt' => 1],
            $sinh_vien4->getKey() => ['stt' => 2]
        ]);
        $lop_thi2 = LopThi::updateOrCreate(['lop_id' => $lop2->getKey(), 'ma' => '100001-Nhóm 1'], ['loai' => 'GK', 'ngay_thi' => '2023-10-10', 'kip_thi' => '10h30-12h']);
        $lop_thi2->sinhViens()->sync([
            $sinh_vien->getKey() => ['stt' => 1],
            $sinh_vien2->getKey() => ['stt' => 2]
        ]);
        $lop_thi3 = LopThi::updateOrCreate(['lop_id' => $lop3->getKey(), 'ma' => '100001-Nhóm 2'], ['loai' => 'GK', 'ngay_thi' => '2023-10-10', 'kip_thi' => '10h-12h']);
        $lop_thi3->sinhViens()->sync([
            $sinh_vien3->getKey() => ['stt' => 1]
        ]);
        $now = Carbon::now();
        $ngay_ket_thuc_phuc_khao = $now->addDays(360);
        $bang_diem = BangDiem::updateOrCreate(['ma_hp' => 'MI1144', 'ten_hp' => 'Đại số tuyến tính', 'ki_hoc' => '20223'], ['duong_dan_tap_tin' => '', 'ngay_cong_khai' => Carbon::now()->subDay(), 'ngay_ket_thuc_phuc_khao' => $ngay_ket_thuc_phuc_khao, 'loai' => 'nhap_tay', 'ki_thi' => 'GK']);
        DiemNhanDienLopThi::updateOrCreate([
            'lop_thi_id' => $lop_thi2->getKey(),
            'bang_diem_id' => $bang_diem->getKey(),
        ]);
        Diem::updateOrCreate(['bang_diem_id' => $bang_diem->getKey(), 'lop_thi_id' => $lop_thi2->getKey(), 'sinh_vien_id' => $sinh_vien->getKey(), 'nguoi_nhap_id' => 1], ['diem' => 7]);
        Diem::updateOrCreate(['bang_diem_id' => $bang_diem->getKey(), 'lop_thi_id' => $lop_thi2->getKey(), 'sinh_vien_id' => $sinh_vien2->getKey(), 'nguoi_nhap_id' => 1], ['diem' => 8]);
    }
}