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
    <img src="/img/webcont.jpg" alt="">
    <p style="text-align: right; font-style: italic; font-size: 12px;">Дата распечатки: {{ date('d.m.Y H:i:s') }}</p>
    <h2 style="text-align: center">
        @if($container_task->task_type == 'receive')
            Лист <strong>приемки</strong> контейнеров
        @else
            Лист <strong>выдачи</strong> контейнеров
        @endif

        - {{ $container_task->getTransType() }}
    </h2>
    <p style="text-align: center; font-size: 20px;">Заявка № <strong>{{ $container_task->getNumber() }}</strong> от {{ $container_task->created_at }}</p>
    <table border="1" cellpadding="5" cellspacing="2">
        {{--<thead>
            <th>№</th>
            <th>Контейнер</th>
            <th>Тип</th>
            <th>Состояние</th>
            <th>№ТС</th>
            <th>Клиент</th>
            <th>Выполнен</th>
            <th>Зона</th>
        </thead>--}}
        @foreach($container_stocks as $stock)

            @if($loop->index==0)
            <tr>
                <th>№</th>
                <th>Контейнер</th>
                <th>Тип</th>
                <th>Состояние</th>
                <th>№ТС</th>
                <th>Клиент</th>
                <th>Выполнен</th>
                <th>Зона</th>
            </tr>

            
            @endif

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><span style="font-size: 20px; font-weight: bold;">{{ $stock->container->number }}</span></td>
                <td>{{ $stock->container->container_type }}</td>
                <td>{{ $stock->state }}</td>
                <td>{{ $stock->car_number_carriage }}</td>
                <td>{{ $stock->company }}</td>
                <td></td>
                <td>{{ $stock->container_address->title }}</td>
            </tr>
        @endforeach
    </table>

    <div class="row mt-5">
        <div class="col-md-6">
            <p>Крановщик: _____________________________________</p>
        </div>

        <div class="col-md-6">
            <p>Диспетчер (Приемосдатчик): {{ Auth::user()->full_name }}</p>
        </div>
    </div>

    <br>
    <input id="btn" name="1" value="Распечатать" type="button" onclick="window.print();">
</div>
</body>
</html>
