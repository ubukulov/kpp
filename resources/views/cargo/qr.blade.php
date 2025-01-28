<?php
use chillerlan\QRCode\QRCode;
?>
<html>
<head>
    <title>Распечатать пропуск</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        #printSelection2{
            width: 780px;
            height: 780px;
            margin: 5% auto 0 auto;
        }
        .row{
            padding: 0px 20px;
        }
        .company{
            color: #000;
            padding: 5px 5px 5px 10px;
        }
        .qr-code {
            text-align: center;
        }
        .qr-code img {
            max-width: 100%;
        }
        .contact-info {
            padding: 0px 0px 0px 5px;
            font-size: 14px;
        }
        .contact-info p {
            margin-bottom: 0px !important;
        }
        .user_img {
            text-align: center;
            margin-bottom: 5px;
        }
        .user_img img {
            max-width: 100%;
            /*height: 40mm;*/
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 100%;
                height: auto;
            }
            .badge {
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
<br><br>
<div class="container">
    <div class="row">
        @foreach($cargo->cargo_items as $cargoItem)
            <div class="col-sm-12" style="margin-bottom: 10px;margin-top: 10px;">
                <div class="badge" id="printSelection2">
                    @php
                        $cargo_area = $cargoItem->cargo_area;
                        $cargo_area_name = ($cargo_area->name) ? $cargo_area->name : '';
                        $str = "1. ".$cargo->company->full_company_name."\n";
                        $str .= "2. ".$cargoItem->cargo_tonnage->name."\n";
                        $str .= "3. ".$cargoItem->vincode."\n";
                        $str .= "4. ".$cargo->date_time."\n";
                        $str .= "5. ".$cargoItem->car_number."\n";
                        $str .= "6. ".$cargo_area_name."\n";
                        $str .= "7. ".$cargoItem->weight;
                    @endphp
                    <div class="qr-code">
                        <?php
                        // quick and simple:
                        echo '<img width="720" height="720" src="'.(new QRCode)->render($str).'" alt="QR Code" />';
                        ?>
                    </div>

                    <div class="contact-info">
                        <p style="font-weight: bold;text-align: center;font-size: 30px; margin-bottom: 5px !important;white-space: normal;"><strong>Клиент: </strong>{{ $cargo->company->full_company_name }}</p>
                        <p style="font-weight: bold;text-align: center;font-size: 30px; margin-bottom: 5px !important;white-space: normal;"><strong>Наименование: </strong>{{ $cargoItem->cargo_tonnage->name }}</p>
                        <p style="font-weight: normal;text-align: center;font-size: 30px; margin-bottom: 5px !important;white-space: normal;"><strong>ВИНКОД: </strong>{{ $cargoItem->vincode }}</p>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
