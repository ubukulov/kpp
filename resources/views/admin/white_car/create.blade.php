@extends('admin.admin')
@push('admin_styles')
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

        <form action="{{ route('admin.white-car-list.store') }}" method="POST" role="form">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Номер машины</label>
                    <input type="text" value="{{ old('gov_number') }}" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" class="form-control @error('gov_number') is-invalid @enderror" name="gov_number" required>
                </div>

                <div class="form-group">
                    <label>Клиент</label>
                    <select name="company_id" class="form-control select2bs4">
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->short_ru_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
    <!-- /.card -->
@stop
@push('admin_scripts')
    <!-- Select2 -->
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
@endpush
