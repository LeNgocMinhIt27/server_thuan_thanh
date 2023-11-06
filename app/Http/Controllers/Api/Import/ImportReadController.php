<?php

namespace App\Http\Controllers\Api\Import;

use App\Http\Controllers\Controller;
use App\Library\FormData\Reader\Reader;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Storage;

class ImportReadController extends Controller
{
    public function readExcel(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);
        $uploadedFile = $request->file('file');
        $folder = time();
        $path = Storage::disk('temp')->putFileAs(
            $folder,
            $uploadedFile,
            $uploadedFile->getClientOriginalName()
        );
        if (!$path) {
            abort(400, 'Không thế đọc được tập tin');
        }
        $reader = new Reader('excel', [Storage::disk('temp')->path($path)]);
        return $this->responseSuccess([
            'items' => $reader->getRecords(),
            'headers' => $reader->getFields(),
            'total' => $reader->getTotal()
        ]);
    }
    public function suggest(Request $request)
    {
        $request->validate(['type' => ['required', Rule::in(['giao-vien', 'sinh-vien', 'lop', "lop-thi", "lop-thi-sv"])]]);
        $suggest = [
            'giao-vien' => [
                'name' => ['Teacher', 'teacher', 'Name', 'name'],
                'email' => ['Email', 'email']
            ],
            'lop' => [
                "ma" => ["Mã lớp"],
                "ma_kem" => ["Mã lớp kèm"],
                "ma_hp" => ["Mã_HP", "Mã HP"],
                "ten_hp" => ["Tên_HP", "Tên học phần"],
                "loai" => ["Loại lớp"],
                "ghi_chu" => ["Ghi_chú", "Ghi chú"],
                "giao_vien_email" => ["Email"],
                "lop_thu" => ["Thứ"],
                "lop_thoigian" => ["Thời gian"],
                "lop_kip" => ["Kíp"],
                "lop_phong" => ["Phòng"],
                "tuan_hoc" => ["Tuần"]
            ],
            'sinh-vien' => [
                "ma_lop" => ["classid"],
                "ma_hp" => ["courseid"],
                "ten_hp" => ["name"],
                "sinh_vien_id" => ["StudentID"],
                "sinh_vien_name" => ["studentname"],
                "sinh_vien_lop" => ["groupname"],
                "sinh_vien_nhom" => ["studygroupname"],
                "sinh_vien_birthday" => ["birthdate"]
            ],
            'lop-thi' => [
                "ma_lop" => ["classid"],
                "nhom" => ["studyGroup"],
                "ma_lop_thi" => ["ExamID"],
                "loai" => ["termNote"],
                'ngay_thi' => ['Ngày'],
                'kip_thi' => ['Kíp thi'],
                'phong_thi' => ['Phòng thi'],
            ],

            'lop-thi-sv' => [
                "mssv" => ["StudentID"],
                "ma_lop" => ["classid"],
                "nhom" => ["studygroupname"],
                "ma_lop_thi" => ["ExamID"]
            ]
        ];
        return $suggest[$request->type];
    }
}
