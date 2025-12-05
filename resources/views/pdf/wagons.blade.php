<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список вагонов</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h2 { background: #f0f0f0; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #aaa; padding: 5px; text-align: left; }
        /* Разделение на страницы в PDF */
        .page-break {
            page-break-after: always;
            font-size: 12px;
            font-size: 12px;
        }

        /* Чтобы последняя страница не имела пустой лист */
        .page-break:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>
@foreach($wagonStocks as $trackNumber => $wagons)
    <div class="page-break">
        <h2>Путь {{ $trackNumber }} ({{ count($wagons) }} шт.)</h2>
        <table>
            <thead>
            <tr>
                <th>№п/п</th>
                <th>Вагон</th>
                <th>Состояние</th>
                <th>Контейнер</th>
                <th>Груз.получатель</th>
                <th>Статус</th>
                <th>Дата подачи</th>
            </tr>
            </thead>
            <tbody>
            @foreach($wagons as $w)
                <tr>
                    <td>{{ $w->ordinal_number }}</td>
                    <td>{{ $w->wagon->number }}</td>
                    <td>{{ $w->state }}</td>
                    <td>{{ $w->wagon_containers_number }}</td>
                    <td>{{ $w->consignee_name }}</td>
                    <td>{{ $w->wagon_state_label }}</td>
                    <td>{{ $w->reception_datetime->format('d.m.Y H:i:s') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>
