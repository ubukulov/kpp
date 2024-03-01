<html>
<head>
    <title>Распечатать заявку</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media print {
            #btn {
                display: none;
            }
            .br {
                page-break-after: always;
            }
        }

    </style>
</head>
<body>
<br><br>
<div id="printSelection" style="width:1000px; height:auto; padding-left:20px;padding-top:10px;">
    <div class="row">
        <table border="0" cellpadding="5" cellspacing="2">
            <tr>
                <td>
                    <img src="/img/ilc_logo.png" alt="ILC">
                </td>
                <td style="padding-left: 100px;">
                    <p><strong>ТОО "International Logistics Corporation"</strong></p>
                    <p>
                        БИН 090400226877 Расчетный счет KZ276010131000145736   в АО Народном Банке <br>Казахстана г. Алматы БИК HSBKKZKK
                    </p>
                </td>
            </tr>
        </table>

        <div class="col-md-12">
            <p style="text-align: center;">
                <strong>РАСХОДНАЯ НАКЛАДНАЯ №{{ $technique_task->getNumber() }} от {{ date('d.m.Y') }} г.</strong>
            </p>
            <p>Отправитель: <strong><i><u>ТОО "International Logistics Corporation"</u></i></strong></p>
            <p>Грузополучатель: _________________________________________</p>
            <p>По доверенности: ________________________________________</p>
            <p>Через кого: ________________________________________________</p>
        </div>
    </div>
    <table border="1" cellpadding="5" cellspacing="2">

        @foreach($technique_stocks as $stock)

            @if($loop->index==0)
                <tr>
                    <th>№</th>
                    <th>Клиент</th>
                    <th>Вин код</th>
                    <th>Модель</th>
                    <th>Цвет</th>
                    <th>Тип</th>
                    <th>Зона хранения</th>
                </tr>


            @endif

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $stock->full_company_name }}</td>
                <td>{{ $stock->vin_code }}</td>
                <td>{{ $stock->mark }}</td>
                <td>{{ $stock->owner }}</td>
                <td>{{ $stock->type_name }}</td>
                <td>{{ $stock->place_name }}</td>
            </tr>
        @endforeach
    </table>

    <div class="row mt-5">
        <table border="0" cellpadding="5" cellspacing="2">
            <tr>
                <td>
                    <p>Отпустил: _____________________________________</p>
                </td>
                <td style="padding-left: 100px;">
                    <p>Получил: ______________________________________</p>
                </td>
            </tr>
        </table>
    </div>

    <br>
    <input id="btn" name="1" value="Распечатать" type="button" onclick="window.print();">
</div>
</body>
</html>
