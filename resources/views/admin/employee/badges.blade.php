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
            width:69mm;
            height:111mm;
            border:2px dotted #000;
            padding-top: 5px;
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
            padding: 0px 0px 0px 10px;
            font-size: 14px;
        }
        .contact-info p {
            margin-bottom: 0px !important;
        }
        .user_img {
            text-align: center;
            margin-bottom: 10px;
        }
        .user_img img {
            max-width: 100%;
            height: 40mm;
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
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
        @foreach($users as $user)
            <div class="col-sm-4" style="margin-bottom: 10px">
                <div class="badge" id="printSelection2">
                    <div class="user_img">
                        @if(empty($user->image))
                            <img src="/img/default-user-image.png" alt="">
                        @else
                            <img src="{{ $user->image }}" alt="">
                        @endif
                    </div>
                    <div class="contact-info">
                        <p style="font-weight: bold;text-align: center;font-size: 18px; margin-bottom: 5px !important;white-space: normal;">{{ $user->full_name }}</p>
                        <p style="font-weight: bold;text-align: center;font-size: 16px; margin-bottom: 5px !important;white-space: normal;">{{ $user->position->title }}</p>
                        <p style="font-weight: normal;text-align: center;font-size: 16px; margin-bottom: 5px !important;white-space: normal;">{{ $user->company->short_ru_name }}</p>
                    </div>
                    <div class="qr-code">
                        <?php
                        // quick and simple:
                        echo '<img width="150" height="150" src="'.(new QRCode)->render($user->getUuid()).'" alt="QR Code" />';
                        ?>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>
<br>
<input name="1" value="Распечатать" type="button" onclick="window.print();">
</body>
</html>
