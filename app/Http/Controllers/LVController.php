<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LVController extends BaseWmsController
{
    public function __construct()
    {
        $this->database = 'LV_savage';
        parent::__construct();
        if(!$this->conn){
            echo "Connection could not be established.<br />";
            die( print_r( sqlsrv_errors(), true));
        }
    }

    public function lvOrderScreen()
    {
        $sql = "select convert(varchar,ord_InputDate, 110) AS ord_InputDate, ord_Code, orc_Address, Status,ord_ID
                --,sum(ori_Quantity) as 'кол-во_товара_указанного_артикула'
                ,convert(int,sum(ori_QuantitySU)) as 'кол-во_Единиц_отгрузки',

                case when orc_Address LIKE '%almaty%'then 'Город'
                else 'Регион' end as Направление
                from LV_Order
                inner join LV_OrderCustomer on orc_OrderID = ord_ID
                inner join LV_OrderShipment on ost_OrderID = ord_ID
                left join V_OrderStatus on ost_StatusID = ors_ID
                left join LV_OrderItem on ori_OrderID = ord_ID

                where
                --ord_ID in ({results})
                ord_DepositorID = 18
                and ord_StatusID in (1,2)
                and LanguageID = 4
                group by ord_InputDate, ord_Code, orc_Address, Status,ord_ID
                --ori_Quantity,ori_QuantitySU
                order by Направление ASC";

        $logs = $this->query($sql);

        return view('lv-order-screen', compact('logs'));
    }
}
