@extends('layouts.app')
@section('content')
    <kpp
        :datetime="{{json_encode(date('d.m.Y H:i'))}}"
        :companies="{{ json_encode($companies) }}"
        :capacity="{{json_encode($lift_capacity)}}"
        :bodytypes="{{json_encode($body_type)}}"
    ></kpp>

    @push('scripts')
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="/js/webcam.js"></script>
        <script>
            Webcam.set({
                width: 1280,
                height: 720,
                image_format: 'jpeg',
                jpeg_quality: 90
            });
            Webcam.attach('#camera');
        </script>
        <script>
            $(function () {
                function setTimePicker(val){
                    $("#"+val).daterangepicker({
                        singleDatePicker: true,
                        // showDropdowns: true,
                        timePicker: true,
                        timePicker24Hour: true,
                        locale: {
                            format: 'DD.MM.YYYY HH:mm',
                            applyLabel: "OK",
                            cancelLabel: "Отмена",
                            firstDay: 1,
                            daysOfWeek: ["Вс","Пн","Вт","Ср","Чт","Пт","Сб"],
                            monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь']
                        }
                    });
                }
                setTimePicker('date_in');
                $('#image-fire').click(function (e) {
                    e.preventDefault();
                    Webcam.snap( function(data_uri) {
                        $(".image-tag").val(data_uri);
                        //document.getElementById('camera').innerHTML = '<img style="max-width: 100%;" src="'+data_uri+'"/>';
                    });
                });
                $('#tex_btn').click(function (e) {
                    e.preventDefault();
                    Webcam.snap( function(data_uri) {
                        $("#path_docs_back").val(data_uri);
                        //document.getElementById('tex_passport').innerHTML = '<img style="max-width: 100%;" src="'+data_uri+'"/>';
                    });
                });
            });
        </script>
    @endpush
@stop
