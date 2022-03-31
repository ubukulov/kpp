@extends('cabinet.cabinet')
@push('cabinet_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
@section('content')
    <!-- general form elements -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Добавить</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->

        <form action="{{ route('cabinet.white-car-list.store') }}" method="POST" role="form">
            @csrf
            <div id="cabinet_wcl" class="card-body">
                <div class="form-group">
                    <label>ФИО водителя</label>
                    <input type="text" value="{{ old('full_name') }}" style="text-transform: uppercase;" class="form-control" name="full_name">
                </div>

                <div class="form-group">
                    <label>Должность</label>
                    <input type="text" value="{{ old('position') }}" style="text-transform: uppercase;" class="form-control" name="position">
                </div>

                <div class="form-group">
                    <label>Номер машины</label>
                    <input type="text" value="{{ old('gov_number') }}" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" class="form-control @error('gov_number') is-invalid @enderror" name="gov_number" required>
                </div>

                <div class="form-group">
                    <label>Марка машины</label>
                    <input type="text" value="{{ old('mark_car') }}" style="text-transform: uppercase;" class="form-control" name="mark_car">
                </div>

                <div class="form-group">
                    <label>Тип пропуска</label>
                    <select name="pass_type" v-model="pass_type" class="form-control">
                        <option value="1">Сотрудник компании</option>
                        <option value="2">Постоянный подрядчик/партнер</option>
                    </select>
                </div>

                <div v-if="pass_type  != 1" class="form-group">
                    <label>Название компании Подрядчика/партнера</label>
                    <input type="text" class="form-control" required name="contractor_name">
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="{{ route('cabinet.white-car-list.index') }}" class="btn btn-warning">Отменить</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
@push('cabinet_scripts')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script>
        $(function () {
            //Initialize Select2 Elements
            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
    <script>
        new Vue({
            el: '#cabinet_wcl',
            data () {
                return {
                    pass_type: 1,
                }
            },
        });
    </script>
@endpush
