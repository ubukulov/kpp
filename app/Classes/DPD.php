<?php


namespace App\Classes;


use App\Traits\WmsConnection;

class DPD
{
    use WmsConnection;

    public function findCity()
    {
        $client = new \SoapClient(env('DPD_TEST_API') . "geography2?wsdl");
        $arData['auth'] = [
            'clientNumber' => env('DPD_NUMBER'),
            'clientKey' => env('DPD_TOKEN')
        ];
        $arRequest['request'] = $arData;

        return $client->getCitiesCashPay($arRequest);
    }

    public function stdToArray($obj)
    {
        $rc = (array)$obj;
        foreach($rc as $key=>$item){
            $rc[$key]= (array)$item;
            foreach($rc[$key] as $keys=>$items){
                $rc[$key][$keys]= (array)$items;
            }
        }
        return $rc;
    }

    public function order($data)
    {
        $client = new \SoapClient(env('DPD_TEST_API') . "order2?wsdl");
        $arData['auth'] = [
            'clientNumber' => env('DPD_TEST_NUMBER'),
            'clientKey' => env('DPD_TEST_TOKEN')
        ];

        $arData['header'] = [
            'datePickup' => date('Y-m-d'),
            'senderAddress' => [
                'name' => 'TOO "Роберт Бош"',
                'terminalCode' => 'KLF',
                'countryName' => 'Казахстан',
                //'region' => 'Almaty',
                'city' => 'Алматы',
                'street' => 'ул. Султана Бейбарыса 1',
                //'streetAbbr' => 'ул', //сокращенная абривиатура
                //'house' => 1,
                'contactFio' => 'Убукулов Кайрат Жумакулович',
                'contactPhone' => '+77772504794'
            ],
            'pickupTimePeriod' => '9-18'
        ];

        $arData['order'] = [
            'orderNumberInternal' => $data['wmsOrderID'], // ваш личный код (я использую код из таблицы заказов ID)
            'serviceCode' => 'BZP', // тариф
            'serviceVariant' => 'ДД', // вариант доставки ДД - дверь  дверь
            'cargoNumPack' => $data['cntItems'], //количество мест
            'cargoWeight' => $data['PACKING_UNIT_WEIGHT'] ,// вес посылок
            //'cargoVolume' => '1', // объём посылок
            //'cargoValue' => '1', // ОЦ
            'cargoCategory' => 'Содержимое отправки', // название товара через / таваров
            'receiverAddress' => [ // информация о получателе
                'name' => $data['orc_FullName'],
                'countryName' => 'Казахстан',
                'city' => 'Астана',
                'street' => 'ул. Авангардная 10',
                //'house' => 10,
                'contactFio' => 'Абаев Абай Абаевич',
                'contactPhone' => '7771112255'
            ],
            'cargoRegistered' => false
        ];

        $arRequest['orders'] = $arData; // помещаем запрос в orders
        $arRequest['request'] = $arData;

        $ret = $client->createOrder($arRequest); //делаем запрос в DPD
        $echo = $this->stdToArray($ret); //функция из объекта в массив



        if ($echo['return']['errorMessage'][0] == ''){
            print_r ($echo['return']['orderNum'][0]); //выводим номер заказа (созданного)
        }
        else {
            print_r ($echo['return']['errorMessage'][0]); //выводим ошибки
        }
    }

    public function getOrdersFromWms()
    {
        $sql = "select
                convert(varchar,ord_InputDate, 110) AS ord_InputDate,
                ord_Code,
                orc_Address,
                Status,
                ord_ID,
                convert(int,sum(ori_QuantitySU)) as 'кол-воЕдиницотгрузки',
                orc_Address,
                max(shc_Sequence) SSCC
                from LV_Order
                inner join LV_OrderCustomer on orc_OrderID = ord_ID
                inner join LV_OrderShipment on ost_OrderID = ord_ID
                left join V_OrderStatus on ost_StatusID = ors_ID
                left join LV_OrderItem on ori_OrderID = ord_ID
                left join LV_ShipContainer on ost_ID=shc_OrderShipmentID

                where
                orc_Address NOT Like '%almaty%' and orc_Address NOT LIKE '%Алматы%' and Status Not Like '%Отбор%' and Status Not Like '%Отобран%' and

                ord_DepositorID = 18
                and ord_StatusID in (1,2)
                and LanguageID = 4
                and shc_ContainerID is not null
                group by ord_InputDate, ord_Code, orc_Address, Status,ord_ID
                ,shc_OrderShipmentID
                order by ord_InputDate ASC";

        return $this->query($sql, 'LV_SAVAGE');
    }

    public function getOrderAttr($wmsOrderID)
    {
        $sql = "SELECT
                 UNT_CODE AS [PACKING_MATERIAL_CODE],
                 CONVERT(VARCHAR,CONVERT(NUMERIC(19,2),LSC_GROSSWEIGHT)) AS [PACKING_UNIT_WEIGHT],
                 LSC_SSCC [PACKING_UNIT_NUMBER]

                FROM DBO.BOSCH_EXPORT_ORD1
                 INNER JOIN LV_ORDER ON ORD_ID = ORDERID AND ORD_DEPOSITORID = 18
                 INNER JOIN LV_ORDERTYPE ON ORD_TYPEID = ORT_ID
                 INNER JOIN LV_ORDERITEM ON ORI_ORDERID = ORD_ID
                 INNER JOIN LV_ORDERSHIPITEM ON OSI_ORDERITEMID = ORI_ID
                 INNER JOIN LV_PRODUCT ON PRD_ID = ORI_PRODUCTID

                 INNER JOIN LV_LOG ON LOG_ORDERSHIPITEMID = OSI_ID
                 INNER JOIN LV_LOGSTOCK ON LSK_LOGID = LOG_ID
                 INNER JOIN LV_LOGSTOCKCONTAINER ON LSK_FROMCONTAINERID = LSC_ID
                 INNER JOIN LV_UNIT ON UNT_ID = LSC_UNITID
                 INNER JOIN LV_STANDARDPACKTYPE ON UNT_ID = SDP_UNITID

                WHERE
                 ORD_ID = $wmsOrderID
                 AND LOG_TRANSACTIONTYPEID = 24
                 AND (ORT_ID = 26 OR ORT_ID = 27)
                 AND LSK_ORIGINALLED = 0
                 AND LSC_SSCC NOT IN (
                 SELECT
                  LSC_SSCC
                 FROM LV_Log
                  INNER JOIN LV_LogStock on lsk_LogID = log_ID
                  INNER JOIN LV_LogStockContainer on lsk_FromContainerID = Lsc_ID
                  WHERE log_TransactionTypeID = 26 )
                  GROUP BY UNT_CODE, LSC_GROSSWEIGHT, LSC_SSCC";

        return $this->query($sql, 'LV_SAVAGE');
    }
}
