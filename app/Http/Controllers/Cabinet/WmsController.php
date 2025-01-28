<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseWmsController;
use App\Http\Controllers\Controller;
use App\Models\Bosch;
use App\Models\PalletSSCC;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class WmsController extends BaseWmsController
{
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

    public function boschInvoices()
    {
        $boschs = Bosch::orderBy('id', 'DESC')->get();
        $arr = [];
        foreach($boschs as $bosch) {
            if(array_key_exists($bosch->invoice, $arr)) {
                $arr[$bosch->invoice]['items'][] = $bosch;
            } else {
                $arr[$bosch->invoice] = [
                    'invoice' => $bosch->invoice,
                    'date' => $bosch->created_at,
                    'items' => [$bosch]
                ];
            }
        }

        return view('cabinet.wms.bosch.invoices', compact('arr'));
    }

    public function boschImport(Request $request)
    {
        $file = $request->file('file');
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file->getPathname());
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file->getPathname());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $connInfo = [
            'Database' => 'TEST_BOSCH', 'UID' => 'sa', 'PWD' => 'Paradiz2017',
            'CharacterSet' => 'UTF-8',
        ];

        $conn = sqlsrv_connect('DL-SQLDB\DLSQL', $connInfo);

        DB::beginTransaction();
        try {
            foreach ($sheetData as $key=>$arr) {
                if($key == 1) continue;
                Bosch::create([
                    'article' => $arr['A'], 'description_ru' => $arr['B'], 'description_kz' => $arr['C'], 'count' => $arr['D'],
                    'invoice' => $arr['E'], 'cert' => $arr['F']
                ]);

                $article = $arr['A'];
                $certNumber = $arr['F'];

                $clientsSQL = "UPDATE [TEST_BOSCH].[dbo].[WMS_LabelBoschQ] SET lblq_Cert_nr='$certNumber' WHERE lblq_ProductID='$article'";


                $stmt = sqlsrv_query($conn, $clientsSQL);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return response("Error: ".$exception->getMessage());
        }

        return redirect()->route('cabinet.wms.boschInvoices');
    }

    public function boschPrint($bosch_id)
    {
        $bosch = Bosch::findOrFail($bosch_id);
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;

//        $computer_name = 'WO-2092';
//        $printer_name = '\ZDesigner ZD220-203dpi ZPL';
//        $printer_name = '\Zebra ZM400 (203 dpi) - ZPL';
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
                if($all < 44 && $sum < 44) {
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
^XA^LRN^CI28^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW831
^LL406
^LS0
HERE;

        $val = 2;
        foreach($arr as $item) {
            $data .= <<<HERE
               ^FT5,$val^AZN,4,4,TT0003M_^FH\^CI28^F8^FD$item^FS^CI28
HERE;
            $val += 15;
        }

        $data .= <<<HERE
            ^FO235,1^GB0,318,3^FS
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
                if($all < 44 && $sum < 44) {
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

        $val = 2;
        foreach($arr as $item) {
            $data .= <<<HERE
               ^FT241,$val^AZN,4,4,TT0003M_^FH\^CI28^F8^FD$item^FS^CI28
HERE;
            $val += 15;
        }

        $imgCode = $this->findImgCode($bosch->cert);

        if(!$imgCode) {
            return response(['data' => $bosch->cert . " not found info."], 403);
        }

        $eac = (isset($imgCode['eac']) && $imgCode['eac']) ? 'EAC' : '';

        $article = $bosch->article;
        $val += 17;
        $data .= <<<HERE
^FT25,$val^A0N,30,30^FH\^CI28^FD$article^FS^CI27
^FT245,$val^A0N,20,20^FH\^CI28^FD$eac^FS^CI27
^FO464,1^GB0,318,3^FS
HERE;

        $iT = 20;
        foreach($imgCode['items'] as $item) {
            $name = $item['name'];
            $code = $item['code'];
            if($name == 'TEX60') {
                $data .= <<<HERE
^FO470,$iT^GFA,417,660,12,$code
HERE;
            } else {
                $data .= <<<HERE
^FO470,$iT^GFA,449,754,12,$code
HERE;
            }

            $iT += 60;
        }

        $data .= <<<HERE
^PQ1,0,1,Y
^XZ
HERE;

        /*$data .= <<<HERE
^FO726,18^GFA,449,744,12,:Z64:eJyFkb9Lw0AUx7/XUyMOTQS7tQScJINzkdIq6O7iJvgvpJtDkRMHC4X+Ec7SwbGL2XQQN0ch4OAmdTuo5Ly75N5l8McLhC8fLu99Xg74oxqFz1z5HCpBOXjIPL/KKcfM8z7zvI0F5R4k5XP9uJLo0YAFOtQox8Bx3T0mtRz3jgcZzkhNIKHsFLUJFxJGqGNe7MuK7um8w2eWRynYiAVIdV5fTkWBqbCiwyeMsFaK5hIXaKJrXVIM9WJtlL2l6KJvZ6Z68dSKNiG1Zo7Q/AJ2yjK2b50Gy2ee8UKEeoH48zrImDI7ILlphILPzW5Wu9oZtpXJJY9r3JwR1fmgzoW4HE8q/nJy8KpKzpV6U9VNMqXelbthwz/cj0PtHnV//Fy/cV+bRy0XG8ns0X3AtlaOHed3uxOaGq1GdH5+O6YBrUPfZ1ts/Du3qm/aa2bL:FA7A
^FO726,84^GFA,461,744,12,:Z64:eJylkrFOwzAQhn/LDc1QtSxIDFD6DiwdUMjAyNKBHd4gHRCVqKhRlwwofQ2eAjIy8AgMGTulQQipUlHN2YnPlRi5SMmXP/bdf+cAJp4VOGYepfa60LnXVwVz+9FzT1WekTD31Zr5CFPmiC4XU/riYo0+J03Q48IVVXB2CpxYIEEWeDV6EBPnrcCWpwRSRRiYkuREUPp94uEkRUi6NTQO0KLC1uhphi1KTAzffOBBXOLMcPGDaJ5ZoyKfYCY61qhUQ9zPX9A1I6BdidT0BL1vsZZbZYwej95FhQ7axOdfGdns0j7gYBSInCrTjbyoeYHQ9GCnaXoVVTPNumerN7Nw+qBu4I9+DZ8nFjXLslxqXbPQ+lO7k9R6o90J396Nv6+a2YZpukz5MBB7tOP8d8jFImvKQrQu3ljvllyW9JD1bLW73ut7Zbiz/pD18on/Exe/ewVnbA==:DA65
^FO726,152^GFA,481,768,12,:Z64:eJyVkbFqwzAQhk+1TEIbYg9dCiH2WDp16GBKcFzc7B3a9/CWDgUrZOmUvkLGkqHP4EAfRGAomUw6FDwEqyerkjwFesb2x6/T6b8TwLGYcsv53rKoLf9YdrhlUkw6emy4V4wNe8yzOj4d/VXzGHdojsExB0yAGEN1R8+g19HPdAMmmeGLYh9/riycAFB5ZLseXkmjg2tcg+wmQB5GjInFR/WG++jFFoIFTVujUck8Niw9WS+bQ1Ccp63RXQNxEZVLLkcwglHipwR1Zx2jfV46XLY6gWdIUpJhfdawmuwqhg14D3PYk8MLLoJXl4v9yeYSIoDge0u46hlO39mSq1moacrvSE2ZqXm1XHS4bVflSyu+zudheLtplP7z9JjrWxWiEZpzccjF3+ymoplu1uaWfLARwX+i345fhXvnJpqH5YprpjMaah5UK3Os+zmz+asvZvLde1Nz0K8MU58es/ILFUhveg==:7077
^FO722,221^GFA,493,804,12,:Z64:eJyFUbFKw1AUPc8EXunQuAgOwdTVRbplKKn+QQdHBz8hg9AlmFtLQad+go41g7ObEdz1EzIWClK3iOLzvjZ5L4t6IeFw3uWec88F/iqntFh+Wex9Wxwoi6NDMrgvLV96ucGxtLh0ihqKlVgZ2QJGWOSwfI7PWoCnp8YO4bHmPeDC2AGMUR84afDtBq9r65Z/LZbgOfJIM66Wg8dG2sVgMmGjfsxvRW86ZhyG2ub+jLS53rG2PyV+R7gkp3SuSS8QjyBfpaCEcRHBu+o4FOmVfLbvCq3t5MxTKCgi5nwE2BUUAB02GWEwJt5tDyP0sSP0zkG55HGt9c7Bx1OeVLkcZJdrSVSJxlV265T5o4pnu6LBO3V/ohOtLhNl2UypTaCBUi+quvBAqRtVBSrTVKbJBre5P5vXgaJxd55vS+Df6na7pnt4Zu4l5os3g93hs+Uf7g1+3z43+K5cGCxOc+Nm0iHrxrW6BMLv9QNW626m:42D9
^FO726,294^GFA,465,792,12,:Z64:eJyVkLFqwzAQhs9xiUtcYgqFZnDtjN3i0ZRiu5AH8JC+h7YsBWvs1EcoHkOGPEKrsVOeQaV7CHQxJcQ5WbbOU2lvkD5+pLv/foDfyi2Jwz1xWhEXB+KfhJgFBi0ZcsPCM7otPaM7whEde9yVRscfRgd7Z+yAZQbjVMP3PT0G2xhlYDG8BmojFM9Va7URs3Rr9XDqSRev4aQZzBw8x3HJZzyoGqP+pwiFD43R+JvPJA5QRqMlapdZY7RKuCdT2ejShzC7YdZerRpAMk1iNdtG8z5MIvSEq77zN4gb3cmX8AR+hAf6+eIVJJHaLTw8CIZ+ABMdrXhZgSdBJ4etLZWLTlNnp1PW2XX6oKef9fRx+1dZRLM6quNmk9ZHzUVdp3UbKIpp3QZ6vSzCx1zzaL1+feZd0HABVEP4V61uiRcL4u2WeJ4R72Li/Ir444U4mxPzu786OQF2uWaA:5A31
^PQ1,0,1,Y
^XZ
HERE;*/


        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }
    }

    public function findImgCode($certNumber)
    {
        $arrImgCodes = [
            'PP5' => ":Z64:eJyVkj9OwzAUxp/xEKmgpgNjpUQ+SAIHQVwh7Ki21IVjZA4XYEtaBo6BBUvFUo8ZUM17/tsFJKxE/sl2vu99zwEAeFKQxt1NQm7nxIU1icvjKXEFm8QNtIkfcSeObyingMxAEZkbfOogr3ErOJcanUNFFc4ycItzXJcoJYMOFrM4qWiVxwtYDUF/hcx8goWCr567ZOxesQmY/8gqvndiZDuxdwjJRs2PGkZnIDt2a1xReBKlDZQ96ZBC55Oh8oWa4fJAqTDhjpLFVMhkTO2Qrkk+1aC8cWxf+wtX4Twsqdm+O7D2OuWHEHUzPJNOYa000ror49bamV6qE2dadzcgxNA3QqR2rc9at4T/jHGb/5Pu7Spx/Zl1Hl6vE2+2h3y+yDo1z7xi5w7q7wJ+AHTqcOg=:7F84",
            'PAP21' => ":Z64:eJyFUb9Lw0AYfcdJI21I3OJQqLsguAWE1sXNwaEZBf+EujnE5ibplL+hY+jg7JjJv+MmERfTraDkvB/5LgpCP0ju5eW79+59BwArASp2e+lxrKTHiWo8nn3ueoxvj5doPc71H5LcYUYGrMGk7jCXiMkgqBFsyVaAq27DRG9R3gqcDHK9hYw1N2y9VV+pea3778AZj0Qso9G7bVdivBgzl0yJ9KHlTkCf8f7amBheQpZo7YHyO1aHXbL2hIvIJdMKCQ5cMq0wXUUuWSBRNKFZYIjT9RDWoBuBNabRmDRFnw7TDi9dcs/b/tT1W50czOD4Y7OplFJGP9DrtlD2yrjGX/qxk86y7OYxm/8dUVfHv/Ah9hQ7AiPMn6PohfhBMgiJLy+ezoifnwcL4itVVsTj6tXrCPm2z/f/+gHzaWox:360E",
            'LDPE4' => ":Z64:eJylUbFOwzAQPePBSFQJI0MlSzAyULZsXviEZs4vtDuilliR+IV8RreaiTULG4N3pCpjhyrmzvY57JwS++np+fneGQDLQql2mHGYChQhFFyHUA6oQbvC+9oz1qBGxgd7dWK8A3FmPMUv2SP5krFEcXjL9miiv9kewGQjulTnCww2czixPUDn2f6iv+0TfgZZ2lyQU1UaddBFcG/B+GgGYudg6yAlCx5+PKRkehQfLiczo3z1sVnEGyUciMhPl3U45WRnUOFMt2MqbOeOEpA/CgUloFSolT6lULnz2gHwKGnnEcsx6XhKO57emHzpIYnXkcw86aWdfZR76lx8xDA94IK8xC3oEJ+Y8GTwp3Nt267rr3bNw1IWSgn4Uxb+X6vaT6bPeLH9vMn8vtof9xlvkF+yvlodeRqr5eNwnfmmalwz6x37mHfv2X+uX3xhh+4=:C199",
            'PAP20' => ":Z64:eJyVUTtqw0AQnWWJNriwWhcG6QrpBAErR3CRlIYcIa7SKNaCm1TOFVwanyCQwhKB5AQpDQJBCHGRdSeSWOv9aFYq0mRA4u3TzHt6swAA9xywyOTCYV8WDgdSOBx/VS0mvw7PoHY4gdhJVhCjAREQZA2mBfhowDJge7TlQGUzEKgRiVZqBA0SNYLGiuvVzqqtR/1atucTa9zjflE/fJj2FIY3r8QmkzyalnMroP5xmvPE4E0B5XNmDZJrkudZam1DOi+5SaYUGMm5SUYrGMGOm2RMwN34FpjGiojFSH8GXIExxtXoNGmbTonYmtnkjjf9ke0PNB6AuQL/c71efj9ttD6TUu5Taa6MKvyjHrPpyfYgDtu3Ri4Mo866Bh18Cv8oWvd93DvxAm+I/OLc6yN/ecbGyK92ixXycPXidHjxDo7vFOd/uB4BNMZ2Iw==:5870",
            'PET1' => ":Z64:eJyFUDFOw0AQXHOyjNLYBemQTImoUkYpYiPlATQu+QfFSTkpDRKfiPgAfYrE+QEPoEhNg9NZIuLY3btdp8tYpxuNb3d2FgDg1YEgea6V5/6gvPSd8uqnV75MTsr/8BNYWAo1PVRikHRQtpFnB8jFIGshO4qtA+NjQYklPupzbCUGFkvEGO/RSdsPmGDFlSa7JQ/Ch8MxgzG2Tr+kaOfMlmblhC2+JxMELmEOMZmtSQ/J+jvSORl1QL10IS3pbIAJSWdjEihBF1LBTZx2FyckY1mZDWkZZFwRKYLOhtPwnlf5Ain1yb33HR72xfuIh34bvH/xsE3TNLVdLHRF07N1FWf8Gi7haZwq/968rYXvi0T199nKCf+cDLq7N6q348eh6cNF2wH/9rFkWA==:5EC3",
            'PS6' => ":Z64:eJyFUTFOxDAQnFykRDpQTEOHyAMoqGiTIPEETpR8IQ0SxUl3JRVvuJcQP4GGjsIlZUoXUcyu13bS3SiOJuvdnZ0NQDgi4fp74W5ONHMu8QvnUkE51jpyZZSJvNblGPlwzG3kB2RT5LN/pP3EVwJObj9je2r1G9vTCY2UljtGS8MMId7SeQ1xHnhzEr5fTKGC+oOKDuoJtSd3JzSNb4bsoPF+5UWojYEVcUoe0XdBuLVwFuKs7eH2wdn8AdsEZxQYb8VZTgFbybQZFfUUZ2F2NH+JgHdLo5ZGvu+tbINXnPFco9R72GVjrLOOi6FO8tlErlMflPrpheLKEfhFOjnzmg7db5gPTn7xjlD+7J6jTh5FeBi92uman4WyaVgUl4+ptqpMyiluHlb5KuUU6JZ8GLPE31L+FtvVnGfwD+pCgCI=:0860",
            'PAP22' => ":Z64:eJyFkLFOwzAQhs+4xQgk0o2lUpg7IbFkSjL0AbJk5B3SjcFSPGYqr4DEDitbHcGD5Akg2SKoMBfb53brRSf/uvju838AAI0CCvaQBx2ZLujY9EFn32PQNdsH/YcfhYSaJB8hIwDrIdZeiw4iAggNYiCsAm58Q4wtxtdTHEUAiS0ExvNyH8Yf4g7z7OBsIRz4TWWdebJgHC03beuadiqpXj6cs0xDxbS0GpfQXSkHljnTwjsbbyOVOmeIXUIFsXJu0+ZHWWfosB6MtmAsrJ5XwHvnKrx25184gWll0rm1MYEz+35Xt8DE3berfIT5NCcyxvSYlovngDn95nj+YlpMWZa5XK/DTpKjdS2O9AWcCFbM5oXX/DW9fqf6+c1sGer3W6rzYiPC/cZsFd2H9pNmclBfp7iH+AcfdHRE:2CEC",
            //'TEX60' => ":Z64:eJxtkbFOwzAQhs9Ji6UKkQmxFLnKUyCI4j4CvAGPkDFDpEQMCBDiIRjLwFpRJLKxdmBkiJiispipHqIcjh07HXrK8OVy+X//ZwBVBbg6/hoYW4cE0bGPWFqmgjkOqqCyzEoqLPPCl5ZzII3lVj9Gvuk+9fJqmD/0fSXCvq2Vsuh/oMqU9kJMmb72BkwlyYW1nXkvPeckofaccSB5PTGRR2x9KqhGr4jhXHLdPymnkKYmGVZRId9NMiayeZKZZFxwKSOTjIlcJpFJhsu2kZFJlo/SKIlMshbSWJ5BZ6AG06nqM5N2GUsJnYESjrIs1enUeyxjCX63gVI5MKG3wUsI6qDWW1K6BMZKpAEwBzdb5RrmQDLbr/S8DkRKrRP8hGHobxbd5ihucUsRsdKXi/hhmHTM0Fz9xWr1FmwWC3t7xO7T6A813+EZ7Kmj382j5fHn1dSyfwtLp34Al0O/dvqErq9d/wbEzvza9Z/u74b5v0PLnvc82XeevfUPtymeMA==:5CEA",
            //'TEX60' => ":Z64:eJyNkEFOwzAQRcdxxSyQ6hViU5GLoCYUOAA34AjJghVR6xPQKyC4R2PaC3AEixWrKuyigjIYO2N3yWz8NPr+398AABrinL0nXg1HTBHFoY0XRKcMs7TKMmOJkZWWPXPubjDPAQ4salwCmzpxodne7aM9ZC2zqc7je3Z9q9kHTf0WArIN6i8bgsWAUJsmSOip/7RjsxX+VOYi8Pd6Y20IFt3JrDI4xk6ndmyGBme1Qd9YPav5o5VdaIXNgwnNCtfWufjgJUA58eV823hc/mXw3iWejvq8dBmTW++jiKgg8oyOcyKfmxENa9rzZwFCGnnEIqHWUS3ubkrm7OPlPmoWIu5h+xr1cH2VeCsTL7Kk3+0t/Ht+AZjwaNY=:4BCE",
            'TEX60' => ":Z64:eJyFkqFOxEAQhmdZSOXmFGZDeQTOrbhc67A1aF4BiQCuScXVYRAocg4cL3Ahi0LyChvUOSoQG9J0me50txWXMKn4Mtn55/+7CwBQQ6yjn5EzV05YR+5eTeQ2aQJyy21g1rDrwMJAFzjRcBclS8jC5hS/YuAljgShGxwJC7CXmSCPTvNh7e5NmGA070RzSHjAv07skviYaXmW0nFX1QuV0sAqeZBSULJV1SklaMHt+aVUnBbb5/utomSsSRK5AM/c8PVWUbJE87VUvVm0rvn3k+3DYaSSoR9Ih7S/qC76fhZ/BpDABrwJ7zanvhcwgw422Pts5fWdc5/O0V7kD+daHxe5cm4Xb0DDWGbCOfxXrR0fxKwsIp+W8a5hXlyN/Rez//zmcexfTBbUkwe3r/4AYk5soA==:0296",
            'HDPE2' => ":Z64:eJyNkbFOwzAQhn/HRYlUQdqhYkFKZ6bOHZoOeYB2yMg7MDBWxBPqxKMgRraEd+ABMmYqWZAyVDH25exmYOAkJ5/+nO/uvwDAUcHHw9aj1J3nUJ89x7r3nFSF51SlvtAzEq/3iGtGcUboWHaQrSvfQji2yT0nxRVQVK68YW6QmuMa9wpTzfrBnAXrl+GBGW7tg+IIXcvh4xXEZyvIWaCVrFsMzmzLR25QmlEalNSgqCEqJMT9O2TNzg5UP6zIYYQNMG0stzQRObNWOzINuwLxsxumNQVoz8aFWSZEvhvcuVWSa3aRsHMEoCGIJ6zb/JtBS+wgdxD2Xqy17syxxkLzPheafqVknebP83yX7vfUJEIUbUarm4w4wJ+hLphtt+7C6vTxtuZPq2z+smR9fYpe71lfZNdfTz4/Ui5nls2xYl5+N6octfh//AKyzHbW:C093",
            'PVC3' => ":Z64:eJyVkb9KA0EQxr/NXUiwyQZMFzhLIYXYiYJ3aaxtQlofIQ+QeFseIvgKAV/CKqazsPUBglXKsxAOCbeZ/TN7KdI43MJ3s3O/mfkOoFAI0f5qdKqXQed6HbTWFUux65Ssoyr6Y90pxY51b4OaOyRr5AGvzOPx9spFTZ9slccT6mft8XS08ni60wEf8obRqT2ehh+xhgBOuW1KpDOrYyyoQ2Z1C9UtIruNyCCHXmvCrxDZbXLKzOE2u6jsRLZBUkLM4DZLd6bebZbHdOc30y0a1U2LR+A+cVtEBKhWyrIMeDH3W9O7mdM0MHXtzLlkvzfxrhrHEjROGlfZ4YQdPrd5Vy9dveUQmJyzv1CU/T452tMUn3Sob9voDzo0T8voF+1+/WAynY5/JxOD6BZFoSqluD9maOLqQMsD3cWRuHk6GTFF3sWXGUO+nzdLzo+HnEb6+sZmQAr5EOqxveYLiWxwrNe/Yg/KCImc:35ED",
            '3xPVC3' => ":Z64:eJyNkbFOwzAQhu/qoCBAbQfYDERMPEKHqipv0O5IhTfIyICExYSE1GfIY3TMhsQzMETqABsZrQj1ONvJOQOquCjRl/Pd799ngD0xaCIrijwiI5yuYn6YxrxWpfBMVcJTjDyByDk/XVhoOiGufhILFcw6xjIy77QTOwZuvUUWGD0H+XN2gliHLTl1eLxRXn/Ob6LRG92uBxYm3ijeaHiEsTeqjGVxC1++vub8Ihi9q6HhAzx47znUoPnP6Vusjk5g6kZw/4FlqnmFvWzXqlIbuHAeMUlLzCF1vcaw1cL7dUvy4RZ/tpC/7PHQdbXMNSj1his6nTmos/ZmFNE37UIeid7op50V0SdZGbqCGK36f+P6pRA+PVgIX71mwsmyV//ea16OBbOsgL9jr59fYItRhQ==:77B3",
            '2xHDPE2' => ":Z64:eJxjYEADAkjsfwgm438Em/l9A5zNzn4AzuZvfoAQZ0SI8zEgxGUYPsDZNgy/4GwLhjI4u4LBDs4uAOqAgQcIBzE6MIiAGVxApzxYwQB2kDwQO9QwgsWtGxiYGP4xg8UFDjCIMx6DsDnOafA39DGDHcd4zIGdgQXiUObmB/wNUIcyMx6QAVpbAHZ7c4Ndww6GHyA2D0i8AOg8ILBiemABVG0BtvdIQQHDF4hD7c9Z/AA6kw/qzg9g78OcD2eD7WRHiDcg1DPCPfxr0eJV+6Ds+v/f//+Bsu3/v///A8oWDXW86gAPIIgXoVYwIIEGBqyACUm5KyLiu3RWwNmRLCJw9io9BYR6FoTe7hdW2M3HAgDgrUWQ:913B",
            'FOR50' => ":Z64:eJxtkr9KxEAQxmeNkpAi0cIu3L6BtoJyiW9wgoeV4CPcFUKKw2x5heArXCk+gcXBrZ1gc43Ybnlgk0YMcmSczf65K25I4Muw85v9ZgJAIcDHcOnlHrZeM0SvU0RfEEoufV6lymkOYe30QgSN0w/A1k633WPwdKCyWh/OHy2eIHzt8PRakG7Ka4cHwJXRmnGjPB72ZkZnG4MQRRGIE/txOwiXpvGpqpq8ybUzVsn7QZYZZwvViHnfOOP1CCY94yxv/mZlzzirDrEuOet0G7VNk3TdyWoxKTmZNq76ZQK6QUCAC8pz7aphaj7vG3eS1WWW0VAJrwDpPhBQngvgBXVlVEf1sYhFx3MTs3nQi9R53iXN9FIlhEin0yfihD/D4XX6iUj8ABHbHLtV0nIROZoV6zxt2K8+lJvRBWKj2dZIt38gH/FzDC8OMjq+/C2MTqar1bvd0T7IsbQNkpk6+7ag8EoefdnaRKjXD+HO343f3Hk4V8JzDgCKHZfYGf9fkZr3:129E",
            'FE40' => ":Z64:eJyVkj9LxDAUwF+sR4ugrZMFhRy4OMkhiAWH9L6BS7nVj9DlnA4N3NJBvNXxRj+Cm7e5+BEEA34Ab/OQ0pjk5Y94OPggzY+0+b33knLuAtYi6gLHn4GpFIHflp7ZdOV5QmoEZe546/ykhYnXr+Da65dw5jgVwBrHC6CvTs8hdQn21Jba6/mTsH4lHy3sN18AG3PLmRqblhM1jhHNEkOWPI0EQw0t415NS5OAif3bYibMCyp2D/IYu2fi8L6IsPurbJhnBLlLPpoCWtvt6CXDjlW30d0jmCONaiCkNB1zZdZhEsdYOtVTipWYiSLHfr2vDwbwcWlZVyUAPeOqGo6rG71pKqV8l7LT60Txs5Qt1m/W/Y2RcI/aE6L8wX34HX//Pf+P89nc8yAJCYomZDjdDvWcbIV6BnnwHO08eM56F2t5vgEWqnaO:B52B",
            'C/LDPE90' => ":Z64:eJx9kT9Lw0AYxt/ksCcKXjoIHSwpnRwdg4KJCPoFHB0K/QIFBx1icyCIH6N0KhnEMVsP9IMEuzlFEAwYe17uryD4wpEfT948771PAP6pgUNv4pisHYeN45g7TkPHTUitTU2YYVST0nKFLZMS1ZaZV9lRFKwu3G8TzUsK3JhOATLD4pbcDE7Bsz0FuH7hEWpGVB1pLzRP+3O2CbBQt+dsbwNutE7fjqhOImOnAUtN/6pgU9X/3eqh3pZmT2VM1bbQDZJQbwt3BVOpYAZnfYYlEwrHhKJSb6u+EqUdZBKxmlLrzdtq1LZgH62wTWFtBJGi1LM8H8TzodRjzt+zz+tG8xfnXHpizqtMnJb9fFmJdzMdHGBwZWL7XT4M/NnQn8v7JwGuwuePlqOyII+Hq7zl0UkPd4Ir2V/6EepEY+mfQA/j7jjVeud+uZI/YJS0/Uj2T14jtBOpybsv/fPLA3Qhc3vYmi/2/fzvjWz9AIM0hK4=:631C",
            '3xPAP20' => ":Z64:eJyFkDFLw0AUx995gQSpSQQFh1L8CB07SInS0cGhu+fq1G4ZAh510Cl+BSc/RwqCix/B4SAgmWrHUCXnvfTyrpP+j4Mf7+7+938PACXBKXHIG8e+dhxp9yDyXT30Xb3PC+IxV8QZc5zCmnhmVqcKvjsjc/uO4igYExc7LIGCmjRHFM2asO1mbc38Huwv7vGoJwBi/2GJQQMBvA4/yzbc3IMsnty0XObQBCtVocfyAzJ4FRh0T9Ww4flp3WafsTX3RIo8qrkKQ3GGPExZ0fOSvsGTYcOL6EUNDB/M3304PocI85T5kwykxKwMvMj20Ea1vaEGOxyaLS1H+MpO3VhwaXs2Fvzw2vpo/aWbbZ1p/aZ/7NiwXtPQOThx6ZjBv3o0q9M0nhLfjlbEF1cT4lXl6tmluy+aZ+LES4jVovjr+1/S014V:CD92",
            '2xLDPE4' => ":Z64:eJxjYACCFQwIUINgMv9vgLMZvyOJdyPE2RkPwNn8zA+QxBFsPoYfcA0yDAVwcQsGKzjbgEEOzv7BYAdnF0AdxAXEDzj0wGx5IHaQgMhbAa1/YMEMNl8A5JQKCJvhnYJ2ww8Im/FYgjhDBMShzN0P+Bs6GB5A3c5+UAbC5mNu4Gvex/AB7EbmAzKMNRCH2vU9sGPYxbADbO+RAhsGCaAXgMB6lYEFAwfcoQVgr8GcD/IyBIDMZkdi80PZCajiDTD1BQyMB2KgbPv/n///g7Ll/3///wHK5v//+v8LeAAxNjAgADKbgQhxZCAAZzWdRiQIF0+EiiZvBQTH1QAhfmIBnH3EA6HkUYcFhi0AKSJGCQ==:CD49",
            '4xPAP20' => ":Z64:eJyVkL9KxEAQxmezkYSI+VMIFutd7xMEhJxyha3N9fcIKe0csBCCeK2dgl18gYCgiWltfINglUpSpjiM2WyyI3ZOMfz4dpn55gOQhUB1QrjbEltbYu+bWMyII484tIhbjxbEVq654tWErGJa79UvreegF3MEbcgCdqHtINd678Cf2AUwhhnqJHOY0W+xHWeDajacz/cGo/zN4e1xci31naWAOPCX0pBbZtDyrJRGRdGwLfMLqYdVxT+NEKUe5GuWm34e97yoW57bBygN7ReNheIQB6Nl5uFig6HkMzHsFLIliYt9/pGyPNMNlOQqFr84kme5+r+pHofT3aOXMZ7Van45Bud13Ws3BsS67r5rFBtpeps+TGGpeMYy4F9VV7Xm91OdP9w83tH4gPT2mf4XHzTn6omYMWK08e/KH907V4o=:F36F",
            '3xLDPE4' => ":Z64:eJxjYACCFQwIUINgMv9vgLMZvyOJdyPE2RkPwNn8zA+QxBFsPoYfcA0yDAVwcQsGKzjbgEEOzv7BYAdnF0AdxA804QGHHpgtC7TSQQJqxgkOhgcWzBDzjwQwMFRA2MxtCdoNP6BsZgdxhgiIQ5kOH+Bv6GCAOM7xAPtBGSj78QO+5n0MH0BMAWcHGcYaiEPlz72wY9jFsAPsJxYBGwYJoBdA5jAoWDBwwB1aAPYazPkgL0MAyGx2JDY/lJ2AKt4AU1/AwHggBsq2///5/z8oW/7/9/8foGz+/6//v4AHEGMDAwIgsxmIEEcGAnBW02lEgnDxRKho8lZAcFwNEOInFsDZRzwQSh51WGDYAgCOq0XM:EEFF",
            '2xPS6' => ":Z64:eJx9kbFuwjAQhs9JI0cMcQfGSMDGyJgBglH7AH2HvkCqqiqjETvPgGBHjB3Ng6BaZeiG2q1SK9zEmLsICW7xp8+n/H8SgCtzIGSWOLSK/EaTnxpkzoiF+kFOoKhxhpzCALkPQwwYQwuIc+QCmu5slJsmjt2+KKvI9HjfqOLz0PlbCRE8Mlc03mdCffhydwXX92B8d6EP8OXeiWkuX49FxcQk8516q/iGmRRG4BKS5TqHnXKFWp+9DJ6933ezsmZSq8k9V8/m6InLfCUUenny43ZnsfI8eHmyf56H9tee/kz0/r198BwEbfxsZ3PJ07ARcbCifdYkHy5rHiTtzzQtRddz/gGKGkIc:AD93",
            '2xPAP20' => ":Z64:eJxjYEADLEjs/w1wJiMSm/n9AYT4QQSbufEBnM3OUABn8zf8gLP5kMT5GCzghsowyMLFbRjs4eIVDPIMCLYdnF3AIAJhAFU+AJMMDKxA7ABGQDtBTlmwGOwgCQcGVsbCQrBDOV5p8Dfv3gFxqFsB+wFBgQ9g9uMH/A+MLcBsRsYD7A7JAgUQPx3gW/B4xw4Qm43xgAzjQQEZsNuZH9gxL94AdpCse4EN40cHsLj9OwsLphUNfCA2iwvYs+xQJ4PMZm6AOR8hDnI+O0LcASZewcCwSB/Ktg0N/P4Pyrb///s/jM3//+P/K1A2k5LCIgYEcEBiNzAQAqwMAnA2X58enM0SgkgQXLMQ4qxCBnA2h90ChHpGJHsVFDDsAQCcT0bB:8192",
            '2xPAP21' => ":Z64:eJx9kCFPAzEUx1/pHYdYrkxhbjmBIZtBIC6IW/cNDjG/jzA5RxcMCr1MnVz4EKTLJlBz+GoUOJKRdKXdvXdi4a9++fXl9d8C/JOhIn5ssdWIbE3M54YYpshC/SAnLZ9CgUsz6KEvIW/5HH3hTppMIUJ22/18MEyFO52srz13NcTzauSLFu8XYrMwoei2SnRXhkIvhpvbyZffwKWrGTx/0qkypvjjc6ZTpmXmu3NT8tXEF8oSmbEq+OHOzT67p7n0tpcz/+QQz6qpT97V10LhjGx8+Vp/vh05t9/298jC7m3zofH44UPiR4AmhJYGBadyBjfIcXSH3Nn1kaOK/lks78lf0Z7OoCZ/8ibKASIKQvY=:7EB0",
            '4xPAP21' => ":Z64:eJyFkbFKA0EQhmdvzlxAuYuFkCI58xiCEO/AB9AioF0e4QoLC4vFKwXvEezT2gQEuQfwAdIIZ9JYSEi5QvDMTjYzgkSn+Pn4WWb+mQX4o9AIB0vh6Ev4MBbuR8KdQHgcaeYsKJkXWAkr9lUFc+YSeDBq4ECr7tccR+NPv7XhEMAjSEgbpKuJzfC20OvecNFFCopTzzPHBVofUx9u1A5xOHuBpbrLbVA/mcCVUhS0WRmYemjbQXuYqdJXkNlzGIPVHmob6GAxwbKr1kFnz6hPcjiynPo0s0P+PR2n71Zhn+I7AYhZ3Foxr9vYvA9Go+LJ/QzW9UNtfjMMXotB4rjXC0HK28JbqqWEP97GzKfnfH+YP4p/tp8xv39q5vRS+uS74v9T387mTm4=:7470",
            '4xLDPE4' => ":Z64:eJydka1uwzAUha/jTI4KnMACb2GFw9U0tanUPUDfpGBgoCAPsIcozCMEra5SaWzaI7hsZYYBkzz/xD94F1ifjo6Oj68BzBwhDJGR6zHh38irj8gHFvmNRt6TyKJsPSJOuGcssAjM0cVz2aJwsU4JXAIOXAPaW9DJK+CdywZgiE/ZAAUW1GXb48FmfL3Pfii3ej6wO5lzW3Q29HgsBDFFq41Esmqw4aUQeHwSNuL+W6JLLe0N66vE7Voi8wB0rnQGB7u5odeP5a70JysBGrBFs8zeuXT1WThMfX8APCe6tmZe11Ke+KkzmjUAezlM61HqpKYFEaVqNf0kNnpYNCT/Bcl/6fz/TtF1R8/z110T9MWt9VxtN9H/eAt15ttd1BfX6M+jn/Y8+P38AYLwWA0=:1D0D",
            '3xPAP21' => ":Z64:eJyFkbFKw1AUhs/J0aagJBEcHJLYxygI1aIPYIeCbn2ECA4ODhczCuYR3Lu6BFwivoAvIFzt0kFKxwjFmNwk5whSPcPly8/l3O+cAPxRlAvbK2H3S3g/FB64wr4tnLqKObIz5iVpYeQcNSyYM+CHSQELld2vWEfRz9xr2QGwTI/6s2Pulq90nZukEvJLPgvIiO5psPKDhIzo8S5c42bNsxRWeBtXovj0ApeIRtTSObxbBGaASYTZBkJUYncyJ71NqhLyLpaUBWhE+7OU1GEM/ZKDYb0cv/J5vjPLGTSjtHmt3xwAIR/NWCGP22nv29Np8tj8GSqK+yL/zTB+TcZHDfd6DkhZa3hNeSj88ZYyn4x4/7B4kPx0J2Kefyrm4bn0ibck/6e+AceNTbo=:8845"
        ];

        $arrCertNumbers = [
            1111 => [
                'eac' => true,
                'images' => ['PP5', 'PAP21', 'LDPE4']
            ],
            1112 => [
                'eac' => true,
                'images' => ['PP5', 'PAP20', 'PAP21']
            ],
            1113 => [
                'eac' => true,
                'images' => ['PP5', 'PAP21']
            ],
            1114 => [
                'eac' => true,
                'images' => ['PP5', 'PAP20']
            ],
            1115 => [
                'eac' => true,
                'images' => ['PET1', 'PAP21']
            ],
            1116 => [
                'eac' => true,
                'images' => ['PAP21', 'PET1', 'LDPE4']
            ],
            1117 => [
                'eac' => true,
                'images' => ['PAP21', 'PET1']
            ],
            1118 => [
                'eac' => true,
                'images' => ['PAP21', 'LDPE4']
            ],
            1119 => [
                'eac' => true,
                'images' => ['PAP21']
            ],
            1120 => [
                'eac' => true,
                'images' => ['PAP20', 'PET1', 'LDPE4']
            ],
            1121 => [
                'eac' => true,
                'images' => ['PAP20', 'PET1', 'LDPE4', 'PS6']
            ],
            1122 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP22']
            ],
            1123 => [
                'eac' => true,
                'images' => ['PAP20', 'LDPE4', 'PS6']
            ],
            1124 => [
                'eac' => true,
                'images' => ['PAP20', 'LDPE4', 'PAP22']
            ],
            1125 => [
                'eac' => true,
                'images' => ['PAP20']
            ],
            1126 => [
                'eac' => true,
                'images' => ['LDPE4', 'PAP20']
            ],
            1127 => [
                'eac' => true,
                'images' => ['PAP21', 'PAP20']
            ],
            1128 => [
                'eac' => true,
                'images' => ['PET1', 'PAP21', 'TEX60']
            ],
            1129 => [
                'eac' => true,
                'images' => ['HDPE2']
            ],
            1130 => [
                'eac' => true,
                'images' => ['PAP21', 'PP5', 'PP5']
            ],
            1131 => [
                'eac' => true,
                'images' => ['PVC3', 'PAP21']
            ],
            1132 => [
                'eac' => true,
                'images' => ['PVC3', 'PVC3']
            ],
            1133 => [
                'eac' => true,
                'images' => ['PVC3', 'PVC3', 'HDPE2']
            ],
            1134 => [
                'eac' => true,
                'images' => ['3xPVC3', '2xHDPE2']
            ],
            1135 => [
                'eac' => true,
                'images' => ['PVC3', 'PVC3', 'HDPE2', 'HDPE2']
            ],
            1136 => [
                'eac' => true,
                'images' => ['FOR50', 'PAP21']
            ],
            1137 => [
                'eac' => true,
                'images' => ['FE40']
            ],
            1138 => [
                'eac' => true,
                'images' => ['FOR50']
            ],
            1139 => [
                'eac' => true,
                'images' => ['C/LDPE90']
            ],
            1140 => [
                'eac' => true,
                'images' => ['FE40', 'PP5']
            ],
            1141 => [
                'eac' => true,
                'images' => ['TEX60']
            ],
            1142 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', 'LDPE4', 'LDPE4']
            ],
            1143 => [
                'eac' => true,
                'images' => ['3xPAP20', '2xLDPE4']
            ],
            1144 => [
                'eac' => true,
                'images' => ['4xPAP20', '2xLDPE4']
            ],
            1145 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '3xLDPE4', 'PAP22']
            ],
            1146 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '2xLDPE4', '2xPS6']
            ],
            1147 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', 'LDPE4']
            ],
            1148 => [
                'eac' => true,
                'images' => ['3xPAP20', 'LDPE4']
            ],
            1149 => [
                'eac' => true,
                'images' => ['4xPAP20', 'LDPE4']
            ],
            1150 => [
                'eac' => true,
                'images' => ['2xPAP20', 'LDPE4', 'PAP21', 'PAP22']
            ],
            1151 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20']
            ],
            1152 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', 'PAP20']
            ],
            1153 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', 'PAP20', 'PAP20']
            ],
            1154 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', 'PAP20', 'PAP22']
            ],
            1155 => [
                'eac' => true,
                'images' => ['2xPAP21', '2xLDPE4', 'PET1', 'PVC3']
            ],
            1156 => [
                'eac' => true,
                'images' => ['PAP21', 'PAP20']
            ],
            1157 => [
                'eac' => true,
                'images' => ['PAP21', 'PAP21']
            ],
            1158 => [
                'eac' => true,
                'images' => ['PAP21', 'PAP21', 'PAP21', 'PET1']
            ],
            1159 => [
                'eac' => true,
                'images' => ['4xPAP21', 'PET1']
            ],
            1160 => [
                'eac' => true,
                'images' => ['PAP22', 'PAP22']
            ],
            1161 => [
                'eac' => true,
                'images' => ['PAP21', 'PAP20', 'LDPE4']
            ],
            1162 => [
                'eac' => true,
                'images' => ['PAP20', 'LDPE4', 'LDPE4']
            ],
            1163 => [
                'eac' => true,
                'images' => ['4xPAP20', 'LDPE4', 'LDPE4', 'HDPE2']
            ],
            1164 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '3xLDPE4']
            ],
            1165 => [
                'eac' => true,
                'images' => ['3xPAP20', '4xLDPE4']
            ],
            1166 => [
                'eac' => true,
                'images' => ['4xPAP20', '3xLDPE4']
            ],
            1167 => [
                'eac' => true,
                'images' => ['4xPAP20', '4xLDPE4']
            ],
            1168 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '2xLDPE4', 'PAP22']
            ],
            1169 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '2xLDPE4', 'PAP21']
            ],
            1170 => [
                'eac' => true,
                'images' => ['PAP20', '2xLDPE4', 'PAP22']
            ],
            1171 => [
                'eac' => true,
                'images' => ['PAP20', '2xLDPE4', 'PAP22', 'PAP22']
            ],
            1172 => [
                'eac' => true,
                'images' => ['PAP20', 'PAP20', '2xLDPE4', 'PAP22']
            ],
            1173 => [
                'eac' => true,
                'images' => ['PAP20', '2xLDPE4', '2xPS6']
            ],
            1174 => [
                'eac' => true,
                'images' => ['PAP20', '3xLDPE4', '2xPS6']
            ],
            1175 => [
                'eac' => true,
                'images' => ['3xPAP21', 'PAP20', 'PAP20']
            ],
            1176 => [
                'eac' => false,
                'images' => ['PAP20']
            ],
            1177 => [
                'eac' => false,
                'images' => ['PAP21']
            ],
            1178 => [
                'eac' => false,
                'images' => ['LDPE4']
            ],
            1179 => [
                'eac' => false,
                'images' => ['PP5']
            ],
            1180 => [
                'eac' => false,
                'images' => ['HDPE2']
            ],
            1181 => [
                'eac' => false,
                'images' => ['PS6']
            ],
            1182 => [
                'eac' => false,
                'images' => ['PET1']
            ],
        ];

        if(isset($arrCertNumbers[$certNumber])) {
            $arr = [
                'eac' => $arrCertNumbers[$certNumber]['eac']
            ];

            foreach ($arrCertNumbers[$certNumber]['images'] as $code) {
                if(isset($arrImgCodes[$code])) {
                    $arr['items'][] = [
                        'name' => $code,
                        'code' => $arrImgCodes[$code]
                    ];
                }
            }

            return $arr;
        }

        return false;
    }
}
