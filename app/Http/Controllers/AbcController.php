<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbcController extends Controller
{
    public function server() {
        $post_data = file_get_contents("php://input");
//        $params = json_decode($post_data, true);
//        Log::info('server::', $params);
        Log::info('server::'.$post_data);
    }

    public function page() {
        $post_data = file_get_contents("php://input");
//        $params = json_decode($post_data, true);
        Log::info('page::'.$post_data);

        var_dump($post_data);
    }
    
}
