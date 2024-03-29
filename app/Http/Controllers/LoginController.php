<?php

namespace App\Http\Controllers;

use App\Helper\DatabaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    function index()
    {
        return view('login');
    }

    public function login(Request $req)
    {
        try {
            $mytime = date('Ymd H:i:s');
            DatabaseConnection::setConnection($req->branch, "PRODUCTION");
            $data = DB::table("tbmaster_user")
                ->where("userid", $req->username)
                ->where("userpassword", $req->password)
                ->get();

            if (count($data) > 0) {
                session([
                    "login" => true,
                    "userid" => $req->username,
                    "NAMACABANG" => $req->branchname,
                    "KODECABANG" => $req->branch,
                    "SERVER" => $req->type,
                    "userlevel" => $data[0]->userlevel,
                    "sequencenumber" => $data[0]->userlevel
                ]);

                return response()->json(["success" => "user found", "status" => "200"], 200);
            } else {
                return response()->json(["error" => "user not found", "status" => "400"], 400);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "error" => "user tidak ditemukan, silahkan coba lagi!", "errorMessage" => $e->getMessage()], 400);
        }
    }

    function logout()
    {
        session()->flush();
        // return redirect("/login");
        return redirect(\URL::previous());
    }
}
