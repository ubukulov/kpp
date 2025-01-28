@extends('admin.admin')
@push('admin_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
@section('content')
    <!-- general form elements -->
    <div id="admin_wcl" class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Добавить</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->

        <form action="{{ route('admin.white-car-list.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>ФИО водителя <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('full_name') }}" required style="text-transform: uppercase;" class="form-control" name="full_name">
                </div>

                <div class="form-group">
                    <label>Должность <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('position') }}" required style="text-transform: uppercase;" class="form-control" name="position">
                </div>

                <div class="form-group">
                    <label>Номер машины <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('gov_number') }}" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" class="form-control @error('gov_number') is-invalid @enderror" name="gov_number" required>
                </div>

                <div class="form-group">
                    <label>Марка машины <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('mark_car') }}" required style="text-transform: uppercase;" class="form-control" name="mark_car">
                </div>

                <div class="form-group">
                    <label>Клиент <span style="color: red;">*</span></label>
                    <select name="company_id" class="form-control select2bs4">
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Тип пропуска <span style="color: red;">*</span></label>
                    <select name="pass_type" v-model="pass_type" class="form-control">
                        <option value="0">Укажите тип</option>
                        <option value="1">Сотрудник компании</option>
                        <option value="2">Постоянный подрядчик/партнер</option>
                    </select>
                </div>

                <div v-if="pass_type  == 2" class="form-group">
                    <label>Название компании Подрядчика/партнера</label>
                    <input type="text" class="form-control" required name="contractor_name">
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a style="margin-left: 20px;" href="{{ route('admin.white-car-list.index') }}" class="btn btn-warning">Отменить</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
@push('admin_scripts')
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
            el: '#admin_wcl',
            data () {
                return {
                    pass_type: 0,
                }
            },
        });
    </script>
@endpush
