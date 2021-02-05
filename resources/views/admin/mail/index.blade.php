@extends('admin.admin')
@push('admin_styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h3 class="card-title">Массовая рассылка по WhatsApp</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('admin.whatsapp.send') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Предложение</label>
                                    <textarea required name="prefer" class="form-control" cols="30" rows="10"></textarea>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">Отправить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
    <style>
        .select2-selection__choice{
            color: #000 !important;
        }
    </style>
@stop

@push('admin_scripts')
    <!-- Select2 -->
    <script src="/plugins/select2/js/select2.full.min.js"></script>

    <script>
        $(function () {
            $('.select2').select2();
        })
    </script>
@endpush
