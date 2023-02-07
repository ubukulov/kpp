<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseWmsController;
use App\Http\Controllers\Controller;
use App\Models\Bosch;
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

    public function boschInvoices()
    {
        $str = "Гарантия:  Изготовитель, поставщик: Роберт Бош Пауэр Тулз ГмбХ,";
        $boschs = Bosch::orderBy('id', 'DESC')->get();
        return view('cabinet.wms.bosch.invoices', compact('boschs'));
    }

    public function boschImport(Request $request)
    {
        $file = $request->file('file');
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file->getPathname());
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file->getPathname());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key=>$arr) {
            if($key == 1) continue;
            Bosch::create([
                'article' => $arr['A'], 'description_ru' => $arr['B'], 'description_kz' => $arr['C'], 'count' => $arr['D'],
                'invoice' => $arr['E'], 'cert' => $arr['F']
            ]);
        }

        return redirect()->route('cabinet.wms.boschInvoices');
    }

    public function boschPrint($bosch_id)
    {
        $bosch = Bosch::findOrFail($bosch_id);
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;

        $computer_name = 'WO-2092';
        $printer_name = '\ZDesigner ZD220-203dpi ZPL';
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

        $txt_ru = '';
        $arr = [];
        $arrRU = explode(" ", $bosch->description_ru);
        foreach ($arrRU as $item) {
            if($txt_ru == '') {
                $txt_ru = $item;
            } else {
                $all = mb_strlen($txt_ru);
                $sizeItem = mb_strlen($item);
                $sum = $all + $sizeItem;
                if($all < 64 && $sum < 64) {
                    $txt_ru .= " ".$item;
                } else {
                    $arr[] = $txt_ru;
                    $txt_ru = $item;
                }
            }
        }

        if($txt_ru != '') {
            $arr[] = $txt_ru;
        }

        $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:tt0003m_^FS^XZ
^XA
^MMT
^PW831
^LL406
^LS0
HERE;

        $val = 25;
        foreach($arr as $item) {
            $data .= <<<HERE
               ^FT23,$val^A0N,11,10,TT0003M_^FH\^CI17^F8^FD$item^FS^CI0
HERE;
            $val += 25;
        }

        $data .= <<<HERE
            ^FO340,6^GB0,394,3^FS
HERE;

        $txt_ru = '';
        $arr = [];
        $arrRU = explode(" ", $bosch->description_kz);
        foreach ($arrRU as $item) {
            if($txt_ru == '') {
                $txt_ru = $item;
            } else {
                $all = mb_strlen($txt_ru);
                $sizeItem = mb_strlen($item);
                $sum = $all + $sizeItem;
                if($all < 64 && $sum < 64) {
                    $txt_ru .= " ".$item;
                } else {
                    $arr[] = $txt_ru;
                    $txt_ru = $item;
                }
            }
        }

        if($txt_ru != '') {
            $arr[] = $txt_ru;
        }

        $val = 25;
        foreach($arr as $item) {
            $data .= <<<HERE
               ^FT351,$val^A0N,11,10,TT0003M_^FH\^CI17^F8^FD$item^FS^CI0
HERE;
            $val += 25;
        }

        $article = $bosch->article;
        $val += 25;
        $data .= <<<HERE
^FT16,$val^A0N,28,28,TT0003M_^FH\^CI17^F8^FD$article^FS^CI0
^FT351,$val^A0N,28,28,TT0003M_^FH\^CI17^F8^FDEAC^FS^CI0
^FO706,6^GB0,394,3^FS
HERE;



        $data .= <<<HERE
^FO726,18^GFA,449,744,12,:Z64:eJyFkb9Lw0AUx7/XUyMOTQS7tQScJINzkdIq6O7iJvgvpJtDkRMHC4X+Ec7SwbGL2XQQN0ch4OAmdTuo5Ly75N5l8McLhC8fLu99Xg74oxqFz1z5HCpBOXjIPL/KKcfM8z7zvI0F5R4k5XP9uJLo0YAFOtQox8Bx3T0mtRz3jgcZzkhNIKHsFLUJFxJGqGNe7MuK7um8w2eWRynYiAVIdV5fTkWBqbCiwyeMsFaK5hIXaKJrXVIM9WJtlL2l6KJvZ6Z68dSKNiG1Zo7Q/AJ2yjK2b50Gy2ee8UKEeoH48zrImDI7ILlphILPzW5Wu9oZtpXJJY9r3JwR1fmgzoW4HE8q/nJy8KpKzpV6U9VNMqXelbthwz/cj0PtHnV//Fy/cV+bRy0XG8ns0X3AtlaOHed3uxOaGq1GdH5+O6YBrUPfZ1ts/Du3qm/aa2bL:FA7A
^FO726,84^GFA,461,744,12,:Z64:eJylkrFOwzAQhn/LDc1QtSxIDFD6DiwdUMjAyNKBHd4gHRCVqKhRlwwofQ2eAjIy8AgMGTulQQipUlHN2YnPlRi5SMmXP/bdf+cAJp4VOGYepfa60LnXVwVz+9FzT1WekTD31Zr5CFPmiC4XU/riYo0+J03Q48IVVXB2CpxYIEEWeDV6EBPnrcCWpwRSRRiYkuREUPp94uEkRUi6NTQO0KLC1uhphi1KTAzffOBBXOLMcPGDaJ5ZoyKfYCY61qhUQ9zPX9A1I6BdidT0BL1vsZZbZYwej95FhQ7axOdfGdns0j7gYBSInCrTjbyoeYHQ9GCnaXoVVTPNumerN7Nw+qBu4I9+DZ8nFjXLslxqXbPQ+lO7k9R6o90J396Nv6+a2YZpukz5MBB7tOP8d8jFImvKQrQu3ljvllyW9JD1bLW73ut7Zbiz/pD18on/Exe/ewVnbA==:DA65
^FO726,152^GFA,481,768,12,:Z64:eJyVkbFqwzAQhk+1TEIbYg9dCiH2WDp16GBKcFzc7B3a9/CWDgUrZOmUvkLGkqHP4EAfRGAomUw6FDwEqyerkjwFesb2x6/T6b8TwLGYcsv53rKoLf9YdrhlUkw6emy4V4wNe8yzOj4d/VXzGHdojsExB0yAGEN1R8+g19HPdAMmmeGLYh9/riycAFB5ZLseXkmjg2tcg+wmQB5GjInFR/WG++jFFoIFTVujUck8Niw9WS+bQ1Ccp63RXQNxEZVLLkcwglHipwR1Zx2jfV46XLY6gWdIUpJhfdawmuwqhg14D3PYk8MLLoJXl4v9yeYSIoDge0u46hlO39mSq1moacrvSE2ZqXm1XHS4bVflSyu+zudheLtplP7z9JjrWxWiEZpzccjF3+ymoplu1uaWfLARwX+i345fhXvnJpqH5YprpjMaah5UK3Os+zmz+asvZvLde1Nz0K8MU58es/ILFUhveg==:7077
^FO722,221^GFA,493,804,12,:Z64:eJyFUbFKw1AUPc8EXunQuAgOwdTVRbplKKn+QQdHBz8hg9AlmFtLQad+go41g7ObEdz1EzIWClK3iOLzvjZ5L4t6IeFw3uWec88F/iqntFh+Wex9Wxwoi6NDMrgvLV96ucGxtLh0ihqKlVgZ2QJGWOSwfI7PWoCnp8YO4bHmPeDC2AGMUR84afDtBq9r65Z/LZbgOfJIM66Wg8dG2sVgMmGjfsxvRW86ZhyG2ub+jLS53rG2PyV+R7gkp3SuSS8QjyBfpaCEcRHBu+o4FOmVfLbvCq3t5MxTKCgi5nwE2BUUAB02GWEwJt5tDyP0sSP0zkG55HGt9c7Bx1OeVLkcZJdrSVSJxlV265T5o4pnu6LBO3V/ohOtLhNl2UypTaCBUi+quvBAqRtVBSrTVKbJBre5P5vXgaJxd55vS+Df6na7pnt4Zu4l5os3g93hs+Uf7g1+3z43+K5cGCxOc+Nm0iHrxrW6BMLv9QNW626m:42D9
^FO726,294^GFA,465,792,12,:Z64:eJyVkLFqwzAQhs9xiUtcYgqFZnDtjN3i0ZRiu5AH8JC+h7YsBWvs1EcoHkOGPEKrsVOeQaV7CHQxJcQ5WbbOU2lvkD5+pLv/foDfyi2Jwz1xWhEXB+KfhJgFBi0ZcsPCM7otPaM7whEde9yVRscfRgd7Z+yAZQbjVMP3PT0G2xhlYDG8BmojFM9Va7URs3Rr9XDqSRev4aQZzBw8x3HJZzyoGqP+pwiFD43R+JvPJA5QRqMlapdZY7RKuCdT2ejShzC7YdZerRpAMk1iNdtG8z5MIvSEq77zN4gb3cmX8AR+hAf6+eIVJJHaLTw8CIZ+ABMdrXhZgSdBJ4etLZWLTlNnp1PW2XX6oKef9fRx+1dZRLM6quNmk9ZHzUVdp3UbKIpp3QZ6vSzCx1zzaL1+feZd0HABVEP4V61uiRcL4u2WeJ4R72Li/Ir444U4mxPzu786OQF2uWaA:5A31
^PQ1,0,1,Y
^XZ
HERE;


        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }
    }
}
