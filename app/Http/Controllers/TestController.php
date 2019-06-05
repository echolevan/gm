<?php

namespace App\Http\Controllers;

use App\Libs\MateData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function gm()
    {

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


        $machines = $machines->map(function ($i) use ($o_name) {
            $app_version = Redis::get('app_version:' . $i->code);

            $temp = [];
            $temp['机器码'] = $i->code;
            $temp['设备名'] = $i->name;
            $temp['屏幕编号'] = $i->index_id;
            if ($i->organization_id == 0) {
                $temp['组织机构'] = '总部';
            } else {
                $temp['组织机构'] = $o_name[$i->organization_id] ?? '';
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

        switch ($status) {
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


    public function test()
    {
        $mateData = new MateData('cab9778341284786980f1f76ac5accbf', 'sa8b9p8nc3urnhkn197hi2syw81kibsa');
        return $mateData->auth('雷斐', '420114199204302813', '18571874104');
    }

    public function getData()
    {
        $str = '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';
        $str = base64_decode($str);
        $str = gzdecode($str);

        echo $str;
        exit;
        $mateData = new MateData('cab9778341284786980f1f76ac5accbf', 'sa8b9p8nc3urnhkn197hi2syw81kibsa');
        return $mateData->getData('SP190603161900000049', '6541226769232465920', '雷斐', '420114199204302813', '18571874104');
    }

    public function notifyUrl()
    {
        $post_data = file_get_contents("php://input");
        Log::info($post_data);
        return response(['msg' => true]);
    }

    public function test2()
    {
        $device_id = [862877030963613, 869152026492446];
        $device_id = implode(",",$device_id);

        $res = DB::table('washers as w')
            ->join(DB::raw("
            ( SELECT * FROM washer_report WHERE id IN ( SELECT MAX( id ) FROM washer_report where device_id in ($device_id) GROUP BY device_id ) )
             AS wrj"), function ($q) {
                $q->on('wrj.device_id', '=', 'w.device_id');
            })
            ->where('wrj.fault_text', '!=', '警告接触')
            ->where('wrj.fault_text', '!=', '')
            ->whereNotNull('wrj.fault_text')
//            ->whereIn('w.id', [267])
            ->select('w.*')
            ->get();
//        return $res;
        dd($res);
    }

}
