<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScangoController extends Controller
{
    protected $username = 'ScanGo';
    protected $password = '128500';
    protected $server   = '10.1.0.18\DLSQL';
    protected $database = 'SCANGO';

    public function scanGoChecking(Request $request)
    {
        $barcode = $request->input('barcode');
        $connInfo = [
            'Database' => $this->database, 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);
        if( $conn ) {
            $pos = stripos($barcode, "-");
            if ($pos !== false) {
                $doc = explode('-', $barcode);
                $db_id = $doc[0];
                $doc_id = $doc[1];
                $Script1 = "SELECT TOP 1 convert(varchar,DATE_SCAN,120) AS DATE_SCAN FROM LOG_SCAN WHERE SCAN = '$barcode' AND DATEDIFF(mi,DATE_SCAN, getdate()) > 5 AND ERROR_DESCRIPTION IS NULL ORDER BY ID DESC";
                $stmt = sqlsrv_query($conn, $Script1, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                if( $stmt === false ) {
                    die( print_r( sqlsrv_errors(), true));
                }
                if (sqlsrv_has_rows($stmt)) {
                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    $double_scan = date( 'd-m-Y H:i', strtotime($row['DATE_SCAN']));
                    $datetime = date('Y-m-d H:i:s');
                    $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime',NULL,NULL,NULL,'$barcode','Штрих-код уже сканировался СБ!', 'kpp')";
                    $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                    if( $stmt === false ) {
                        die( print_r( sqlsrv_errors(), true));
                    }
                    $MailMessage='Тревога! Обнаружено нарушение!<br><font color="red">'.$barcode.'</font>: Данный Штрих-код уже сканировался СБ!';
                    $this->sendMail($MailMessage);
                    $result = '<div class="scan-error" align="center"><span>ВНИМАНИЕ!<br>Штрих-код: </span>'.$barcode.'<br><span>Уже сканировался: </span>'.$double_scan.'</div>';
                    return response(['data' => $result], 200);
                } else {
                    $this->database = 'master';
                    $conn = sqlsrv_connect($this->server, $connInfo);
                    if($conn) {
                        $sql = "SELECT
                                    NAME
                                FROM SYS.DATABASES
                                WHERE DATABASE_ID = $db_id
                                    AND NAME NOT IN ('master','tempdb','model','msdb')
                                    AND STATE_DESC = 'ONLINE'";
                        $stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => SQLSRV_CURSOR_DYNAMIC ));
                        if (sqlsrv_has_rows($stmt)) {
                            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                            $this->database = $row['NAME'];
                            $conn = sqlsrv_connect($this->server, $connInfo);
                            if ($conn){
                                $sql = "SELECT
                                            ORD_CODE, DEP_CODE
                                        FROM [$this->database].[dbo].[LV_ORDER]
                                            INNER JOIN [$this->database].[dbo].[LV_DEPOSITOR] ON ORD_DEPOSITORID = DEP_ID
                                        WHERE ORD_ID = $doc_id";
                                $stmt = sqlsrv_query($conn, $sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                                if( $stmt === false ) {
                                    die( print_r( sqlsrv_errors(), true));
                                }
                                if (sqlsrv_has_rows($stmt)) {
                                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                                    $dep_code = $row['DEP_CODE'];
                                    $ord_code = $row['ORD_CODE'];
                                    $sql = "SELECT
                                                LOG_DOCSHIPDATE
                                                ,LOG_DOCSHIPTIME
                                                ,LOG_DOCSHIPALL
                                            FROM(
                                            SELECT TOP 1
                                                convert(varchar,MAX(LOG_DATETIME),4)  AS LOG_DOCSHIPDATE
                                                ,convert(varchar,MAX(LOG_DATETIME),8) AS LOG_DOCSHIPTIME
                                                ,convert(varchar,MAX(LOG_DATETIME),120) AS LOG_DOCSHIPALL
                                            FROM [$this->database].[dbo].[LV_LOG]
                                                INNER JOIN [$this->database].[dbo].[LV_ORDERSHIPITEM] ON LOG_ORDERSHIPITEMID = OSI_ID
                                                INNER JOIN [$this->database].[dbo].[LV_ORDERSHIPMENT] ON OST_ID = OSI_ORDERSHIPMENTID
                                                INNER JOIN [$this->database].[dbo].[LV_ORDER] ON ORD_ID = OST_ORDERID
                                                INNER JOIN [$this->database].[dbo].[LV_DEPOSITOR] ON ORD_DEPOSITORID = DEP_ID

                                            WHERE LOG_TRANSACTIONTYPEID IN (10)
                                                AND OST_STATUSID = 10
                                            --	AND ORD_STATUSID = 3
                                                AND ORD_ID = $doc_id
                                            )tab
                                            WHERE LOG_DOCSHIPDATE IS NOT NULL
                                                AND LOG_DOCSHIPTIME IS NOT NULL";
                                    $stmt = sqlsrv_query($conn, $sql, array(), array( "Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                                    if( $stmt === false ) {
                                        die( print_r( sqlsrv_errors(), true));
                                    }
                                    if (sqlsrv_has_rows($stmt)) {
                                        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                                        $shipment_date_all = $row['LOG_DOCSHIPALL'];
                                        $shipment_date = $row['LOG_DOCSHIPDATE'];
                                        $shipment_time = $row['LOG_DOCSHIPTIME'];
                                        $datetime = date('Y-m-d H:i:s');
                                        $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime','$dep_code','$ord_code','$shipment_date_all','$barcode',NULL, 'kpp')";
                                        $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                                        if( $stmt === false ) {
                                            die( print_r( sqlsrv_errors(), true));
                                        }
                                        $result = '<div class="scan-succes" align="center">Все впорядке!<br>Можете пропустить машину!<br>Клиент: <span>'
                                            .$dep_code.'</span><br>Расходная накладная: <span>'
                                            .$ord_code.'</span><br>Дата отгрузки: <span>'
                                            .$shipment_date.'<br></span>Время отгрузки: <span>'
                                            .$shipment_time.'</span></div>';
                                        return response(['data' => $result], 200);
                                    } else {
                                        $datetime = date('Y-m-d H:i:s');
                                        $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime','$dep_code','$ord_code',NULL,'$barcode','Накладная не Отгружена в WMS', 'kpp')";
                                        $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                                        if( $stmt === false ) {
                                            die( print_r( sqlsrv_errors(), true));
                                        }
                                        $MailMessage='Тревога! Обнаружено нарушение!<br><font color="red">'.$barcode.'</font>: Накладная не Отгружена в WMS!';
                                        $this->sendMail($MailMessage);
                                        $result = '<div class="scan-error" align="center"><span>ВНИМАНИЕ!<br>Накладная: </span>'.$ord_code.'<br><span>Не отгружалась на складе!</span></div>';
                                        return response(['data' => $result], 200);
                                    }
                                } else {
                                    $datetime = date('Y-m-d H:i:s');
                                    $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime',NULL,NULL,NULL,'$barcode','Накладная не найдена в WMS', 'kpp')";
                                    $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                                    if( $stmt === false ) {
                                        die( print_r( sqlsrv_errors(), true));
                                    }
                                    $MailMessage='Тревога! Обнаружено нарушение!<br><font color="red">'.$barcode.'</font>: Не найден документ!';
                                    $this->sendMail($MailMessage);
                                    $result = '<div class="scan-error" align="center"><span>ВНИМАНИЕ!<br>Данный штрих-код: </span>'.$barcode.'<br><span>Является ложным!</span><br>Не найден документ!</div>';
                                    return response(['data' => $result], 200);
                                }
                            } else {
                                echo "Connection could not be established.<br />";
                                die( print_r( sqlsrv_errors(), true));
                            }
                        } else {
                            $datetime = date('Y-m-d H:i:s');
                            $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime',NULL,NULL,NULL,'$barcode','Не найден ID БД!', 'kpp')";
                            $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                            if( $stmt === false ) {
                                die( print_r( sqlsrv_errors(), true));
                            }
                            $MailMessage='Тревога! Обнаружено нарушение!<br><font color="red">'.$barcode.'</font>: Не найден Клиент!';
                            $this->sendMail($MailMessage);
                            $result = '<div class="scan-error" align="center"><span>ВНИМАНИЕ!<br>Данный штрих-код: </span>'.$barcode.'<br><span>Является ложным!</span><br>Не найден клиент!</div>';
                            return response(['data' => $result], 200);
                        }
                    }else{
                        echo "Connection could not be established.<br />";
                        die( print_r( sqlsrv_errors(), true));
                    }
                }
            } else {
                $datetime = date('Y-m-d H:i:s');
                $insert_sql = "INSERT INTO [dbo].[LOG_SCAN](DATE_SCAN,DEPOSITOR_CODE, ORDER_CODE, LOG_DOCSHIPDATE, SCAN, ERROR_DESCRIPTION, [USER]) VALUES('$datetime',NULL,NULL,NULL,'$barcode','Посторонний Штрих-код без знака -', 'kpp')";
                $stmt = sqlsrv_query($conn, $insert_sql, array(), array("Scrollable" => SQLSRV_CURSOR_DYNAMIC));
                if( $stmt === false ) {
                    die( print_r( sqlsrv_errors(), true));
                }
                $MailMessage='Тревога! Обнаружено нарушение!<br><font color="red">'.$barcode.'</font>: Посторонний Штрих-код без знака -';
                $this->sendMail($MailMessage);
                $result = '<div class="scan-error" align="center"><span>ВНИМАНИЕ!<br>Данный штрих-код: </span>'.$barcode.'<br><span>Является ложным!</span><br>Не найден символ "-"</div>';
                return response(['data' => $result], 200);
            }
        }else{
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function sendMail($message)
    {
        $to      = 'SCANGO@htl.kz';
        $subject = 'Внимание! Важное сообщение!';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'To: SCANGO@htl.kz';
        $headers .= 'From: SCAN&GO <order@htl.kz>' . "\r\n";
        mail($to, $subject, $message, $headers);
    }

    public function getLast5Logs()
    {
        $connInfo = [
            'Database' => 'SCANGO', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];
        $conn = sqlsrv_connect($this->server, $connInfo);
        if( $conn ) {
            $sql = "SELECT TOP (5) * FROM LOG_SCAN ORDER BY ID DESC";
            $stmt = sqlsrv_query($conn, $sql);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $logs = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $row['DATE_SCAN'] = $row['DATE_SCAN']->format('Y-m-d H:i:s');
                $logs[] = $row;
            }
            return json_encode($logs);
        } else {
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }
}
