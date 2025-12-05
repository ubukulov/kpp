<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;
class WmsConnectionService
{
    protected string $username;
    protected string $password;
    protected string $server;
    protected string $database;
    protected $conn = null;

    public function __construct()
    {
        $this->username = config('wms.username', env('WMS_USERNAME'));
        $this->password = config('wms.password', env('WMS_PASSWORD'));
        $this->server = config('wms.server', env('WMS_SERVER'));
        $this->database = config('wms.database', env('WMS_DATABASE'));
    }

    /**
     * @param string $database
     * @return resource
     * @throws RuntimeException
     */
    protected function connect(string $database = '')
    {
        if ($this->conn !== null && ($database === '' || $database === $this->database)) {
            return $this->conn;
        }

        $db = $database === '' ? $this->database : $database;

        $connInfo = [
            'Database' => $db,
            'UID' => $this->username,
            'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn === false) {
            $error = sqlsrv_errors() ?: [['message' => 'Unknown connection error']];
            Log::error('WMS SQLSRV connect error', [
                'errors' => $error,
                'server' => $this->server,
                'database' => $db
            ]);

            throw new RuntimeException('Cannot connect to WMS database');
        }

        $this->conn = $conn;
        return $this->conn;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $database
     * @return resource
     * @throws RuntimeException
     */
    protected function runQuery(string $sql, array $params = [], string $database = '')
    {
        $conn = $this->connect($database);

        if (empty($params)) {
            $stmt = sqlsrv_query($conn, $sql);
        } else {
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt) {
                if (!sqlsrv_execute($stmt)) {
                    $stmt = false;
                }
            }
        }

        if ($stmt === false) {
            $error = sqlsrv_errors();
            Log::error('WMS SQLSRV query error', [
                'sql'    => $sql,
                'params' => $params,
                'errors' => $error,
            ]);
            throw new RuntimeException('WMS SQLSRV query error');
        }

        return $stmt;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $database
     * @return array
     * @throws RuntimeException
     */

    public function select(string $sql, array $params = [], string $database = ''): array
    {
        $stmt = $this->runQuery($sql, $params, $database);

        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }

        sqlsrv_free_stmt($stmt);

        return $rows;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $database
     * @return array
     */
    public function selectWithIndex(string $sql, array $params = [], string $database = ''): array
    {
        $rows = $this->select($sql, $params, $database);
        foreach ($rows as $i => &$row) {
            $row['index'] = $i + 1;
        }
        return $rows;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $database
     * @return array|null
     */
    public function selectOne(string $sql, array $params = [], string $database = ''): ?array
    {
        $rows = $this->select($sql, $params, $database);
        return $rows[0] ?? null;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $database
     * @return int
     * @throws RuntimeException
     */

    public function execute(string $sql, array $params = [], string $database = ''): int
    {
        $stmt = $this->runQuery($sql, $params, $database);
        $rowsAffected = sqlsrv_rows_affected($stmt);
        sqlsrv_free_stmt($stmt);

        return (int) $rowsAffected;
    }

    public function close(): void
    {
        if ($this->conn !== null) {
            sqlsrv_close($this->conn);
            $this->conn = null;
        }
    }
}
