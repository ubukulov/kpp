<ul class="nav nav-pills mb-3" id="cabinet-wms" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="receive-tab" data-toggle="pill" href="#receive" role="tab" aria-controls="receive" aria-selected="true">Приемка</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="ship-tab" data-toggle="pill" href="#ship" role="tab" aria-controls="ship" aria-selected="false">Отгрузка</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="return-tab" data-toggle="pill" href="#return" role="tab" aria-controls="return" aria-selected="false">Возврат</a>
    </li>
</ul>
<div class="tab-content" id="cabinet-wmsContent">

    <div class="tab-pane fade show active" id="receive" role="tabpanel" aria-labelledby="receive-tab">
        @include('cabinet.wms._receive')
    </div>

    <div class="tab-pane fade" id="ship" role="tabpanel" aria-labelledby="ship-tab">
        @include('cabinet.wms._ship')
    </div>

    <div class="tab-pane fade" id="return" role="tabpanel" aria-labelledby="return-tab">
        @include('cabinet.wms._return')
    </div>

</div>
