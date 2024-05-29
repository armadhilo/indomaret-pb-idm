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
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GeneralExcelExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;

class KlikIgrFooterController extends KlikIgrController
{
    public function index(){

        //* atur judulnya
        if(session('flagIGR')){
            $data['tittle'] = 'MASTER PICKING KLIKINDOGROSIR';
        }else{
            $data['tittle'] = 'MASTER PICKING SPI';
        }

        //* CHECK GROUP PICKING
        $dtCek = DB::table('group_picker_klik')->selectRaw("COUNT(gpk_group)")->first();
        if($dtCek->count <= 0){
            return ApiFormatter::error(400, 'Mohon Setting Group Picking Terlebih Dahulu!');
        }
    }

    //* loadFilterRak
    public function loadKodeRak($group){
        // sb.AppendLine("SELECT DISTINCT lks_koderak ")
        // sb.AppendLine(" FROM tbmaster_lokasi ")
        // sb.AppendLine("WHERE (  ")
        // sb.AppendLine("  lks_koderak LIKE 'D%' OR   ")
        // sb.AppendLine("  lks_koderak LIKE 'O%' OR  ")
        // sb.AppendLine("  lks_koderak LIKE 'R%' OR  ")
        // sb.AppendLine("  lks_koderak LIKE 'P%'  ")
        // sb.AppendLine(") ")
        // sb.AppendLine("AND (  ")
        // sb.AppendLine("  lks_tiperak LIKE 'B%' OR  ")
        // sb.AppendLine("  lks_tiperak LIKE 'I%' OR  ")
        // sb.AppendLine("  lks_tiperak LIKE 'N%'  ")
        // sb.AppendLine(") ")
        // sb.AppendLine("AND COALESCE(lks_noid,'9999') NOT LIKE '%B' ")
        // If group <> String.Empty Then
        //     sb.AppendLine("AND NOT EXISTS ( ")
        //     sb.AppendLine("  SELECT pk_koderak ")
        //     sb.AppendLine("  FROM picker_klik ")
        //     sb.AppendLine("  WHERE pk_group = '" & group & "' ")
        //     sb.AppendLine("  AND pk_koderak = lks_koderak ")
        //     sb.AppendLine("  AND pk_kodesubrak = lks_kodesubrak ")
        //     sb.AppendLine(") ")
        // End If
        // sb.AppendLine("ORDER BY 1 ASC ")

        //* ada pilihan all juga
    }

    //* loadGroup
    public function loadGroup(){
        // sb.AppendLine("SELECT DISTINCT gpk_group GRUP ")
        // sb.AppendLine("  FROM group_picker_klik ")
        // sb.AppendLine(" ORDER BY gpk_group ASC ")
    }

    //* loadUser
    public function loadUserID($group){
        // sb.AppendLine("SELECT userid, username, ")
        // sb.AppendLine("       userid || ' - ' || username ""USER"" ")
        // sb.AppendLine("  FROM tbmaster_user ")
        // sb.AppendLine(" WHERE userid IN ( ")
        // sb.AppendLine("          SELECT DISTINCT u.userid ")
        // sb.AppendLine("            FROM tbmaster_useraccess u, tbmaster_access a ")
        // sb.AppendLine("           WHERE u.accesscode = a.accesscode ")
        // sb.AppendLine("             AND upper(a.accessgroup) = 'HANDHELD' ")
        // sb.AppendLine("             AND a.accessname LIKE '%OBI%' ")
        // sb.AppendLine(" ) ")
        // sb.AppendLine(" AND recordid IS NULL ")
        // If group <> String.Empty Then
        //     sb.AppendLine("AND EXISTS ( ")
        //     sb.AppendLine("  SELECT gpk_userid ")
        //     sb.AppendLine("  FROM group_picker_klik ")
        //     sb.AppendLine("  WHERE gpk_group = '" & group & "' ")
        //     sb.AppendLine("  AND gpk_userid = userid ")
        //     sb.AppendLine(") ")
        // End If
        // sb.AppendLine(" ORDER BY username ASC ")
    }

    //* loadRakUser
    public function actionSelectUserId($group, $user){
        // userid = Strings.Left(user, user.IndexOf("-") - 1)

        // sb = New StringBuilder
        // sb.AppendLine("SELECT pk_urutan ""Urutan"", ")
        // sb.AppendLine("       pk_koderak ""Kode Rak"", ")
        // sb.AppendLine("       pk_kodesubrak ""SubRak"" ")
        // sb.AppendLine("  FROM picker_klik ")
        // sb.AppendLine(" WHERE pk_userid = '" & userid & "' ")
        // sb.AppendLine("   AND pk_group = '" & group & "' ")
        // sb.AppendLine(" ORDER BY pk_urutan ASC ")
    }

