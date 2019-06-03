<?php

namespace App\Events;

use App\Mail\GMMachineBind;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Rap2hpoutre\FastExcel\FastExcel;

class SendEmailToGM
{

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->go();
    }

    protected function go()
    {
//        $day = Carbon::now()->subDay()->toDateString();

//        $items = $this->getCacheForCollect("machine_first_create_time:40*");

//        $yesterday_machines = [];
//        $organization_ids = [];

//        foreach ($items as $k => $v) {
//            $v = json_decode($v, true);
//            if ($v['date'] >= $day . ' 00:00:00' && $v['date'] <= $day . ' 23:59:59') {
//                $yesterday_machines[] = $v;
//            }
//            $organization_ids[] = $v['organization_id'];
//        }
//        unset($v);

        $shops = DB::table('shops')
            ->where('user_id', 40)
            ->select('id', 'name')
            ->get()
            ->toArray();

//        $all_machines = DB::table('machines')
//            ->where('user_id', 40)
//            ->select('organization_id', 'shop_id')
//            ->distinct('organization_id')
//            ->get()
//            ->toArray();

//        $yesterday_machines = collect($yesterday_machines)
//            ->groupBy('organization_id')->map(function ($i) use ($shops, $all_machines) {
//                $temp = [];
//                $temp['门店名称'] = $i[0]['organization_name'];
//                $temp['所属店型'] = $this->getShopName($i[0]['organization_id'], $shops, $all_machines);
//                $temp['已绑定屏幕数量'] = count($i);
//                $temp['激活时间'] = date('Y-m-d', strtotime($i[0]['date']));
//                return $temp;
//            })->values();

//        $data = $yesterday_machines;

        // 实时数据
        $on_time_machines = DB::table('machines as m')
            ->leftJoin('organizations as o', 'o.id', '=', 'm.organization_id')
            ->where('m.user_id', 40)
            ->select('m.id', 'm.organization_id', 'o.name as name', 'm.created_at', 'm.shop_id')
            ->get();

        $o_name = DB::table('organizations as o')
            ->leftJoin('organization_closure as oc', 'oc.ancestor', '=', 'o.id')
            ->whereIn('oc.descendant', $on_time_machines->pluck('organization_id')->toArray())
            ->select(DB::raw("GROUP_CONCAT(o.name ORDER BY oc.distance DESC) as name"), 'oc.descendant as id')
            ->groupBy('oc.descendant')
            ->get();

        $o_name = $o_name->groupBy('id')->map(function ($i) {
            return current($i->toArray())->name;
        });

        $on_time_machines = $on_time_machines
            ->groupBy('organization_id')->map(function ($i) use ($shops, $o_name) {
                $temp = [];
                if ($i[0]->organization_id == 0) {
                    $temp['机构'] = '总部';
                } else {
                    $name = explode(',', $o_name[$i[0]->organization_id] ?? '');
                    $temp['机构'] = implode(',', array_slice($name, 0, 3));
                }
                $temp['门店名称'] = $i[0]->name;
                $temp['所属店型'] = $this->getShopName2($i[0]->shop_id, $shops);
                $temp['已绑定屏幕数量'] = count($i);
                $temp['激活时间'] = date('Y-m-d', strtotime($i[0]->created_at));
                return $temp;
            })->sortBy('机构')->values();

//        $file_for_day = app_path() . '/../public/excel/' . '古茗-' . $day . '-绑定信息.xlsx';

        $file_for_real_time = app_path() . '/../public/excel/' . '古茗实时绑定信息.xlsx';

        try {
//            unlink($file_for_day);
            unlink($file_for_real_time);
        } catch (\Exception $e) {
        }

//        (new FastExcel($data))->export($file_for_day);
        (new FastExcel($on_time_machines))->export($file_for_real_time);

        $mail_to = explode(',', env('GM_EMAIL_TO'));
//
        foreach ($mail_to as $k => $v) {
            Mail::to($v)->send(new GMMachineBind($file_for_real_time));
        }

    }


    protected function getCacheForCollect($prefix)
    {
        $prefix = Redis::keys($prefix);

        $data = Redis::pipeline(function ($pipe) use ($prefix) {
            for ($i = 0; $i < count($prefix); $i++) {
                $pipe->get($prefix[$i]);
            }
        });

        return $data;
    }

    protected function getShopName($org_id, $shops, $all_machines)
    {
        // 先找出店型id
        $shop_id = 0;
        foreach ($all_machines as $k => $v) {
            if ($v->organization_id == $org_id) {
                $shop_id = $v->shop_id;
                break;
            }
        }

        foreach ($shops as $k => $v) {
            if ($shop_id == $v->id) {
                return $v->name;
            }
        }
        return '未知';
    }


    protected function getShopName2($shop_id, $shops)
    {
        foreach ($shops as $k => $v) {
            if ($shop_id == $v->id) {
                return $v->name;
            }
        }
        return '';
    }
}
