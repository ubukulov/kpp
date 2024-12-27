<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class InventSamsungController extends BaseApiController
{
    protected $username = 'sa';
    protected $password = 'Paradiz2017';
    protected $server = '10.1.0.18\DLSQL';
    protected $database = 'LV_SAMSUNG';
    protected $data = [
        'Database' => 'LV_SAMSUNG', 'UID' => 'sa', 'PWD' => 'Paradiz2017',
        'CharacterSet' => 'UTF-8',
    ];

    public function getInventCodeList() {
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);
        if ($conn) {
            $sqlselect = 'select
                            cpn_ID AS id,
                            cpn_code As code
                          from LV_CountPlan where cpn_StatusID <> 4';
            $smt = sqlsrv_query($conn, $sqlselect);
            if ($smt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($smt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function inventory(Request $request)
    {
        $web_inventId = $request->get('web_InventId');
        $web_inventCode = $request->get('web_InventCode');
        $web_LocCode = $request->get('web_InventLocCode');
        $web_sscc = $request->get('web_SSCC');
        $web_Qty = $request->get('web_Qty');
        $user_email = $this->user->email;

        $conn = sqlsrv_connect($this->server, $this->data);
        if($conn) {
            $sql = "SELECT * FROM [dbo].[WO_WebCount] WHERE web_InventId = '$web_inventId' AND web_LocCode = '$web_LocCode' AND web_SSCC= '$web_sscc' ";
            $smt = sqlsrv_query($conn, $sql);
            if ($smt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($smt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }

            if(count($logs) == 0) {
                $insert_sql = "INSERT INTO wo_WebCount(web_InventId, web_InventCode, web_LocCode, web_SSCC, web_Qty, web_User)
                               VALUES($web_inventId, '$web_inventCode', '$web_LocCode', '$web_sscc', $web_Qty,'$user_email')";
                $smt = sqlsrv_query($conn, $insert_sql);
                if ($smt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                return response()->json([
                    'status' => 'ok'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error'
                ], 200);
            }
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function getInventory()
    {
        $conn = sqlsrv_connect($this->server, $this->data);
        if ($conn) {
            $sqlselect = 'select
                            cpn_ID AS id,
                            cpn_code As code
                          from LV_CountPlan where cpn_StatusID = 4';
            $smt = sqlsrv_query($conn, $sqlselect);
            if ($smt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($smt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function getInventoryById($id)
    {
        $conn = sqlsrv_connect($this->server, $this->data);
        if ($conn) {
            $sqlselect = "SELECT * FROM [dbo].[WO_WebCount] WHERE web_InventId = '$id'";
            $smt = sqlsrv_query($conn, $sqlselect);
            if ($smt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($smt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function parking(Request $request)
    {
        $data = $request->all();

        $response = [
            "plateNumber" => $data['plateNumber'],
           "discountType" => "percent",
           "discountValue" => "100",
           "startDate" =>  $data['startDate'],
           "endDate" =>  $data['endDate'],
           "sessionId" =>  $data['sessionId'],
        ];

        return response()->json($response);
    }
}
