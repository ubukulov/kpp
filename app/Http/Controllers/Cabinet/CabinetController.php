<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\Driver;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Auth;

class CabinetController extends BaseController
{
    protected $username = 'ScanGo';
    protected $password = '128500';
    protected $server   = '10.1.0.18\DLSQL';
    protected $database = 'SCANGO';

    public function cabinet()
    {
        return view('cabinet.index');
    }

    public function getDriversList()
    {
        $result = Driver::orderBy('drivers.id', 'DESC')
            ->join('permits', 'permits.ud_number', '=', 'drivers.ud_number')
            ->whereNotNull(['path_docs_fac', 'path_docs_back'])
            ->get();
        $drivers = [];
        foreach($result as $item) {
            if(file_exists(public_path().'/uploads/'.$item->path_docs_fac)) {
                $drivers[] = $item;
            }
        }

        return json_encode($drivers);
    }

    public function kpp_samsung()
    {
        return view('cabinet.kpp_samsung');
    }

    public function get100Logs()
    {
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {
            $sql = "SELECT TOP 100
                        CONVERT(VARCHAR, DATE_SCAN,120) AS [VALUE-1]
                        ,ORDER_CODE AS [VALUE-2]
                    FROM LOG_SCAN
                    WHERE DEPOSITOR_CODE = 'SAMSUNG'
                    ORDER BY DATE_SCAN DESC";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            $i = 1;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $row['index'] = $i;
                $logs[] = $row;
                $i++;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function getDocumentByCode(Request $request)
    {
        $document = $request->input('document');

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {
            $sql = "SELECT TOP 1
                        CONVERT(VARCHAR, DATE_SCAN,120) AS [VALUE-1]
                        ,ORDER_CODE AS [VALUE-2]
                    FROM LOG_SCAN
                    WHERE DEPOSITOR_CODE = 'SAMSUNG' AND ORDER_CODE like '%$document%'
                    ORDER BY DATE_SCAN DESC";

            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            $i = 1;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $row['index'] = $i;
                $logs[] = $row;
                $i++;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function barcode()
    {
        return view('cabinet.barcode_samsung');
    }

    public function getOrders()
    {
        $connInfo = [
            'Database' => 'LV_SAMSUNG', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {
            $sql = "SELECT TOP 1000
                        /*CONCAT(LV_Order.ord_Code, ' - ', LV_OrderStatus.ors_MessageCode) as ord_Code,*/
                        LV_Order.ord_Code,
                        LV_OrderStatus.*
                    FROM LV_Order
                    INNER JOIN LV_OrderStatus ON LV_OrderStatus.ors_ID=LV_Order.ord_StatusID
                    WHERE ord_DepositorID = 11 /*AND ord_StatusID NOT IN(10,11,1)*/ AND LV_Order.ord_TypeID=25 ORDER BY ord_ID DESC";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            $i = 1;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $row['index'] = $i;
                $logs[] = $row;
                $i++;
            }
            return response()->json($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function printOrders(Request $request)
    {
        $count = (int) $request->input('count');
        $order_code = $request->input('order_code');

        if ($count == 1) {
            $this->print($order_code, $count);
        }

        if ($count > 1) {
            for($i=1; $i<=$count; $i++) {
                $this->print($order_code, $count, $i);
            }
        }

        return response('Принтер распечатал этикентки');
    }

    public function print($order_code, $count, $i=0)
    {
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;

        $printer = "\\\\".$computer_name.$printer_name;
        // Open connection to the thermal printer
        $fp = fopen($printer, "w");
        if (!$fp){
//            die('no connection');
            return response([
                'data' => [
                    'message' => 'no connection to printer'
                ]
            ], 500);
        }

        $date = date("d.m.Y H:i");

        if ($i == 0) $i = 1;


        $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:tt0003m_^FS^XZ
^XA
^MMT
^PW812
^LL0812
^LS0
^FT46,53^A0N,34,33^FH\^FDOrder num:^FS
^FT535,58^A0N,34,33^FH\^FD$date^FS
^FT104,203^A0N,127,124^FH\^FD$order_code^FS
^BY3,3,122^FT46,370^BCN,,N,N
^FD>;$order_code^FS
^FT623,306^A0N,56,55^FB123,1,0,R^FH\^FDPack:^FS
^FT657,358^A0N,51,50^FB63,1,0,C^FH\^FD$i/$count^FS

^PQ1,0,1,Y^XZ
HERE;

        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }
    }
}
