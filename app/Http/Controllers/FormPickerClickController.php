<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', '0');

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FormPickerClickController extends KlikIgrController
{

    //* loadFilterRak
    public function loadKodeRak($group = ''){
        $query = "";
        $query .= "SELECT DISTINCT lks_koderak ";
        $query .= "FROM tbmaster_lokasi ";
        $query .= "WHERE ( ";
        $query .= "  lks_koderak LIKE 'D%' OR ";
        $query .= "  lks_koderak LIKE 'O%' OR ";
        $query .= "  lks_koderak LIKE 'R%' OR ";
        $query .= "  lks_koderak LIKE 'P%' ";
        $query .= ") ";
        $query .= "AND ( ";
        $query .= "  lks_tiperak LIKE 'B%' OR ";
        $query .= "  lks_tiperak LIKE 'I%' OR ";
        $query .= "  lks_tiperak LIKE 'N%' ";
        $query .= ") ";
        $query .= "AND COALESCE(lks_noid,'9999') NOT LIKE '%B' ";

        
        if ($group !== '') {
            $query .= "AND NOT EXISTS ( ";
            $query .= "  SELECT pk_koderak ";
            $query .= "  FROM picker_klik ";
            $query .= "  WHERE pk_group = '" . $group . "' ";
            $query .= "  AND pk_koderak = lks_koderak ";
            $query .= "  AND pk_kodesubrak = lks_kodesubrak ";
            $query .= ") ";
        }

        $query .= "ORDER BY 1 ASC";

        $data['data'] = DB::select($query);
        
        return ApiFormatter::success(200, "success", $data);

        //* ada pilihan all juga
    }

    //* loadGroup
    public function loadGroup(){
        $data['data'] = DB::select("SELECT DISTINCT gpk_group AS GRUP FROM group_picker_klik ORDER BY gpk_group ASC");
        return ApiFormatter::success(200, "Success", $data);
    }

    //* loadUser
    public function loadUserID($group = ''){
        $query = "";
        $query .= "SELECT userid, username, ";
        $query .= "       CONCAT(userid, ' - ', username) AS USER ";
        $query .= "  FROM tbmaster_user ";
        $query .= " WHERE userid IN ( ";
        $query .= "          SELECT DISTINCT u.userid ";
        $query .= "            FROM tbmaster_useraccess u, tbmaster_access a ";
        $query .= "           WHERE u.accesscode = a.accesscode ";
        $query .= "             AND UPPER(a.accessgroup) = 'HANDHELD' ";
        $query .= "             AND a.accessname LIKE '%OBI%' ";
        $query .= " ) ";
        $query .= " AND recordid IS NULL ";
        if ($group !== '') {
            $query .= " AND EXISTS ( ";
            $query .= "  SELECT gpk_userid ";
            $query .= "  FROM group_picker_klik ";
            $query .= "  WHERE gpk_group = '" . addslashes($group) . "' ";
            $query .= "  AND gpk_userid = userid ";
            $query .= " ) ";
        }

        $query .= " ORDER BY username ASC";

        
        $data['data'] = DB::select($query);

        if(count($data['data']) < 1){
            return ApiFormatter::error(400, "Data User HH Tidak Ditemukan!");
        }
        
        return ApiFormatter::success(200, "success", $data);
    }

    //* loadRakUser
    public function actionSelectUserId($group, $userid){

        $query = "";
        $query .= "SELECT pk_urutan AS Urutan, ";
        $query .= "       pk_koderak AS koderak, ";
        $query .= "       pk_kodesubrak AS kodesubrak ";
        $query .= "  FROM picker_klik ";
        $query .= " WHERE pk_userid = '" . addslashes($userid) . "' ";
        $query .= "   AND pk_group = '" . addslashes($group) . "' ";
        $query .= " ORDER BY pk_urutan ASC ";
        //! IRVAN || DUMMY DATA

        $data['data'] = DB::select($query);

        return ApiFormatter::success(200, "success", $data);
    }

    //* loadRakAll
    public function actionSelectKodeRak(Request $request){
        $query = "";
        $query .= "SELECT DISTINCT lks_koderak AS koderak, ";
        $query .= "       lks_kodesubrak AS kodesubrak, ";
        $query .= "       0 AS pick ";
        $query .= "FROM tbmaster_lokasi ";
        $query .= "WHERE ( ";
        $query .= "  lks_koderak LIKE 'D%' OR ";
        $query .= "  lks_koderak LIKE 'O%' OR ";
        $query .= "  lks_koderak LIKE 'R%' OR ";
        $query .= "  lks_koderak LIKE 'P%' ";
        $query .= ") ";
        $query .= "AND ( ";
        $query .= "  lks_tiperak LIKE 'B%' OR ";
        $query .= "  lks_tiperak LIKE 'I%' OR ";
        $query .= "  lks_tiperak LIKE 'N%' ";
        $query .= ") ";
        $query .= "AND COALESCE(lks_noid,'9999') NOT LIKE '%B' ";

        if (session("flagIGR") && !session("flagSPI")) {
            if (!empty($request->group)) {
                $query .= "AND NOT EXISTS ( ";
                $query .= "  SELECT pk_koderak ";
                $query .= "  FROM picker_klik ";
                $query .= "  WHERE pk_group = '" . $request->group . "' ";
                $query .= "  AND pk_koderak = lks_koderak ";
                $query .= "  AND pk_kodesubrak = lks_kodesubrak ";
                $query .= ") ";
            }
        }

        if ($request->kode_rak != "ALL") {
            $query .= "AND lks_koderak = '" . $request->kode_rak . "' ";
        }

        $query .= "ORDER BY 1 ASC, 2 ASC";

        // Execute the query
        $results = DB::select($query);
        if (is_array($request->listRak) && count($request->listRak) !== 0) {
            foreach ($results as $key => $result) {
                $rak = $result->koderak . '|' . $result->kodesubrak;
                $results[$key]->pick = in_array($rak, $request->listRak) ? 1 : 0;
            }
        }

        $data['data'] = $results;

        return ApiFormatter::success(200, "success", $data);
    }

    public function actionSimpan(Request $request){
        DB::beginTransaction();
        try{
            if(count($request->data) == 0){
                return ApiFormatter::error(400, 'Rak Belum Dipilih !');
            }

            $result = DB::select(
                'SELECT COUNT(pk_urutan) as counter FROM picker_klik WHERE pk_userid = ? AND pk_group = ?',
                [$request->userid, $request->group]
            );

            $counter = $result[0]->counter;

            foreach ($request->data as $item) {
                $counter++;

                $cekQuery = "SELECT COUNT(pk_userid) FROM picker_klik WHERE pk_userid = '" . addslashes($request->userid) . "' AND pk_group = '" . addslashes($request->group) . "' AND pk_koderak = '" . addslashes($item["koderak"]) . "' AND pk_kodesubrak = '" . addslashes($item["kodesubrak"]) . "'";
                $cek = DB::select($cekQuery)[0]->count;

                if ($cek == 0) {
                    $query = "";
                    $query .= "INSERT INTO picker_klik (pk_group, pk_userid, pk_koderak, pk_kodesubrak, pk_urutan, pk_create_by, pk_create_dt) ";
                    $query .= "VALUES ('" . addslashes($request->group) . "', '" . addslashes($request->userid) . "', '" . addslashes($item["koderak"]) . "', '" . addslashes($item["kodesubrak"]) . "', " . $counter . ", '" . addslashes(session("userid")) . "', NOW()); ";
                    DB::insert($query);
                }
            }

            //! IRVAN | COMMIT COMMENT
            DB::commit();

            return ApiFormatter::success(200, "Berhasil Menyimpan Data!");


        } catch (HttpResponseException $e) {
            // Handle the custom response exception
            throw new HttpResponseException($e->getResponse());

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }
    }

    public function actionHapus(Request $request){
        DB::beginTransaction();
        try{
            foreach ($request->data as $item) {
                $concatenatedRak = $item['koderak'] . '|' . $item['kodesubrak'];
                $query = "DELETE FROM picker_klik WHERE pk_userid ='" . $request->userid . "' AND CONCAT(pk_koderak, '|', pk_kodesubrak) IN ('" . $concatenatedRak . "')";
                DB::delete($query);
            }


            $rows = DB::table('picker_klik')
                ->select('pk_koderak', 'pk_kodesubrak')
                ->where('pk_userid', $request->userid)
                ->orderBy('pk_urutan', 'ASC')
                ->get();

            if (count($rows) > 0) {
                $newSeq = 0;

                foreach ($rows as $row) {
                    $newSeq++;
                    
                    $query = "UPDATE picker_klik SET pk_urutan = ? WHERE pk_userid = ? AND pk_koderak = ? AND pk_kodesubrak = ?";
                    
                    DB::update($query, [$newSeq, $request->userid, $row->pk_koderak, $row->pk_kodesubrak]);
                }
            }

            DB::commit();
            return ApiFormatter::success(200, "Berhasil Menghapus Data!");

        } catch (HttpResponseException $e) {
            // Handle the custom response exception
            throw new HttpResponseException($e->getResponse());

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }
    }


    //! ACTION ADD GROUP
    public function actionAddGroup(){
        if(session("flagIGR") && !session("flagSPI")){
            $data['lblTitle'] = "MASTER GROUP PICKING KLIKINDOGROSIR";
        } else {
            $data['lblTitle'] = "MASTER GROUP PICKING SPI";
        }

        return ApiFormatter::success(200, "success", $data);
    }

    public function actionFilterGroup(){
        $data['data'] = DB::select("SELECT DISTINCT gpk_group AS group FROM group_picker_klik ORDER BY gpk_group ASC");
        return ApiFormatter::success(200, "success", $data);
    }

    public function actionLoadUser(){
        $query = "";
        $query .= "SELECT userid AS ID, username AS NAMA, 0 AS PICK ";
        $query .= "FROM tbmaster_user ";
        $query .= "WHERE userid IN ( ";
        $query .= "    SELECT DISTINCT u.userid ";
        $query .= "    FROM tbmaster_useraccess u, tbmaster_access a ";
        $query .= "    WHERE u.accesscode = a.accesscode ";
        $query .= "      AND UPPER(a.accessgroup) = 'HANDHELD' ";
        $query .= "      AND a.accessname LIKE '%OBI%' ";
        $query .= ") ";
        $query .= "AND recordid IS NULL ";
        $query .= "AND NOT EXISTS ( ";
        $query .= "    SELECT 1 ";
        $query .= "    FROM group_picker_klik ";
        $query .= "    WHERE gpk_userid = userid ";
        $query .= ") ";
        $query .= "ORDER BY username ASC";

        $data['data'] = DB::select($query);

        return ApiFormatter::success(200, "success", $data);
    }

    public function actionGroupPicker($group){
        $query = "";

        $query .= "SELECT gpk_group AS GRUP, gpk_userid AS ID, username AS NAMA ";
        $query .= "FROM group_picker_klik ";
        $query .= "JOIN tbmaster_user ";
        $query .= "ON gpk_userid = userid ";

        if ($group !== "ALL") {
            $query .= "WHERE gpk_group = ? ";
        }

        $query .= "ORDER BY gpk_group ASC, username ASC";

        if ($group !== "ALL") {
            $data['data'] = DB::select($query, [$group]);
        } else {
            $data['data'] = DB::select($query);
        }

        return ApiFormatter::success(200, "success", $data);
    }

    public function actionGroupSimpan(Request $request){
        DB::beginTransaction();
        try{
            foreach ($request->data as $item) {
                $query = "";
                $query .= "INSERT INTO group_picker_klik ( ";
                $query .= "  gpk_group, gpk_userid, gpk_create_by, gpk_create_dt ";
                $query .= ") VALUES ( ";
                $query .= "  ?, ?, ?, NOW() ";
                $query .= ") ";
        
                // Execute the query
                DB::insert($query, [$request->inputGroup, $item["id"], session("userid")]);
            }

            DB::commit();
            return ApiFormatter::success(200, "Berhasil Menyimpan Data Group " . $request->inputGroup . " !");
        } catch (HttpResponseException $e) {
            // Handle the custom response exception
            throw new HttpResponseException($e->getResponse());

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }

    }

    public function actionGroupHapus(Request $request){
        DB::beginTransaction();
        try{
            foreach ($request->data as $item) {
                $userID = $item['id'];
                $query = "";
                $query .= "DELETE FROM group_picker_klik ";
                $query .= "WHERE gpk_userid IN ('" . $userID . "')";
                DB::delete($query);

                $query = "";
                $query .= "DELETE FROM picker_klik ";
                $query .= "WHERE pk_userid IN ('" . $userID . "')";
                DB::delete($query);
            }

            DB::commit();
            return ApiFormatter::success(200, "Berhasil Menghapus Data!");

        } catch (HttpResponseException $e) {
            // Handle the custom response exception
            throw new HttpResponseException($e->getResponse());

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }
    }


}
