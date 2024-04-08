<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getPaketIPP($kodetoko){
        $data = DB::select("select coalesce(CLS_PAKETIPP,'0') as data FROM CLUSTER_IDM WHERE CLS_TOKO = '" . $kodetoko . "'");
        if(count($data) == 0){
            return null;
        }

        return $data[0]['data'];
    }
}
