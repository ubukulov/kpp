<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseWmsController extends Controller
{
    protected $username = 'ScanGo';
    protected $password = '128500';
    protected $server   = '10.1.0.18\DLSQL';
    protected $database = 'LV_SAMSUNG';
    protected $conn;

    public function __construct()
    {
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        if($this->conn === null) {
            $this->conn = sqlsrv_connect($this->server, $connInfo);
        }

        return $this->conn;
    }

    public function query($sql)
    {
        $stmt = sqlsrv_query($this->conn, $sql);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $logs = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $logs[] = $row;
        }

        return $logs;
    }
}
