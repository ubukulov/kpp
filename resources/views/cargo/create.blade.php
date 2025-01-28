@extends('layouts.app')
@push('styles')
    <style>
        .card{
            margin-bottom: 10px;
            font-family: monospace;
            border: 1px dashed;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div id="kt" class="kt">
                @include('partials.kt_header')

                <form action="{{ route('cargo.store') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Тип</label>
                                <select name="type" class="form-control">
                                    <option value="receive">Прием</option>
                                    <option value="ship">Выдача</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Тоннаж</label>
                                <select name="tonnage" class="form-control">
                                    <option value="small">Мелко</option>
                                    <option value="large">Крупно</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Контрагент</label>
                                <select name="company_id" class="form-control">
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->full_company_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Режим</label>
                                <select name="mode" class="form-control">
                                    <option value="customs">Таможенный</option>
                                    <option value="cvx">СВХ</option>
                                    <option value="normal">Обычный</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="positions">
                        <div class="card">
                            <div class="card-body" style="display: flex; justify-content: space-around;">

                                <div class="form-group">
                                    <label>Вид груза</label>
                                    <select name="cargo_items[type][]" class="form-control">
                                        @foreach($tonnages as $tonnage)
                                            <option value="{{ $tonnage->id }}">{{ $tonnage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Номер машины</label>
                                    <input type="text" name="cargo_items[car][]" required placeholder="845ACS05" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>VINCODE / SERIA</label>
                                    <input type="text" name="cargo_items[vincode][]" required placeholder="Vincode" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="add-position-div">
                        <button id="addPosition" type="button" class="btn btn-outline-dark">Добавить позиция</button>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col text-left">
                            <a class="btn btn-warning" href="{{ route('cargo.index') }}">
                                <i class="fa fa-arrow-circle-left"></i>&nbsp;Назад
                            </a>
                        </div>

                        <div class="col text-right">
                            <button style="font-size: 0.9rem !important;" type="submit" class="btn btn-success">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        let index = 1;
        let types = <?php echo $tonnages; ?>;
        let str = '';
        for(let i=0; i < types.length; i++) {
            str = str + "<option value=" + types[i].id + ">" + types[i].name + "</option>";
        }
        $('#addPosition').click(function(){
            index++;
            let position = '<div class="card">' +
                '<div class="card-body" style="display: flex; justify-content: space-around;">' +
                        '<div class="form-group">' +
                            '<label>Вид груза</label>' +
                                '<select name="cargo_items[type][]" class="form-control">' +
                                    str +
                                '</select>' +
                        '</div>' +
                        '<div class="form-group"><label>Номер машины</label> ' +
                            '<input type="text" name="cargo_items[car][]" required placeholder="845ACS05" class="form-control">' +
                        '</div> ' +
                        '<div class="form-group"><label>VINCODE / SERIA</label> ' +
                            '<input type="text" name="cargo_items[vincode][]" required placeholder="Vincode" class="form-control">' +
                        '</div>' +
                '</div>' +
            '</div>';

            $(".add-position-div").before(position);
        });
    </script>
@endpush