    //* loadRakAll
    public function actionSelectKodeRak($group){
        // sb.AppendLine("SELECT DISTINCT lks_koderak koderak, ")
        // sb.AppendLine("       lks_kodesubrak kodesubrak, ")
        // sb.AppendLine("       0 pick ")
        // sb.AppendLine(" FROM tbmaster_lokasi ")
        // sb.AppendLine("WHERE ( ")
        // sb.AppendLine("  lks_koderak LIKE 'D%' OR ")
        // sb.AppendLine("  lks_koderak LIKE 'O%' OR ")
        // sb.AppendLine("  lks_koderak LIKE 'R%' OR ")
        // sb.AppendLine("  lks_koderak LIKE 'P%' ")
        // sb.AppendLine(") ")
        // sb.AppendLine("AND ( ")
        // sb.AppendLine("  lks_tiperak LIKE 'B%' OR ")
        // sb.AppendLine("  lks_tiperak LIKE 'I%' OR ")
        // sb.AppendLine("  lks_tiperak LIKE 'N%' ")
        // sb.AppendLine(") ")
        // sb.AppendLine("AND COALESCE(lks_noid,'9999') NOT LIKE '%B' ")

        // If flagIGR And Not flagSPI Then
        //     If group <> String.Empty Then
        //         sb.AppendLine("AND NOT EXISTS ( ")
        //         sb.AppendLine("  SELECT pk_koderak ")
        //         sb.AppendLine("  FROM picker_klik ")
        //         sb.AppendLine("  WHERE pk_group = '" & group & "' ")
        //         sb.AppendLine("  AND pk_koderak = lks_koderak ")
        //         sb.AppendLine("  AND pk_kodesubrak = lks_kodesubrak ")
        //         sb.AppendLine(") ")
        //     End If
        // End If

        // If cbKodeRak.Text.ToString <> "ALL" Then
        //     sb.AppendLine("AND lks_koderak = '" & cbKodeRak.Text.ToString & "' ")
        // End If
        // sb.AppendLine("ORDER BY 1 ASC, 2 ASC ")

        //* CHECK YANG UDAH ADAH
        // For i As Integer = 0 To dgvRakAll.Rows.Count - 1
        //     Dim rak As String = dgvRakAll.Item(0, i).Value.ToString & "|" & _
        //                             dgvRakAll.Item(1, i).Value.ToString

        //     If listRak.Contains(rak) Then
        //         dgvRakAll.Item(2, i).Value = True
        //     End If
        // Next
    }

    public function actionPlus(){
        //* open form -> frmGroupPickerKlik
    }

    public function actionSimpan($array_rak, $group){

        if(count($array_rak) == 0){
            return ApiFormatter::error(400, 'Rak Belum Dipilih !');
        }

        $counter = 0;

        // userid = Strings.Left(cbPicker.Text, cbPicker.Text.IndexOf("-") - 1)
        // ExecScalar("SELECT count(pk_urutan) FROM picker_klik WHERE pk_userid = '" & userid & "' AND pk_group = '" & group & "'", "GET COUNTER", counter)

        // For Each strRaks As String In listRak
        //     Dim arrRak As String() = Strings.Split(strRaks, "|")
        //     counter += 1
        //     ExecScalar("SELECT COUNT(pk_userid) FROM picker_klik WHERE pk_userid = '" & userid & "' AND pk_group = '" & group & "' AND pk_koderak = '" & arrRak(0).ToString & "' AND pk_kodesubrak = '" & arrRak(1).ToString & "'", "GET DATA PICKER", cek)

        //     If cek = 0 Then
        //         sb = New StringBuilder
        //         sb.AppendLine("INSERT INTO picker_klik ( ")
        //         sb.AppendLine("  pk_group, pk_userid, pk_koderak, pk_kodesubrak, pk_urutan, pk_create_by, pk_create_dt ")
        //         sb.AppendLine(") VALUES ( ")
        //         sb.AppendLine("  '" & group & "', '" & userid & "', '" & arrRak(0).ToString & "', '" & arrRak(1).ToString & "', " & counter & ", '" & UserMODUL & "', NOW() ")
        //         sb.AppendLine(") ")
        //         NonQueryOra(sb.ToString)
        //     End If
        // Next
        // MessageDialog.Show(EnumMessageType.Information, EnumCommonButtonMessage.Ok, "Berhasil Menyimpan Data !", "INDOGROSIR")

        //* nanti get ulang data table kanan dan kiri

    }

    public function actionHapus(){
        // sb = New StringBuilder
        // sb.AppendLine("DELETE FROM picker_klik ")
        // sb.AppendLine(" WHERE pk_userid = '" & userid & "' ")
        // sb.AppendLine("   AND pk_koderak || '|' || pk_kodesubrak IN (" & rak & ") ")

        // NonQueryOra(sb.ToString)

        // sb = New StringBuilder
        // sb.AppendLine("SELECT pk_koderak, pk_kodesubrak ")
        // sb.AppendLine("  FROM picker_klik  ")
        // sb.AppendLine(" WHERE pk_userid = '" & userid & "' ")
        // sb.AppendLine(" ORDER BY pk_urutan ASC ")
        // dt = New DataTable

        // dt = QueryOra(sb.ToString)
        // If dt.Rows.Count > 0 Then
        //     Dim newSeq As Integer = 0
        //     For Each row As DataRow In dt.Rows
        //         newSeq += 1
        //         sb = New StringBuilder
        //         sb.AppendLine("UPDATE picker_klik SET pk_urutan = " & newSeq & " ")
        //         sb.AppendLine(" WHERE pk_userid = '" & userid & "' ")
        //         sb.AppendLine("   AND pk_koderak = '" & row.Item(0).ToString & "' ")
        //         sb.AppendLine("   AND pk_kodesubrak = '" & row.Item(1).ToString & "' ")

        //         NonQueryOra(sb.ToString)
        //     Next
        // End If


        // MessageDialog.Show(EnumMessageType.Information, EnumCommonButtonMessage.Ok, "Berhasil Menghapus Data !", "INDOGROSIR")
    }




}
