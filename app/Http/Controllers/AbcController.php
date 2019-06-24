<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbcController extends Controller
{
    public function server() {
//        $data =
        $post_data = file_get_contents("php://input");
        $params = json_decode($post_data, true);
        Log::info('server::', $params);
    }

    public function page() {
        $post_data = file_get_contents("php://input");
        $params = json_decode($post_data, true);
        Log::info('server::', $params);

        var_dump($params);
    }
    
}
