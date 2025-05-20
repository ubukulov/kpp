@extends('cabinet.cabinet')
@push('cabinet_styles')
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

        <form action="{{ route('cabinet.white-cars.guest.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">

                <div class="form-group">
                    <label>ФИО водителя (гостя) <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('full_name') }}" required style="text-transform: uppercase;" class="form-control" name="last_name">
                </div>

                <div class="form-group">
                    <label>Номер машины <span style="color: red;">*</span></label>
                    <input type="text" value="{{ old('gov_number') }}" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" class="form-control @error('gov_number') is-invalid @enderror" name="gov_number" required>
                </div>

                <div class="form-group">
                    <label>Цель визита (гостя) <span style="color: red;">*</span></label>
                    <input type="text" required style="text-transform: uppercase;" class="form-control" name="note">
                </div>

                <div class="form-group">
                    <label>Планируемая дата приезда (гостя) <span style="color: red;">*</span></label>
                    <input type="datetime-local" required style="text-transform: uppercase;" class="form-control" name="planned_arrival_date">
                </div>

            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <a style="margin-left: 20px;" href="{{ route('cabinet.white-cars.guest.index') }}" class="btn btn-warning">Отменить</a>
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
            el: '#admin_wcl',
            data () {
                return {
                    pass_type: 0,
                }
            },
        });
    </script>
@endpush
