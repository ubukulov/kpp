<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Корешок</title>
    <style>
        .spine_wrap {
            margin-left: 20px;
            width: 520px;
            border-right: 2px dashed;
            font-size: 18px;
        }
        .spine_table {
            width: 500px;
            border: 4px solid;
        }
        .spine_table td {
            padding: 0 5px;
            border: 2px solid #000;
            color: #000;
            font-weight: 500;
        }
        .spine_table td:last-child {
            text-align: center;
        }
        .spine_table td:first-child {
            text-align: right;
        }
        @media print {
            table {
                border: 4px solid #000 !important;
            }
            tr,td {
                border: 2px solid #000 !important;
            }
            .spine_table td {
                border: 2px solid #000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="spine">
        <div class="spine_wrap">
            <table class="table table-bordered spine_table">
                <tr>
                    <td colspan="2" class="text-center"><strong>TOO "INTERNATIONAL LOGISTICS CORPORATION"</strong></td>
                </tr>
                <tr>
                    <td>Корешок к пропуску</td>
                    <td>{{ $spine->spine_number }}</td>
                </tr>
                <tr>
                    <td>Дата</td>
                    <td>{{ $spine->created_at->format('d.m.Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td>Наименование фирмы</td>
                    <td>{{ $spine->company }}</td>
                </tr>
                <tr>
                    <td>@if($spine->type == 'receive')Завоз груза @elseВывоз груза@endif</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Расходная накладная №</td>
                    <td>{{ $spine->technique_task_number }}</td>
                </tr>
                <tr>
                    <td>Наименование груза</td>
                    <td>{{ $spine->name }}</td>
                </tr>
                <tr>
                    <td>Номер контейнера</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Номер автомашины</td>
                    <td>{{ $spine->car_number }}</td>
                </tr>
                <tr>
                    <td>ФИО водителя</td>
                    <td>{{ $spine->driver_name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">Ознакомлен с правилами по технике безопасности</td>
                </tr>
                <tr>
                    <td>Подпись (сдал/получил)</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Диспетчер</td>
                    <td>{{ $spine->user_name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">На территории терминала запрещается: курить, бросать мусор, превышать скорость более 20 км/ч. <br> Штраф 5000 тенге.</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="spine">
        <div class="spine_wrap">
            <table class="table table-bordered spine_table">
                <tr>
                    <td colspan="2" class="text-center"><strong>TOO "INTERNATIONAL LOGISTICS CORPORATION"</strong></td>
                </tr>
                <tr>
                    <td>Разовый пропуск</td>
                    <td>{{ $spine->spine_number }}</td>
                </tr>
                <tr>
                    <td>Дата</td>
                    <td>{{ $spine->created_at->format('d.m.Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td>Наименование фирмы</td>
                    <td>{{ $spine->company }}</td>
                </tr>
                <tr>
                    <td>@if($spine->type == 'receive')Завоз груза @elseВывоз груза@endif</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Расходная накладная №</td>
                    <td>{{ $spine->technique_task_number }}</td>
                </tr>
                <tr>
                    <td>Наименование груза</td>
                    <td>{{ $spine->name }}</td>
                </tr>
                <tr>
                    <td>Номер контейнера</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Номер автомашины</td>
                    <td>{{ $spine->car_number }}</td>
                </tr>
                <tr>
                    <td>ФИО водителя</td>
                    <td>{{ $spine->driver_name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">Ознакомлен с правилами по технике безопасности</td>
                </tr>
                <tr>
                    <td>Подпись (сдал/получил)</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Диспетчер</td>
                    <td>{{ $spine->user_name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">На территории терминала запрещается: курить, бросать мусор, превышать скорость более 20 км/ч. <br> Штраф 5000 тенге.</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
