<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AggregationController extends BaseApiController
{
    protected $username = 'sa';
    protected $password = 'Paradiz2017';
    protected $server   = '10.1.0.18\DLSQL';
    protected $database = 'wms';

    public function aggregation(Request $request)
    {
        $user_email = $this->user->email;
        $data = $request->all();
        $web_ReceiptCode = $data['web_ReceiptCode'];
        $web_ProductCode = $data['web_ProductCode'];
        $web_Batch = $data['web_Batch'];
        $web_SSCC = $data['web_SSCC'];
        $web_ReceiptId = $data['web_ReceiptId'];
        $web_Brak = ($data['web_Brak'] == 'true') ? 1 : 0;
        $web_FactorySSCC = $data['web_FactorySSCC'];
        $web_SeqNumber = $data['web_SeqNumber'];
        $web_Barcode = $data['web_Barcode'];

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);

        if( $conn ) {
            $select_qty_rows = "SELECT web_ProductCode
                                FROM [dbo].[WO_WEBRECEIPTSVERKA]
                                WHERE web_ReceiptCode = '$web_ReceiptCode'
                                      AND web_SSCC = '$web_SSCC'";
            $sqlqtyrows = sqlsrv_query($conn, $select_qty_rows);
            if ($sqlqtyrows === false) {
                die( print_r( sqlsrv_errors(), true));
            }
            $insert_sql = "INSERT INTO [dbo].[WO_WEBRECEIPTSVERKA](web_ReceiptCode, web_ProductCode, web_Batch, web_SSCC, web_User, web_ReceiptId, web_Brak, web_FactorySSCC, web_SeqNumber, web_Barcode) VALUES('$web_ReceiptCode','$web_ProductCode','$web_Batch','$web_SSCC','$user_email', $web_ReceiptId, $web_Brak, '$web_FactorySSCC', '$web_SeqNumber', '$web_Barcode')";
            $qtyRows = [];
            while ($row = sqlsrv_fetch_array($sqlqtyrows, SQLSRV_FETCH_ASSOC)) {
                $qtyRows = $row;
            }
            if (count($qtyRows) == 0) {
                $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                if( $stmt === false ) {
                    die( print_r( sqlsrv_errors(), true));
                }
                return response()->json('success', 200);
            } else if (count($qtyRows) > 0) {
                foreach ($qtyRows as $row) {
                    if ($row == $web_ProductCode) {
                        $select_sql = "SELECT *
                           FROM [dbo].[WO_WEBRECEIPTSVERKA]
                           WHERE web_ReceiptCode = '$web_ReceiptCode'
                                 AND web_ProductCode = '$web_ProductCode'
                                 AND web_Batch = '$web_Batch'
                                 AND web_FactorySSCC = '$web_FactorySSCC'
                                 AND web_SeqNumber = '$web_SeqNumber'
                                 AND web_Barcode = '$web_Barcode'";
                        $sqlquery = sqlsrv_query($conn, $select_sql);
                        if ( $sqlquery === false) {
                            die( print_r(sqlsrv_errors(), true));
                        }
                        $rows = [];
                        while ( $row = sqlsrv_fetch_array( $sqlquery, SQLSRV_FETCH_ASSOC)) {
                            $rows[] = $row;
                        }
                        if (count($rows) == 0) {
                            $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                            if( $stmt === false ) {
                                die( print_r( sqlsrv_errors(), true));
                            }
                            return response()->json('success', 200);
                        } else {
                            return response()->json('failure');
                        }
                    } else {
                        return response()->json('ismono');;
                    }
                }
            }
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    // API которая возращает список приемок в ожидании WMS
    public function getAggregationCodes()
    {
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);

        if( $conn ) {
            $sql = "SELECT rct_ID as id, rct_Code as receipt
FROM LV_Receipt
WHERE rct_DepositorID = 13 and rct_ProgressID = 1";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return json_encode($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    // API возращает кол-во отсканированных коробов на паллете
    public function getAggregationQty(Request $request) {
        $data = $request->all();
        $web_ReceiptId = $data['web_ReceiptId'];
        $web_SSCC = $data['web_SSCC'];

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);

        if( $conn ) {
            $sqlCount = "SELECT count('$web_SSCC') as count
                         FROM [dbo].[WO_WEBRECEIPTSVERKA]
                         WHERE web_ReceiptID = '$web_ReceiptId' AND web_SSCC = '$web_SSCC'";
            $stmt = sqlsrv_query($conn, $sqlCount);
            if ( $stmt === false) {
                die( print_r( sqlsrv_errors(), true));
            }
            $logs = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
                }
                return json_encode($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    //API для разбивки паллеты
    public function deleteAggregationSscc(Request $request)
    {
        $user_email = $this->user->email;
        $data = $request->all();
        $web_SSCC = $data['web_SSCC'];
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',];
        $conn = sqlsrv_connect($this->server, $connInfo);

        if( $conn ) {
            $sql_exec = "EXEC WO_P_DeleteSSCCWebInterface ?, ?";
            $params = array(
                array($web_SSCC, SQLSRV_PARAM_IN),
                array($user_email, SQLSRV_PARAM_IN)
            );
            $stmt = sqlsrv_query($conn, $sql_exec, $params);
            if( $stmt === false) {
                die( print_r( sqlsrv_errors(), true));
            }
            $rows = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
               $rows[] = $row;
            }
            if (count($rows) == 0) {
                return response()->json('failure');
            } else {
                return response()->json('success');
            }
        } else {
            echo "Connection could not be established.<br />";
            die( print_r(sqlsrv_errors(), true));
        }
    }
    //API для получения списка расходных документов в статусе отобран
    public function getAggregationOrderCodes()
    {
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);

        if( $conn ) {
            $sql = "SELECT
	                      ord_ID  as id
	                     ,ord_Code as orders
                    FROM LV_Order
                    LEFT JOIN LV_OrderShipment on ost_OrderID = ord_ID
                    WHERE ord_StatusID in (1, 2)
                          AND ord_DepositorID = 13
                          AND ost_StatusID = 4";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return json_encode($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }
    //API для вставки данных
    public  function aggregationOrder(Request $request)
    {
        $user_email = $this->user->email;
        $data = $request->all();
        $web_OrderId = $data['web_OrderId'];
        $web_OrderCode = $data['web_OrderCode'];
        $web_ProductCode = $data['web_ProductCode'];
        $web_Batch = $data['web_Batch'];
        $web_SeqNumber = $data['web_SeqNumber'];
        $web_Barcode = $data['web_Barcode'];

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $sql_insert = "INSERT INTO [dbo].[WO_WebOrderSverka] (web_OrderId, web_OrderCode, web_ProductCode, web_Batch, web_SeqNumber, web_Barcode, web_User) VALUES($web_OrderId, '$web_OrderCode', '$web_ProductCode', '$web_Batch', '$web_SeqNumber', '$web_Barcode', '$user_email')";

        $sql_select = "SELECT * FROM [dbo].[WO_WebOrderSverka] WHERE web_Barcode = '$web_Barcode' AND web_OrderId = '$web_OrderId'";

        $sql_qty = "SELECT COUNT(web_OrderId) AS COUNT FROM [dbo].[WO_WebOrderSverka] WHERE web_OrderId = '$web_OrderId'";

        $conn = sqlsrv_connect($this->server, $connInfo);
        if($conn) {
            $stmt = sqlsrv_query($conn, $sql_select);
            if ($stmt === false) {
                die( print_r( sqlsrv_errors(), true));
            }
            $rows = [];
            $rows_qty = [];
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $rows[] = $row;
                }
            if (count($rows) == 0) {
                $stmt_insert = sqlsrv_query($conn, $sql_insert, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                if ($stmt_insert === false) {
                    die( print_r( sqlsrv_errors(), true));
                }
                $stmt_qty = sqlsrv_query($conn, $sql_qty);
                if ($stmt_qty === false) {
                    die( print_r( sqlsrv_errors(), true));
                }
                while ($row = sqlsrv_fetch_array($stmt_qty, SQLSRV_FETCH_ASSOC)) {
                    $rows_qty[] = $row;
                }
                return response()->json([
                    'status' => 'success',
                    'rows' => $rows_qty[0]
                ], 200);
            } else {
                $stmt_qty = sqlsrv_query($conn, $sql_qty);
                if ($stmt_qty === false) {
                    die( print_r( sqlsrv_errors(), true));
                }
                while ($row = sqlsrv_fetch_array($stmt_qty, SQLSRV_FETCH_ASSOC)) {
                    $rows_qty[] = $row;
                }
                return response()->json([
                   'status' => 'failure',
                    'rows' => $rows_qty[0]
                ], 404);
            }
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }

    }
    //API которая возвращает кол-во отсканированных коробов
    public  function getAggregationOrderScanQty(Request $request)
    {
        $data = $request->all();
        $web_OrderId = $data['web_OrderId'];

        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);
        if( $conn ) {
            $sqlCount = "SELECT count('$web_OrderId') as count
                         FROM [dbo].[WO_WebOrderSverka]
                         WHERE web_OrderId = '$web_OrderId'";
            $stmt = sqlsrv_query($conn, $sqlCount);
            if ( $stmt === false) {
                die( print_r( sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $logs[] = $row;
            }
            return json_encode($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }
}
