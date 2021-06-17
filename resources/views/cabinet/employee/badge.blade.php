<html>
<head>
    <title>Распечатать пропуск</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        #printSelection{
            width:120mm;
            height:80mm;
            border:2px dotted #000;
            padding-top: 10px;
        }
        #printSelection2{
            width:80mm;
            height:120mm;
            border:2px dotted #000;
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
        }
        .user_img img {
            width: 30mm;
            height: 40mm;
        }
    </style>
</head>
<body>
<br><br>
<div class="row">
    <div class="col-md-6">
        <div id="printSelection">
            <div class="row">
                <div class="col-md-6">
                    <div class="user_img text-left">
                        <img src="/img/default-user-image.png" alt="">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="contact-info">
                        <p><strong>{{ $user->company->short_ru_name }}</strong></p>
                        <p><strong>{{ $user->full_name }}</strong></p>
                        <p><strong>{{ $user->position->title }}</strong></p>
                    </div>
                </div>

                <div class="col-md-12">
                    <div>
                        <img src="/lib/sample-gd.php?code={{ $user->getUuid() }}" height="100">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div id="printSelection2">
            <div class="user_img">
                <img src="/img/default-user-image.png" alt="">
            </div>
            <div class="contact-info">
                <p>{{ $user->company->short_ru_name }} / {{ $user->position->title }}</p>
                <p>{{ $user->full_name }}</p>
            </div>
            <div class="qr-code">
                <?php
                use chillerlan\QRCode\QRCode;
                // quick and simple:
                echo '<img src="'.(new QRCode)->render($user->getUuid()).'" alt="QR Code" />';
                ?>
            </div>

        </div>
    </div>
</div>
<br>
<input name="1" value="Распечатать" type="button" onclick="window.print();">
</body>
</html>
