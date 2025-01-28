<table id="shipTable" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th>Код расходного заказа</th>
        <th>Дата создание</th>
        <th>Действие</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ships as $ship)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $ship['ord_Code'] }}</td>
            <td>{{ $ship['ord_InputDate'] }}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger">Переотправить</button>
                    <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" style="min-width: auto; margin: 0px;">
                        <div class="btn-group dropright">
                            <button type="button" class="btn btn-danger">
                                GIINFO
                            </button>
                            <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
                                <span class="sr-only">Toggle Dropright</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('cabinet.wms.resendShip', ['type' => '9_1', 'shipID' => $ship['OrderID']]) }}">9_1</a>
                                <a class="dropdown-item" href="{{ route('cabinet.wms.resendShip', ['type' => '7_2', 'shipID' => $ship['OrderID']]) }}">7_2</a>
                            </div>
                        </div>

                        <div style="margin-top: 10px;" class="dropright">
                            <a style="text-align: left; width: 100%;" class="btn btn-success" href="{{ route('cabinet.wms.shipAckansUpdated', ['OrderID' => $ship['OrderID']]) }}">
                                ACKANS
                            </a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
