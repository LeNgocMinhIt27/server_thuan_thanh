<?php

namespace App\Http\Controllers\Api;

use App\Helpers\System\DownloadFileHelper;
use App\Http\Controllers\Controller;
use App\Library\FormData\Writer\WriterFactory;
use App\Traits\ResponseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TempDiskHelper;
use App\Library\FormData\Writer\Custom\ExcelWriter;

class DownloadController extends Controller
{
    use ResponseType;
    public function downloadFile(Request $request, $key)
    {
        $cache = Cache::get('download-' . $key);
        if (empty($cache)) {
            abort(404, 'not-fount-key');
        }
        if (is_string($cache)) {
            $cache = [
                'path' => $cache,
                'file_name' => null
            ];
        }
        $path = $cache['path'];
        $is_full_path = $cache['is_full_path'] ?? false;
        $file_name = $cache['file_name'] ?? null;
        $delete_file_after_send = $cache['delete_file_after_send'] ?? true;
        $path = $is_full_path ? $path : TempDiskHelper::getPath($path);
        $response = response()->download($path, $file_name);
        if ($delete_file_after_send) {
            $response->deleteFileAfterSend();
        }
        return $response;
    }
    public function downloadExcel(Request $request)
    {
        $request->validate(['data' => 'required', 'headers' => 'required', 'name' => 'required']);
        $writer = new ExcelWriter();
        $file_name = $request->get('name');
        $info = $request->except(['excel_type', 'name', 'data', 'headers']);
        $full_name_file = DownloadFileHelper::getFileName($file_name, 'xlsx', $info);

        $path = $writer->write($request->data, $request->get('headers'), $file_name, [
            'is_origin' => true,
            'title' => $request->get('title'),
            'sheet_name' => 'Dữ liệu'
        ]);
        $builder = new DownloadFileHelper;
        $builder->setPath($path);
        $builder->setFileName($full_name_file);
        return $this->responseSuccess($builder->build());
    }
}
