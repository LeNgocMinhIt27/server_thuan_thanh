<?php

namespace Database\Seeders;

use App\Constants\RoleCode;
use App\Models\Auth\User;
use App\Models\User\SinhVien;
use Hash;
use Illuminate\Database\Seeder;

class UpdateEmailSinhVienTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sinh_viens = SinhVien::where('email', 'ilike', '%sis.hust.edu.vn')->orWhereNull('email')->with('user')->get();
        foreach ($sinh_viens as $sinh_vien) {
                $email = $this->getEmail($sinh_vien);
                $sinh_vien->email = $this->getEmail($sinh_vien);
                $sinh_vien->save();
                if (empty($sinh_vien->user)) {
                    $user = User::create([
                        'username' => $email,
                        'password' => Hash::make('12345678'),
                        'role_code' => RoleCode::STUDENT,
                        "info_id" => $sinh_vien->getKey(),
                        'info_type' => $sinh_vien->getMorphClass()
                    ]);
                } else {
                    $sinh_vien->user->update([
                        'username' => $email,
                    ]);
                }
        }
    }
    public function getEmail($sinh_vien)
    {
        try {
            $mssv = $sinh_vien->mssv;
            if (strlen($mssv) == 8) {
                $mssv = substr($mssv, 2);
            }
            $name = $sinh_vien->name;
            $name = $this->convert_vi_to_en($name);
            $name = strtolower($name);
            $names = explode(" ", $name);
            $name = array_pop($names);
            $sub = '';
            foreach ($names as $item) {
                if (!empty($item))
                    $sub .= $item[0];
            }
            if (!empty($name) && strlen($mssv) == 6 && !empty($sub))
                return "$name.$sub$mssv@hp.com";
            return '';
        } catch (\Throwable $th) {
            dd($sinh_vien->name, $th->getMessage());
        }
    }
    public function convert_vi_to_en($str)
    {
        $str = preg_replace(
            [
                "/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|ầ|ạ)/",
                "/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/",
                "/(ì|í|ị|ỉ|ĩ|ì)/",
                "/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/",
                "/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/",
                "/(ỳ|ý|ỵ|ỷ|ỹ)/",
                "/(đ)/",
                "/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/",
                "/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/",
                "/(Ì|Í|Ị|Ỉ|Ĩ)/",
                "/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/",
                "/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/",
                "/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/",
                "/(Đ)/",
            ],
            [
                "a",
                "e",
                "i",
                "o",
                "u",
                "y",
                "d",
                "A",
                "E",
                "I",
                "O",
                "U",
                "Y",
                "D",
            ],
            $str
        );
        return $str;
    }
}
