<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseWmsController;
use App\Http\Controllers\Controller;
use App\Models\PalletSSCC;
use Illuminate\Http\Request;
use Auth;

class WmsController extends BaseWmsController
{
    public function __construct()
    {
        parent::__construct();
        if(!$this->conn){
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function orders()
    {
        $sql = "SELECT
                    convert(varchar(20),ord_InputDate,120)as ordDate
                    --convert(date, ord_InputDate) as ordDate
                    --,substring(convert(varchar(20),ord_InputDate,114),1,5) as ordTime
                    ,ord_Code
                    ,V_OrderStatus.status
                    --,pav_Value
                    ,prd_PrimaryCode
                    ,prdl_Description
                    ,convert(int,osi_QuantitySU) Qty
                    ,isnull(oav_Value,'no address') as region
                    ,case when oav_Value <> 'Алматы' and pav_Value = '400'  then 'Обрешетка' else '-' end as Obreshetka
                    FROM
                    LV_Order
                    left join LV_OrderAttributesValues on oav_OrderID = LV_Order.ord_ID
                    left join LV_OrderItem on ori_OrderID = ord_ID
                    left join LV_OrderShipItem on osi_OrderItemID = ori_ID
                    left join V_OrderStatus ON V_OrderStatus.ors_ID=osi_StatusID
                    left join LV_Product on prd_ID = ori_ProductID
                    left join LV_ProductLang on prdl_ProductID = prd_ID
                    left join LV_ProductAttributesValues on pav_ProductID = prd_ID

                    WHERE
                    ord_DepositorID = 11
                    AND ord_TypeID=25
                    AND ord_StatusID not in (3,4)
                    AND osi_StatusID <> 11
                    AND prdl_LanguageID = 4
                    AND V_OrderStatus.LanguageID=4
                    AND oav_AttributeID = 117
                    AND pav_attributeID = 12
                    --and ord_ID in ({results})
                    ORDER BY 1,2,4";
        $logs = $this->query($sql);

        return view('cabinet.wms.index', compact('logs'));
    }

    public function boxes()
    {
        $sql = "SELECT loc_Description
                    ,count(CASE WHEN loc_CapacityStatusID IN (2,3) THEN loc_id END) AS 'not_empty'
                    ,count(CASE WHEN loc_CapacityStatusID IN (1) THEN loc_id END) AS 'empty'

                    FROM
                    LV_Location AS ll (NOLOCK)
                    where
                        loc_depositorID = 11
                        and loc_Description like 'eStore%'
                    GROUP BY
                    loc_Description
                    ";
        $logs = $this->query($sql);

        return view('cabinet.wms.boxes', compact('logs'));
    }

    public function resendReceive()
    {
        $sql = "
            Select top(1500)
                *
            from
                ant_ExportGRINFO
                left outer join lv_Receipt on rct_ID = ReceiptID
                left outer join LV_ReceiptAttributesValues on rct_ID = rav_ReceiptID
            where
                status = 1
                and rct_TypeID in (1,7) and rav_AttributeID=88
                ORDER BY rct_ID DESC
        ";

        $sql2 = "SELECT TOP 1500 * FROM ant_ExportGIINFO
            left outer join LV_Order ON OrderID = ord_ID
            left outer join LV_OrderAttributesValues ON OrderID = oav_OrderID
            where
            status = 1
            AND ord_TypeID in (25,11,12) and oav_AttributeID = 21
            ORDER BY ord_ID DESC
            ";

        $returnSql = "SELECT TOP 1500 *
            from
            ant_ExportLRT
            left outer join lv_Receipt on rct_ID = ReceiptID
            left outer join LV_ReceiptAttributesValues on rav_ReceiptID = ReceiptID
            where
            status = 1
            and rct_TypeID = 10
            and rav_AttributeID = 88

            order by DateInsert DESC";
        $items = $this->query($sql);
        $items2 = $this->query($sql2);
        $itemsReturn = $this->query($returnSql);
        $logs = [];
        $ships = [];
        $returns = [];

        foreach ($items as $item) {
            $item['rct_InputDate'] = (is_null($item['rct_InputDate'])) ? "" : $item['rct_InputDate']->format('Y-m-d H:i:s');
            $item['rct_ActualDate'] = (is_null($item['rct_ActualDate'])) ? "" : $item['rct_ActualDate']->format('Y-m-d H:i:s');
            $logs[] = $item;
        }

        foreach ($items2 as $item) {
            $item['ord_InputDate'] = (is_null($item['ord_InputDate'])) ? "" : $item['ord_InputDate']->format('Y-m-d H:i:s');
            $ships[] = $item;
        }

        foreach ($itemsReturn as $item) {
            $item['DateInsert'] = (is_null($item['DateInsert'])) ? "" : $item['DateInsert']->format('Y-m-d H:i:s');
            $returns[] = $item;
        }

        return view('cabinet.wms.resend', compact('logs', 'ships', 'returns'));
    }

    public function estore()
    {
        $sql = "SELECT
	isnull(loc_Description, '-') [Тип ячейки]
	,isnull(loc_Code, '-') [Ячейка]
	,isnull(prd_PrimaryCode, '-') [Артикул]
    ,isnull(prdl_Description, '-') [Описание]
	,isnull(convert(int, spt_Quantity), '-') [Кол-во]
	,isnull(convert(int, spt_QuantityFree), '-') [Доступно]
	,isnull(part.sav_Value, '-') [StorageLocation]
FROM
	LV_Location

	LEFT OUTER JOIN LV_Stock ON LV_Stock.stk_LocationID = loc_ID

	LEFT OUTER JOIN LV_Product ON LV_Product.prd_ID = stk_ProductID
	LEFT OUTER JOIN LV_ProductLang ON LV_ProductLang.prdl_ProductID = prd_ID

	LEFT OUTER JOIN LV_StockPackType ON LV_StockPackType.spt_StockID = stk_ID
	LEFT OUTER JOIN LV_StockAttributesValues part ON part.sav_StockID = stk_ID

WHERE
	loc_DepositorID = 11
	AND spt_ParentId IS NULL
	AND (prdl_LanguageID = 4 or prdl_LanguageID is NULL)
	AND (part.sav_AttributeID = 24 or part.sav_AttributeID is NULL)
	AND loc_Description like 'eStore%'
	AND loc_LockReasonID is null

order by loc_Description, substring(loc_Code,1,2), loc_SectorCode, loc_ColumnCode, loc_LevelCode
                    ";

        $logs = $this->query($sql);

        return view('cabinet.wms.estore', compact('logs'));
    }

    public function resendUpdate($type, $receiptID)
    {
        if($type == '9_1') {
            $sql = "UPDATE ant_ExportGRINFO SET Status = 0, Resend = 9
WHERE
ReceiptID in ($receiptID)";

            sqlsrv_query($this->conn, $sql);
        }

        if($type == '7_2') {
            $sql = "UPDATE ant_ExportGRINFO SET Status = 0, TryNumber=TryNumber+1, Resend = 7
WHERE
ReceiptID in ($receiptID)";

            sqlsrv_query($this->conn, $sql);
        }

        return back();
    }

    public function resendShip($type, $shipID)
    {
        if($type == '9_1') {
            $sql = "UPDATE ant_ExportGIINFO SET Status = 0, Resend = 9
                    WHERE
                    OrderID in ($shipID)
                    ";

            sqlsrv_query($this->conn, $sql);
        }

        if($type == '7_2') {
            $sql = "UPDATE ant_ExportGIINFO SET Status = 0, TryNumber=TryNumber+1, Resend = 7
                    WHERE
                    OrderID in ($shipID)
                    ";

            sqlsrv_query($this->conn, $sql);
        }

        return back();
    }

    public function resendAckanUpdate($receiptID)
    {
        $sql = "update ant_importackans set status=0 where MessageNumber in
                (select rav_Value from LV_ReceiptAttributesValues where  rav_AttributeID=88 and rav_ReceiptID = $receiptID)

                    ";

        sqlsrv_query($this->conn, $sql);

        return back();
    }

    public function resendShipAckanUpdate($OrderID)
    {
        $sql = "update ant_importackans set status = 0 where MessageNumber in (
select oav_Value from LV_OrderAttributesValues
where oav_AttributeID = 21
and oav_OrderID = $OrderID )";

        sqlsrv_query($this->conn, $sql);

        return back();
    }

    public function resendReturnGrin($receiptID)
    {
        $sql = "update ant_ExportLRT set Status = 0, TryNumber=TryNumber+1

where ReceiptID in ($receiptID)";

        sqlsrv_query($this->conn, $sql);

        return back();
    }

    public function resendReturnAckan($receiptID)
    {
        $sql = "update ant_importackans set status=0 where MessageNumber in
(select rav_Value from LV_ReceiptAttributesValues where  rav_AttributeID=88 and rav_ReceiptID = $receiptID)";

        sqlsrv_query($this->conn, $sql);

        return back();
    }

    public function barcodeForWmsBoxes()
    {
        $connInfo = [
            'Database' => 'LVISION5', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        $clientsSQL = "SELECT dep_ID, dep_Code FROM LV_Depositor";


        $stmt = sqlsrv_query($conn, $clientsSQL);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $clients = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $clients[] = $row;
        }



        $boxesSQL = "SELECT loc_SectorCode FROM LV_Location
                     GROUP BY loc_SectorCode ORDER BY loc_SectorCode";

        $stmt = sqlsrv_query($conn, $boxesSQL);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $boxes = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $boxes[] = $row;
        }

        return view('cabinet.barcode.jti', compact('clients', 'boxes'));
    }

