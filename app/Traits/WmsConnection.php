<?php


namespace App\Traits;


trait WmsConnection
{
    protected $username;
    protected $password;
    protected $server;
    protected $database;
    protected $conn;

    public function __construct()
    {
        $this->username = env('WMS_USERNAME');
        $this->password = env('WMS_PASSWORD');
        $this->server   = env('WMS_SERVER');
        $this->database = env('WMS_DATABASE');

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password, 'CharacterSet' => 'UTF-8',
        ];

        if($this->conn === null) {
            $this->conn = sqlsrv_connect($this->server, $connInfo);
        }

        return $this->conn;
    }

    public function query($sql, $newDatabaseName = '')
    {
        $connInfo = [
            'Database' => ($newDatabaseName == '') ? $this->database : $newDatabaseName, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        if($newDatabaseName !== '') {
            $this->conn = sqlsrv_connect($this->server, $connInfo);
        }

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
