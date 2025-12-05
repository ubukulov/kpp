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
        p {
            margin-bottom: 0;
        }
        @page {
            size: 24mm 35mm;
            margin: 0;
        }
        @media print {
            body * {
                visibility: hidden; /* скрыть всё */
            }
            .label, .label * {
                visibility: visible; /* показать только label */
            }
            .label {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0;
                width: 4cm;
                height: 7cm;
                padding: 5px;
            }
        }
        .label {
            width: 4cm;
            height: 7cm;
        }
        .fonts {
            font-size: 6px;
        }
    </style>
</head>
<body>
<br><br>
<div class="container">
    <div class="row">

        <div class="label">
            <div class="text-center mb-1">
                {!! DNS2D::getBarcodeSVG($data['code'], 'DATAMATRIX', 2, 2) !!}
            </div>

            <div class="fonts text-center">
                {{ $data['boxNumber'] }}
            </div>

            <div class="text-center fonts">
                <p>Наим. товара</p>
                <p>{{ $data['line1'] }}</p>
            </div>

            <div style="font-size: 6px;">
                <p>Материал: {{ $data['line4'] }}</p>
                <p>Страна изг: {{ $data['line8'] }}</p>
                <p>GTIN: {{ $data['gtin'] }}</p>
                <p>Артикул: {{ $data['artikul'] }}</p>
                <p>Импортёр: ТОО «LPP Kazakhstan» <br>(LPP Kazakhstan), г.Алматы,<br>Медеуский р, ул Хаджи Мукана 22/5</p>
            </div>
        </div>


    </div>
</div>
<br>
<input name="1" value="Распечатать" type="button" onclick="window.print();">
</body>
</html>
