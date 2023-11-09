@extends('cabinet.cabinet')
@push('cabinet_styles')
    <style>
        #printSelection2{
            width:69mm;
            height:111mm;
            border:2px dotted #000;
            padding-top: 5px;
        }
        .row{
            padding: 0px 20px;
        }
        .company{
            color: #000;
            padding: 5px 5px 5px 10px;
        }
        .qr-code {
            text-align: center;
        }
        .qr-code img {
            max-width: 100%;
        }
        .contact-info {
            padding: 0px 0px 0px 5px;
            font-size: 14px;
        }
        .contact-info p {
            margin-bottom: 0px !important;
        }
        .user_img {
            text-align: center;
            margin-bottom: 5px;
        }
        .user_img img {
            max-width: 100%;
            height: 40mm;
        }
        @page {
            size: A4;
            margin: 0;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
            .badge {
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="card-title">Цифровой талон</h3>
                        </div>
                    </div>

                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="badge" id="printSelection2">
                        <div style="font-size: 30px;margin-bottom: 5px;">
                            <span>ПРОПУСК</span>
                        </div>
                        <div class="user_img">
                            @if(empty($user->image))
                                <img width="200" height="150" src="/img/default-user-image.png" alt="">
                            @else
                                <img src="{{ $user->image }}" alt="">
                            @endif
                        </div>
                        <div class="contact-info">
                            <p style="font-weight: bold;text-align: center;font-size: 18px; margin-bottom: 5px !important;white-space: normal;">{{ $user->full_name }}</p>
                            <p style="font-weight: bold;text-align: center;font-size: 16px; margin-bottom: 5px !important;white-space: normal;">{{ $user->position->title }}</p>
                            <p style="font-weight: normal;text-align: center;font-size: 16px; margin-bottom: 5px !important;white-space: normal;">{{ $user->company->short_ru_name }}</p>
                        </div>
                        <div class="qr-code">
                            <?php
                            // quick and simple:
                            echo '<img width="120" height="120" src="'.(new \chillerlan\QRCode\QRCode)->render($user->getUuid()).'" alt="QR Code" />';
                            ?>
                        </div>

                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop
