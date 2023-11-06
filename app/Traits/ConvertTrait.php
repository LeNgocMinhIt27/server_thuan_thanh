<?php

namespace App\Traits;

trait ConvertTrait
{
    public function convert_vi_to_en($str)
    {
        return convert_vi_to_en($str);
    }
    public function convert_vi_to_vi($str)
    {
        //convert dấu

        $str = mb_convert_encoding($str, "utf-8");
        // \xcc\x83 - ngã
        // \xcc\x81 - sắc
        // \xcc\x80 - huyền
        // \xcc\x89 - hỏi
        // \xcc\xa3 - nặng
        // ư
        $str = preg_replace(
            [
                '/(\xc6\xb0\xcc\x83)/',
                '/(\xc6\xb0\xcc\x81)/',
                '/(\xc6\xb0\xcc\x80)/',
                '/(\xc6\xb0\xcc\x89)/',
                '/(\xc6\xb0\xcc\xa3)/',
            ],
            ['ữ', 'ứ', 'ừ', 'ử', 'ự'],
            $str
        );
        $str = preg_replace(
            [
                '/(\xc6\xb0\xcc\x83)/',
                '/(\xc6\xb0\xcc\x81)/',
                '/(\xc6\xb0\xcc\x80)/',
                '/(\xc6\xb0\xcc\x89)/',
                '/(\xc6\xb0\xcc\xa3)/',
            ],
            ['ữ', 'ứ', 'ừ', 'ử', 'ự'],
            $str
        );
        // ê
        $str = preg_replace(
            [
                '/(\xc3\xaa\xcc\x83)/',
                '/(\xc3\xaa\xcc\x81)/',
                '/(\xc3\xaa\xcc\x80)/',
                '/(\xc3\xaa\xcc\x89)/',
                '/(\xc3\xaa\xcc\xa3)/',
            ],
            ['ễ', 'ế', 'ề', 'ể', 'ệ'],
            $str
        );
        // ơ
        $str = preg_replace(
            [
                '/(\xc6\xa1\xcc\x83)/',
                '/(\xc6\xa1\xcc\x81)/',
                '/(\xc6\xa1\xcc\x80)/',
                '/(\xc6\xa1\xcc\x89)/',
                '/(\xc6\xa1\xcc\xa3)/',
            ],
            ['ỡ', 'ớ', 'ờ', 'ở', 'ợ'],
            $str
        );
        // ô
        $str = preg_replace(
            [
                '/(\xc3\xb4\xcc\x83)/',
                '/(\xc3\xb4\xcc\x81)/',
                '/(\xc3\xb4\xcc\x80)/',
                '/(\xc3\xb4\xcc\x89)/',
                '/(\xc3\xb4\xcc\xa3)/',
            ],
            ['ỗ', 'ố', 'ồ', 'ổ', 'ộ'],
            $str
        );
        // â
        $str = preg_replace(
            [
                '/(\xc3\xa2\xcc\x83)/',
                '/(\xc3\xa2\xcc\x81)/',
                '/(\xc3\xa2\xcc\x80)/',
                '/(\xc3\xa2\xcc\x89)/',
                '/(\xc3\xa2\xcc\xa3)/',
            ],
            ['ẫ', 'ấ', 'ầ', 'ẩ', 'ậ'],
            $str
        );
        // ắ
        $str = preg_replace(
            [
                '/(\xe1\xba\xaf\xcc\x83)/',
                '/(\xe1\xba\xaf\xcc\x81)/',
                '/(\xe1\xba\xaf\xcc\x80)/',
                '/(\xe1\xba\xaf\xcc\x89)/',
                '/(\xe1\xba\xaf\xcc\xa3)/',
            ],
            ['ẵ', 'ắ', 'ằ', 'ẳ', 'ặ'],
            $str
        );
        // U
        $str = preg_replace(
            [
                '/(\x55\xcc\x83)/',
                '/(\x55\xcc\x81)/',
                '/(\x55\xcc\x80)/',
                '/(\x55\xcc\x89)/',
                '/(\x55\xcc\xa3)/',
            ],
            ['Ũ', 'Ú', 'Ù', 'Ủ', 'Ụ'],
            $str
        );

        // i
        $str = preg_replace(
            [
                '/(\x69\xcc\x83)/',
                '/(\x69\xcc\x81)/',
                '/(\x69\xcc\x80)/',
                '/(\x69\xcc\x89)/',
                '/(\x69\xcc\xa3)/',
            ],
            ['ĩ', 'í', 'ì', 'ỉ', 'ị'],
            $str
        );
        // a
        $str = preg_replace(
            [
                '/(\x61\xcc\x83)/',
                '/(\x61\xcc\x81)/',
                '/(\x61\xcc\x80)/',
                '/(\x61\xcc\x89)/',
                '/(\x61\xcc\xa3)/',
            ],
            ['ã', 'á', 'à', 'ả', 'ạ'],
            $str
        );
        // u
        $str = preg_replace(
            [
                '/(\x75\xcc\x83)/',
                '/(\x75\xcc\x81)/',
                '/(\x75\xcc\x80)/',
                '/(\x75\xcc\x89)/',
                '/(\x75\xcc\xa3)/',
            ],
            ['ũ', 'ú', 'ù', 'ủ', 'ụ'],
            $str
        );


        $str = mb_convert_encoding($str, "utf-8");
        return $str;
    }
}
