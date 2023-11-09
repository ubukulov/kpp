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

    public function getBarcodeForBosch()
    {
        return view('cabinet.barcode_bosch');
    }

    public function getOrders()
    {
        $connInfo = [
            'Database' => 'LV_SAMSUNG', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {
            /*$sql = "SELECT TOP 1500

                        LV_Order.ord_Code,
                        LV_OrderStatus.*
                    FROM LV_Order
                    INNER JOIN LV_OrderStatus ON LV_OrderStatus.ors_ID=LV_Order.ord_StatusID
                    WHERE ord_DepositorID = 11 AND LV_Order.ord_TypeID=25 ORDER BY ord_ID DESC";*/


            /*$sql = "SELECT TOP 1500
                    LV_Order.ord_Code,
                    isnull(LV_OrderAttributesValues.oav_Value,'no address') as address,
                    LV_OrderStatus.*
                    FROM
                    LV_Order
                    INNER JOIN LV_OrderStatus ON LV_OrderStatus.ors_ID=LV_Order.ord_StatusID
                    left outer join LV_OrderAttributesValues on LV_OrderAttributesValues.oav_OrderID = LV_Order.ord_ID
                    WHERE
                    ord_DepositorID = 11
                    AND LV_Order.ord_TypeID=25
                    AND LV_OrderAttributesValues.oav_AttributeID = 117
                    ORDER BY
                    ord_ID DESC";*/

            /*$sql = "SELECT TOP 1500
                 LV_Order.ord_Code,
		 av.oav_Value as KASPI_DO,
                 LV_Order.ord_CustomerOrderCode,
                 isnull(LV_OrderAttributesValues.oav_Value,'no address') as address,
                 LV_OrderStatus.*
                 FROM
                 LV_Order
                 INNER JOIN LV_OrderStatus ON LV_OrderStatus.ors_ID=LV_Order.ord_StatusID
                 left outer join LV_OrderAttributesValues on LV_OrderAttributesValues.oav_OrderID = LV_Order.ord_ID and LV_OrderAttributesValues.oav_AttributeID = 117
				  left outer join LV_OrderAttributesValues av on av.oav_OrderID = LV_Order.ord_ID and av.oav_AttributeID = 126
                 WHERE
                 ord_DepositorID = 11
                 AND LV_Order.ord_TypeID=25
                 ORDER BY
                 ord_ID DESC";*/

            $sql = "SELECT TOP 1500
                 LV_Order.ord_Code,
		         av.oav_Value as KASPI_DO,
				 'https://kaspi.kz/mc/api/merchant/kaspi-delivery/waybill?orderCode=' + av.oav_Value as KASPI_URL,
                 LV_Order.ord_CustomerOrderCode,
                 isnull(LV_OrderAttributesValues.oav_Value,'no address') as address,
                 LV_OrderStatus.*
                 FROM
                 LV_Order
                 INNER JOIN LV_OrderStatus ON LV_OrderStatus.ors_ID=LV_Order.ord_StatusID
                 left outer join LV_OrderAttributesValues on LV_OrderAttributesValues.oav_OrderID = LV_Order.ord_ID and LV_OrderAttributesValues.oav_AttributeID = 117
		 left outer join LV_OrderAttributesValues av on av.oav_OrderID = LV_Order.ord_ID and av.oav_AttributeID = 126

                 WHERE
                 ord_DepositorID = 11
                 AND LV_Order.ord_TypeID=25
                 ORDER BY
                 ord_ID DESC";



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

    public function getOrdersForBosch()
    {
        $connInfo = [
            'Database' => 'LV_SAVAGE', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {

            $sql = "SELECT ord_Code,
     COUNT(*) AS Count,
     orc_FullName as ADDRES
FROM LV_Order
LEFT JOIN LV_OrderShipment ON ost_OrderID = ord_ID
LEFT JOIN LV_ShipContainer ON ost_ID = shc_OrderShipmentID
LEFT JOIN LV_OrderCustomer ON orc_OrderID = ord_ID
WHERE ost_StatusID = 8 -- Статус Упакован
AND ord_DepositorID = 18
AND ord_StatusID = 2
AND ord_TypeID = 26
AND shc_ContainerID IS NOT NULL
GROUP BY ord_Code, orc_FullName";



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

    public function getSSCC(Request $request)
    {
        $order_code = $request->input('order_code');
        $connInfo = [
            'Database' => 'LV_SAVAGE', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        if ($conn) {

            $sql = "SELECT
       orc_FullName AS ADDRES,
       ord_Code + '; BRT ' + CAST(CAST(stk.stc_GrossWeight AS DECIMAL(18, 3)) AS VARCHAR) + '; QTY ' + CAST(SUM(CAST(stc.stk_CUQuantity AS DECIMAL(18, 0))) AS VARCHAR) AS BARCODE,
	   stk.stc_SSCC,
	   stc_ID
FROM LV_Order
LEFT JOIN LV_OrderShipment ON ost_OrderID = ord_ID
LEFT JOIN LV_ShipContainer ON ost_ID = shc_OrderShipmentID
LEFT JOIN LV_StockContainer stk ON stk.stc_ID = shc_ContainerID
LEFT JOIN LV_Stock stc ON stc.stk_ContainerID = stk.stc_ID
LEFT JOIN LV_OrderCustomer ON orc_OrderID = ord_ID
WHERE ost_StatusID = 8
  AND ord_DepositorID = 18
  AND ord_StatusID = 2
  AND ord_TypeID = 26
  AND shc_ContainerID IS NOT NULL
 and ord_Code like '%$order_code%'  -- Условие на выборку ord_Code
GROUP BY stc_ID,ord_Code, orc_FullName, stk.stc_GrossWeight,ord_ID,stk.stc_SSCC
";

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
        $ord_CustomerOrderCode = $request->input('ord_CustomerOrderCode');
        $KASPI_DO = $request->input('KASPI_DO');
        //$direction = utf8_encode($request->input('direction'));
        $direction = $request->input('direction');
        //dd($direction);

        if ($count == 1) {
            $this->print($order_code, $count, 0, $direction, $ord_CustomerOrderCode, $KASPI_DO);
        }

        if ($count > 1) {
            for($i=1; $i<=$count; $i++) {
                $this->print($order_code, $count, $i, $direction, $ord_CustomerOrderCode, $KASPI_DO);
            }
        }

        return response('Принтер распечатал этикентки');
    }

    public function printOrdersBosch(Request $request)
    {
        $order_code = $request->input('order_code');
        $sscc_codes = json_decode($request->input('sscc_codes'));
        //dd($sscc_codes[0]->ADDRES);
        //dd(json_decode($sscc_codes));

        $index = 1;

        for($i = 0; $i <= count($sscc_codes); $i++) {
            if($index <= 9) {
                $order_codeName = $order_code . "-00" . $index;
            } else {
                $order_codeName = $order_code . "-0" . $index;
            }

            $this->printBosch($sscc_codes[$i], $order_codeName, $index, count($sscc_codes));
            $index++;
        }

        return response('Принтер распечатал этикентки');
    }

    public function print($order_code, $count, $i=0, $direction, $ord_CustomerOrderCode, $KASPI_DO)
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
            ], 400);
        }

        $date = date("d.m.Y H:i");

        if ($i == 0) $i = 1;

        if($KASPI_DO == 'null') {
            $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW799
^LL0406
^LS0
^FT19,34^A0N,28,56^FH\^CI28^FD$direction^FS^CI27
^FT561,34^A0N,28,30^FH\^CI28^FD$date^FS^CI27
^FT110,123^A0N,79,127^FH\^CI28^FD$order_code^FS^CI27
^BY6,3,131^FT137,281^BCN,,N,N
^FH\^FD>;$order_code^FS
^FT593,324^A0N,39,38^FH\^CI28^FDPack:  $i/$count^FS^CI27
^FT520,384^A0N,45,46^FH\^CI28^FD$ord_CustomerOrderCode^FS^CI27
^PQ1,0,1,Y^XZ
HERE;
        } else {
            $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW799
^LL0406
^LS0
^FT19,34^A0N,28,56^FH\^CI28^FD$direction^FS^CI27
^FT561,34^A0N,28,30^FH\^CI28^FD$date^FS^CI27
^FT110,123^A0N,79,127^FH\^CI28^FD$order_code^FS^CI27
^FT19,384^A0N,45,46^FH\^CI28^FD$KASPI_DO^FS^CI27
^BY6,3,131^FT137,281^BCN,,N,N
^FH\^FD>;$order_code^FS
^FT593,324^A0N,39,38^FH\^CI28^FDPack:  $i/$count^FS^CI27
^FT593,384^A0N,45,46^FH\^CI28^FD$ord_CustomerOrderCode^FS^CI27
^PQ1,0,1,Y^XZ
HERE;
        }

        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 403);
        }
    }

    public function printBosch($sscc_codes, $order_codeName, $i, $count)
    {
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;
        $address = $sscc_codes->ADDRES;
        $barcode = $sscc_codes->BARCODE;
        $ss_code = $sscc_codes->stc_SSCC;

        $printer = "\\\\".$computer_name.$printer_name;
        // Open connection to the thermal printer


        $fp = fopen($printer, "w");
        if (!$fp){
//            die('no connection');
            return response([
                'data' => [
                    'message' => 'no connection to printer'
                ]
            ], 400);
        }


        $data = <<<HERE
^XA^LRN^CI28^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW812
^LL0812
^LS0
^FT34,125^A0N,63,84^FH\^FD$order_codeName^FS
^BY2,3,91^FT67,241^BAN,,N,N^FH\^FD>$barcode^FS
^FT21,48^A0N,45,99^FH\^FDНомер заказа^FS
^FT21,315^A0N,28,76^FH\^FDАдрес^FS
^FT21,358^A0N,31,33^FH\^FD$address^FS
^FT23,282^A0N,28,38^FH\^FDSSCC:^FS
^FT125,282^A0N,28,33^FH\^FD$ss_code^FS
^FT612,312^A0N,51,66^FH\^FD$i/$count^FS
^PQ1,0,1,Y^XZ
HERE;

        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 403);
        }
    }

    public function customs()
    {
        return view('cabinet.customs');
    }
}
