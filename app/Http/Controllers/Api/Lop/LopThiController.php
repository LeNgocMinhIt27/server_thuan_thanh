<?php

namespace App\Http\Controllers\Api\Lop;

use App\Constants\RoleCode;
use App\Http\Controllers\Controller;
use App\Jobs\SendMailTrongThiGV;
use App\Models\Lop\LopThi;
use Illuminate\Http\Request;
use App\Library\QueryBuilder\QueryBuilder;
use App\Library\QueryBuilder\Filters\AllowedFilter;
use App\Library\QueryBuilder\Filters\Custom\FilterRelation;
use App\Mail\MailNotifyTrongThiGV;
use App\Models\Diem\Diem;
use App\Models\Diem\DiemNhanDienLopThi;
use App\Models\Lop\LopGiaoVien;
use App\Models\Lop\LopThiGiaoVien;
use App\Models\User\GiaoVien;
use DB;
use Illuminate\Contracts\Validation\Rule;
use Mail;
use Validator;

class LopThiController extends Controller
{
    protected $includes = ['lop', 'sinhViens', 'lopThiSinhVien'];
    public function index(Request $request)
    {
        $query = LopThi::query()->with('lop', 'lopThiSinhVien');
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes)
            ->allowedFilters(['lop_id'])
            ->allowedSorts(['lop_id'])
            ->defaultSort('created_at')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->paginate()), 200, []);
    }

    public function LopThiFilter(Request $request)
    {
        $query = LopThi::query()->with('lop');
        $query = QueryBuilder::for($query, $request)->with('lop')
            ->allowedSearch('ma')
            ->defaultSort('id')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function show(Request $request, $id)
    {
        $query = LopThi::query();
        $query = QueryBuilder::for($query, $request)
            ->allowedIncludes($this->includes);
        return response()->json($query->findOrFail($id), 200, []);
    }
    public function indexAgGrid(Request $request)
    {
        $query = LopThi::query();
        $query = DB::query()->fromSub(function ($query) {
            $query->from('ph_lop_this')
                ->join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id');
            $query->orderBy('ph_lop_this.id');
            $query->select([
                'ph_lop_this.id',
                'ph_lop_this.lop_id',
                DB::raw('ph_lops.ma as ma_lop'), //lop
                DB::raw('ph_lop_this.ma as ma_lop_thi'), //lop thi
                'ph_lops.ma_hp',
                'ph_lops.ten_hp',
                'ph_lop_this.phong_thi',
                'ph_lops.ki_hoc',
                'ph_lop_this.loai',
                'ph_lop_this.ngay_thi',
                'ph_lop_this.kip_thi',
            ]);
        }, 'lop_this');
        $query = QueryBuilder::for($query, $request)
            ->allowedAgGrid([])
            ->defaultSort('id')
            ->allowedPagination();
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function store(Request $request)
    {
        $lopId = $request->input('ma');
        $loai = $request->input('loai');
        if (!empty($lopId) && !empty($loai)) {
            $exists = DB::table('ph_lop_this')
                ->where('ma', $lopId)
                ->where('loai', $loai)
                ->exists();
            if (!empty($exists)) {
                return response()->json(['message' => 'Mã lớp thi và đợt thi đã tồn tại'], 422);
            }
        }
        $listLoaiThi = ['GK', 'GK2', 'CK'];
        $request->validate(
            [
                'lop_id' => 'required|integer',
                'ma' => 'required',
                'loai' => [
                    'required',
                    function ($attribute, $value, $fail) use ($listLoaiThi) {
                        if (!in_array($value, $listLoaiThi)) {
                            $fail("Đợt thi không tồn tại.");
                        }
                    },
                ],
            ],
            [
                'required' => 'Hãy nhập thông tin cho trường :attribute',
            ],
            [
                'ma' => 'Mã lớp thi',
                'loai' => 'Đợt thi',
            ],
        );
        $data = $request->all();
        $result = LopThi::create($data);
        return $this->responseSuccess($result);
    }
    public function update(Request $request, $id)
    {
        $listLoaiThi = ['GK', 'GK2', 'CK'];
        $request->validate(
            [
                'lop_id' => [
                    'required',
                    'integer',
                ],
                'ma' => [
                    'required',
                    function ($attribute, $value, $fail) use ($id) {
                        $lop_thi = LopThi::findOrFail($id);
                        if ($lop_thi->ma != $value) {
                            $fail("Mã lớp thi không thể thay đổi.");
                        }
                    },
                ],
                'loai' => [
                    'required',
                    function ($attribute, $value, $fail) use ($listLoaiThi) {
                        if (!in_array($value, $listLoaiThi)) {
                            $fail("Đợt thi không tồn tại.");
                        }
                    },
                ],
            ],
            [
                'required' => 'Hãy nhập thông tin cho trường :attribute',
            ],
            [
                'ma' => 'Mã lớp thi',
                'loai' => 'Đợt thi',
            ],
        );

        $exists = DB::table('ph_lop_this')
            ->where('ma', $request->ma)
            ->where('loai', $request->loai)
            ->where('id', '<>', $id)
            ->exists();
        if (!empty($exists)) {
            return response()->json(['message' => 'Mã lớp thi và đợt thi đã tồn tại'], 422);
        }

        $data = $request->all();
        $lop_thi = LopThi::findOrFail($id);
        $result = $lop_thi->update($data);
        return $this->responseSuccess($result);
    }
    public function destroy($id)
    {
        $lop_thi = LopThi::findOrFail($id);
        $result  = $lop_thi->delete($lop_thi);
        return $this->responseSuccess($result);
    }
    public function LopThiMon(Request $request, $id)
    {
        $user = $request->user();
        $query = DiemNhanDienLopThi::join('ph_lop_this', 'ph_lop_this.id', '=', 'd_diem_nhan_dien_lop_this.lop_thi_id')
            ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id');

        if ($user->allow(RoleCode::TEACHER) && !$user->allow(RoleCode::ASSISTANT)) {
            $query->join('ph_lop_giao_viens', 'ph_lops.id', '=', 'ph_lop_giao_viens.lop_id')
                ->select(
                    'ph_lop_giao_viens.giao_vien_id',
                )
                ->where('ph_lop_giao_viens.giao_vien_id', $user->info->id);
        }
        $query->where('d_diem_nhan_dien_lop_this.bang_diem_id', $id)
            ->select(
                'd_diem_nhan_dien_lop_this.id',
                'ph_lop_this.ma',
                'ph_lop_this.loai',
                'ph_lops.ma_hp',
                'ph_lops.ki_hoc',
                'ph_lop_this.lop_id',
                DB::raw('ph_lop_this.id as lop_thi_id'),
                DB::raw('ph_lops.ma as ma_lop'),
                DB::raw('ph_lop_this.ma as ma_lop_thi')

            )->withCount([
                'diems',
                'diems as diem_count_not_null' => function ($query) {
                    $query->select(\DB::raw('count(*)'))
                        ->whereNotNull('diem');
                },
                'diems as diem_count_null' => function ($query) {
                    $query->select(\DB::raw('count(*)'))
                        ->whereNull('diem');
                }
            ]);
        $query = QueryBuilder::for($query, $request)
            ->defaultSort('id')
            ->allowedAgGrid()
            ->allowedFilters([AllowedFilter::custom('ph_lop_this', new FilterRelation('lopThi', 'id'))])
            ->allowedPagination();

        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function cacheLopThiMon($id)
    {
        $query = DiemNhanDienLopThi::query()->where('bang_diem_id', $id)->with('lopThi');
        return response()->json(new \App\Http\Resources\Items($query->get()), 200, []);
    }
    public function LoaiLopThi()
    {
        $loai_lop_thi = [
            [
                "title" => "Giữa kỳ",
                "value" => "GK"
            ],
            [
                "title" => "Giữa kỳ lần 2",
                "value" => "GK2"
            ], [
                "title" => "Cuối kỳ",
                "value" => "CK"
            ]
        ];
        return response()->json($loai_lop_thi);
    }

    public function lopThiGiaoVien($id)
    {
        $query = DB::table('ph_lops')
            ->join('ph_lop_giao_viens', 'ph_lops.id', '=', 'ph_lop_giao_viens.lop_id')
            ->join('ph_lop_this', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('ph_lop_giao_viens.giao_vien_id', '=', $id);
        $query->select([
            'ph_lop_this.id', 'ph_lop_this.lop_id', 'ph_lop_giao_viens.giao_vien_id',
            'ph_lops.ki_hoc', 'ph_lops.ma_hp', 'ph_lop_this.loai', 'ph_lop_this.ma', 'ph_lop_this.ngay_thi', 'ph_lop_this.phong_thi', 'ph_lop_this.kip_thi'
        ]);

        return $this->responseSuccess($query->get());
    }
    public function lopThiKi(Request $request)
    {
        $query = DB::query()->fromSub(
            function ($query) use ($request) {
                $query->from('ph_lop_this')
                    ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
                    ->leftJoin(DB::raw('(SELECT COUNT(sinh_vien_id) as sl_sinh_vien, lop_thi_id FROM ph_lop_thi_sinh_viens GROUP BY lop_thi_id) as c'), 'ph_lop_this.id', '=', 'c.lop_thi_id')
                    ->orderBy('ph_lop_this.phong_thi', 'asc')
                    ->orderBy('ph_lop_this.ma', 'asc');
                $query->select([
                    'ph_lop_this.loai',
                    'ph_lop_this.lop_id',
                    'ph_lop_this.id as lop_thi_id',
                    'ph_lops.ki_hoc',
                    'ph_lops.ma_hp',
                    'ph_lop_this.loai',
                    'ph_lop_this.ma',
                    'ph_lop_this.ngay_thi',
                    'ph_lop_this.phong_thi',
                    'ph_lop_this.kip_thi',
                    'c.sl_sinh_vien',
                ]);
                if (!empty($request['loai'])) {
                    $query->where('ph_lop_this.loai', $request['loai']);
                }
                if (!empty($request['ki_hoc'])) {
                    $query->where('ph_lops.ki_hoc', $request['ki_hoc']);
                }
                if (!empty($request['is_dai_cuong'])) {
                    if ($request['is_dai_cuong'] === 1) {
                        $query->where('ph_lops.is_dai_cuong', true);
                    } else {
                        $query->where('ph_lops.is_dai_cuong', false);
                    }
                }
            },
            'lop_this'
        );
        $query_gv = DB::query()->fromSub(
            function ($query) use ($request) {
                $query->from('ph_lop_thi_giao_viens')->join('u_giao_viens', 'u_giao_viens.id', '=', 'ph_lop_thi_giao_viens.giao_vien_id');
                $query->select(['u_giao_viens.id', 'u_giao_viens.name', 'u_giao_viens.email', 'ph_lop_thi_giao_viens.lop_thi_id']);
            },
            'lop_this'
        );
        $items = $query->get();
        $items_gv = [];
        foreach ($query_gv->get() as $key => $item_gv) {
            $items_gv[$item_gv->lop_thi_id][] = ['id' => $item_gv->id, 'name' => $item_gv->name, 'email' => $item_gv->email];
        }
        $items = array_map(function ($item) use ($items_gv) {
            if (isset($items_gv[$item->lop_thi_id])) {
                $item->giao_viens = $items_gv[$item->lop_thi_id];
            } else $item->giao_viens = [];
            return $item;
        }, $items->toArray());
        $items = collect($items);
        return response()->json(new \App\Http\Resources\Items($items), 200, []);
    }
    public function giaoVienTrongThiSave(Request $request)
    {
        $data = $request->all();
        $ki_hoc = $request->get('ki_hoc');
        $loai = $request->get('loai');
        $lop_this = LopThi::join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id')->where('ph_lop_this.loai', $data['loai'])->get(['ph_lop_this.id', 'ph_lop_this.ma'])->mapWithKeys(function ($item, $key) {
            return [$item['ma'] => $item['id']];
        });
        $lop_thi_gv = LopThiGiaoVien::join('ph_lop_this', 'ph_lop_this.id', '=', 'ph_lop_thi_giao_viens.lop_thi_id')
            ->join('ph_lops', 'ph_lops.id', '=', 'ph_lop_this.lop_id')
            ->where('ph_lops.ki_hoc', $ki_hoc)->where('ph_lop_this.loai', $loai);
        if ($lop_thi_gv->get()->toArray()) {
            $lop_thi_gv->delete();
        }
        foreach ($data['info'] as $item) {
            $giao_vien_id = $item['giao_vien_id'];
            foreach ($item['lop_thi'] as $lop_thi) {
                LopThiGiaoVien::create(["lop_thi_id" => $lop_this[$lop_thi['ma_lop_thi']], "giao_vien_id" => $giao_vien_id]);
            }
        }
    }
    public function giaoVienTrongThi(Request $request)
    {
        $data = $request->all();
        // $lop_this = LopThi::get(['id', 'ma'])->mapWithKeys(function ($item, $key) {
        //     return [$item['ma'] => $item['id']];
        // });
        // $title = $data['title'];
        // foreach ($data['info'] as $item) {
        //     // $lop_thi_id = $item->lop_thi;
        //     $giao_vien_id = $item['giao_vien_id'];
        //     $giao_vien_email = $item['email'];
        //     foreach ($item['lop_thi'] as $lop_thi) {
        //         LopThiGiaoVien::updateOrCreate(["lop_thi_id" => $lop_this[$lop_thi['ma_lop_thi']], "giao_vien_id" => $giao_vien_id]);
        //         $user_email = 'lvt888664@gmail.com';
        //         // dd($item['lop_thi']);
        //     }
        //     dd(1);
        //     Mail::to($user_email)->send(new MailNotifyTrongThiGV($item['lop_thi'], $item, $title));
        // }
        SendMailTrongThiGV::dispatch($data);
        return $this->responseSuccess();
    }

    public function lopCoiThiGiaoVienDetail(Request $request)
    {
        $ki_hoc = $request->get('ki_hoc');
        $giao_vien_id = $request->get('giao_vien_id');
        $loai = $request->get('loai');
        $query = LopThiGiaoVien::join('ph_lop_this', 'ph_lop_thi_giao_viens.lop_thi_id', '=', 'ph_lop_this.id')
            ->join('ph_lops', 'ph_lop_this.lop_id', '=', 'ph_lops.id')
            ->join('u_giao_viens', 'ph_lop_thi_giao_viens.giao_vien_id', '=', 'u_giao_viens.id')
            ->orderBy('ph_lop_this.phong_thi', 'asc')
            ->orderBy('ph_lop_this.ngay_thi', 'asc');
        $query->select(
            'u_giao_viens.name',
            'ph_lop_thi_giao_viens.giao_vien_id',
            'ph_lop_thi_giao_viens.lop_thi_id',
            'ph_lop_this.phong_thi',
            'ph_lop_this.kip_thi',
            'ph_lop_this.ngay_thi',
            'ph_lops.ki_hoc',
            'ph_lop_this.loai',
            DB::raw('ph_lop_this.ma as ma_lop_thi'),
            DB::raw('ph_lops.ma as ma_lop_hoc'),
            DB::raw('ph_lops.id as lop_id'),
            DB::raw('ph_lop_this.id as lop_thi_id')

        )->where('ph_lops.ki_hoc', $ki_hoc);
        if (!empty($loai)) {
            $query->where('ph_lop_this.loai', $loai);
        }
        if (!empty($giao_vien_id)) {
            $query->where('ph_lop_thi_giao_viens.giao_vien_id', $giao_vien_id);
        }
        if (!empty($request['ngay_thi'])) {
            $query->where('ph_lop_this.ngay_thi', $request['ngay_thi']);
        }
        if (!empty($request['kip_thi'])) {
            $query->where('ph_lop_this.kip_thi', $request['kip_thi']);
        }
        return $this->responseSuccess($query->get());
    }
}