    public function barcodeForWmsBoxes2(Request $request)
    {
        $client_id = $request->input('client_id');
        $box_id = $request->input('box_id');

        $connInfo = [
            'Database' => 'LVISION5', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        $clientsSQL = "select top 300 loc_Code from LV_Location
                       where loc_DepositorID='$client_id' AND loc_SectorCode='$box_id'";


        $stmt = sqlsrv_query($conn, $clientsSQL);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $clients = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $clients[] = $row;
        }

        return $clients;
    }

    public function getClientBoxes($dep_ID)
    {
        $connInfo = [
            'Database' => 'LVISION5', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        $clientsSQL = "select loc_SectorCode from LV_Location
                        where loc_DepositorID = $dep_ID
                        group by loc_SectorCode";


        $stmt = sqlsrv_query($conn, $clientsSQL);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $boxes = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $boxes[] = $row;
        }

        return $boxes;
    }

    public function jtiCommandPrint(Request $request)
    {
        $box_code = $request->get('code');
        return view('cabinet.barcode.print', compact('box_code'));
    }

    public function palletSSCC()
    {
        $pallet_sscc = PalletSSCC::orderBy('id', 'DESC')->get();
        return view('cabinet.wms.pallet.sscc', compact('pallet_sscc'));
    }

    /*
     * min: 1330000000000
     * max: 1340000000000
     */
    public function generatePalletSSCC()
    {
        $palletSSCC = PalletSSCC::find(1);
        if(!$palletSSCC) {
            PalletSSCC::create([
                'user_id' => Auth::id(), 'code' => 1330000000000
            ]);
        } else {
            $palletSSCC = PalletSSCC::orderBy('id', 'DESC')->first();
            if($palletSSCC->code < 1340000000000) {
                $code = $palletSSCC->code + 1;
                PalletSSCC::create([
                    'user_id' => Auth::id(), 'code' => $code
                ]);
            }
        }
        return redirect()->route('cabinet.wms.palletSSCC');
    }

