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
}
