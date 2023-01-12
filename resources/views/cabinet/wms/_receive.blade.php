<table id="receiveTable" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th>Код приема</th>
        <th>Дата создание</th>
        <th>Действие</th>
    </tr>
    </thead>
    <tbody>
    @foreach($logs as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item['rct_Code'] }}</td>
            <td>{{ $item['rct_InputDate'] }}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger">Переотправить</button>
                    <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>

                    <div class="dropdown-menu" style="min-width: auto; margin: 0px;">

                        <div class="btn-group dropright">
                            <button type="button" class="btn btn-danger">
                                GRINFO
                            </button>

                            <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
                                <span class="sr-only">Toggle Dropright</span>
                            </button>

                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('cabinet.wms.resendUpdated', ['type' => '9_1', 'receiptID' => $item['ReceiptID']]) }}">9_1</a>
                                <a class="dropdown-item" href="{{ route('cabinet.wms.resendUpdated', ['type' => '7_2', 'receiptID' => $item['ReceiptID']]) }}">7_2</a>
                            </div>
                        </div>

                        <div style="margin-top: 10px;" class="dropright">
                            <a style="text-align: left; width: 100%;" class="btn btn-success" href="{{ route('cabinet.wms.ackansUpdated', ['receiptID' => $item['rav_ReceiptID']]) }}">
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
