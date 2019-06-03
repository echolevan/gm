<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function gm() {

        $o_ids = DB::table('organization_closure')
            ->where('ancestor', 8886)
            ->pluck('descendant')->toArray();

        $machines = DB::table('machines as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.store_user_id')
            ->whereIn('m.organization_id', $o_ids)
            ->select('m.*', 'u.mobile as u_mobile')
            ->get();

        $o_name = DB::table('organizations as o')
            ->leftJoin('organization_closure as oc', 'oc.ancestor', '=', 'o.id')
            ->whereIn('oc.descendant', $machines->pluck('organization_id')->toArray())
            ->select(DB::raw("GROUP_CONCAT(o.name ORDER BY oc.distance DESC) as name"), 'oc.descendant as id')
            ->groupBy('oc.descendant')
            ->get();

        $o_name = $o_name->groupBy('id')->map(function ($i) {
            return current($i->toArray())->name;
        });


        $machines = $machines->map(function ($i) use ($o_name){
            $app_version = Redis::get('app_version:'.$i->code);

            $temp = [];
            $temp['机器码'] = $i->code;
            $temp['设备名'] = $i->name;
            $temp['屏幕编号'] = $i->index_id;
            if ($i->organization_id == 0) {
                $temp['组织机构'] = '总部';
            } else {
                $temp['组织机构']  = $o_name[$i->organization_id] ?? '';
            }
            $temp['账号'] = $i->u_mobile;
            $temp['权限状态'] = $i->permission_status == '1' ? '允许' : '未允许';
            $temp['设备状态'] = $this->getStatusAttribute($i->status, $i->last_time_at);
            $temp['激活时间'] = $i->created_at;
            $temp['版本信息'] = $i->version_name . '/' . json_decode($app_version, true)['version_code'] ?? null;
            $temp['推送升级版本'] = $i->last_push_version_name . '/' . $i->last_push_version_code;
            $temp['推送补丁版本'] = $i->last_patch_version_name . '/' . $i->last_patch_version_name;
            $temp['品牌型号'] = $i->model;
            $temp['安卓系统版本'] = $i->os_version;
            return $temp;
        });

        dd($machines);
    }

    public function getStatusAttribute($status, $last_time_at)
    {
        $time = 60;
        $time = $time + 10;

        $status = (time() - Carbon::parse($last_time_at)->timestamp) > $time ? 3 : $status;

        switch ($status){
            case 1:
                return '播放';
                break;
            case 2:
                return '暂停';
                break;
            case 3:
                return '离线';
                break;
        }
    }
}
