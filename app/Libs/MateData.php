<?php
namespace App\Libs;

class MateData
{

    private $access_id;
    private $access_key;

    public function __construct($access_id, $access_key)
    {
        $this->access_id = $access_id;
        $this->access_key = $access_key;
    }

    public function auth($name, $idNo, $mobile)
    {
        return $this->request_post('http://dat.matedata.net/sp/h5/v1/auth', [
            'name' => $name,
            'idNo' => $idNo,
            'mobile' => $mobile,
            'notifyUrl'=> 'http://47.106.215.176:8099/notifyUrl'
        ]);
    }
    
    public function getData($tradeNo, $orderNo, $name, $idNo, $mobile) {
        return $this->request_post('http://dat.matedata.net/sp/h5/v1/report', [
            'tradeNo' => $tradeNo,
            'orderNo' => $orderNo,
            'name' => $name,
            'idNo' => $idNo,
            'mobile' => $mobile
        ]);
    }
    

    private function request_post($url = '', $param = '')
    {
        $time = time() * 1000;
        $token = md5($this->access_key . $time);

        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = json_encode($param);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);//抓取指定网页
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:application/json",
            "timestamp: $time",
            "Access-Id:$this->access_id",
            "Token:$token"
        ]);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }


}