    public function printPalletSSCC(Request $request, $code)
    {
        $code = $request->input('code');
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

        $data = <<<HERE
^XA^LRN^CI0^XZ
^XA
^MMT
^PW812
^LL0440
^LS0
^FT115,94^A0N,56,72^FH\^FDRobert Bosch Pallet^FS
^BY6,3,180^FT101,293^BCN,,Y,N
^FD>;$code^FS
^PQ1,0,1,Y^XZ
HERE;

        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }
    }

    public function sss()
    {
        $connInfo = [
            'Database' => 'LV_SAVAGE', 'UID' => $this->username, 'PWD' => $this->password,
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect($this->server, $connInfo);

        $clientsSQL = "select prd_PrimaryCode -- АРТИКУЛ
, prdl_Description -- ОПИСАНИЕ
, loc_Code -- ЯЧЕЙКИ
, spt_Quantity -- КОЛИЧЕСТВО
from LV_Product
 inner join LV_ProductDepositor on pdp_ProductID = prd_ID
inner join LV_ProductLang on prdl_ProductID = prd_ID
inner join LV_Stock on stk_ProductID = prd_ID
inner join LV_StockPackType on stk_ID = spt_StockID
inner join LV_Location on loc_id = stk_LocationID
inner join LV_ProductAttributesValues on prd_id = pav_ProductID

where pdp_DepositorID = 18
and prdl_LanguageID = 4
and spt_ParentId is null
and pav_attributeID = 76
and loc_StorageSystemID = 27
and loc_Code like '2p%'

order by prd_PrimaryCode";


        $stmt = sqlsrv_query($conn, $clientsSQL);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $boxes = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $boxes[] = $row;
        }

        dd($boxes);
    }
}
