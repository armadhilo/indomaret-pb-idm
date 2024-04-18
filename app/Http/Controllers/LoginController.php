<?php

namespace App\Http\Controllers;

use App\Helper\DatabaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    function index()
    {

        //! CUSTOM ONLY ON PB IDM
        $dt = DB::table('tbmaster_perusahaan')
            ->selectRaw("prs_kodeigr,prs_namacabang, prs_alamat1, prs_alamat2, prs_namaperusahaan, UPPER(prs_singkatancabang) prs_singkatancabang, COALESCE(prs_flag_ftz,'N') prs_flag_ftz")
            ->first();

        $dtSPI = DB::table('tbmaster_spi')
            ->selectRaw("DISTINCT spi_kodespi, COALESCE(spi_flaghh, '0') spi_flaghh")
            ->first();

        $flagSPI = str_contains($dt->prs_singkatancabang, 'SPI') ? true : false;
        $flagFTZ = str_contains($dt->prs_flag_ftz, 'Y') ? true : false;
        $flagIGR = false;
        $flagHHSPI = false;

        $pilMode = [];

        //! APPEND PIL MODE
        if($flagSPI){
            $pilMode[] = 'SPI';
            $flagHHSPI = $dtSPI->spi_flaghh;
        }else{
            $flagIGR = true;
            $pilMode[] = 'INDOMARET';
            $pilMode[] = 'OMI';

            if(!empty($dtSPI)) $pilMode[] = 'SPI';
        }

        $data = [
            'pilMode' => $pilMode,
            'flagFTZ' => $flagFTZ,
            'flagIGR' => $flagIGR,
            'flagSPI' => $flagSPI,
            'flagHHSPI' => $flagHHSPI,
        ];

        return view('login', $data);
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
                    "sequencenumber" => $data[0]->userlevel,
                    "flagFTZ" => $req->flagFTZ,
                    "flagIGR" => $req->flagIGR,
                    "flagSPI" => $req->flagSPI,
                    "flagHHSPI" => $req->flagHHSPI,
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
        return redirect("/login");
        // return redirect(\URL::previous());
    }
}
