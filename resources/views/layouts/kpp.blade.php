<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/driver.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">

    @stack('styles')

    <title>КПП - разрешение на въезд</title>
</head>
<body>
<div id="app">
    <v-app>
        @yield('content')
    </v-app>
</div>

<!-- Optional JavaScript -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script src="/js/webcam.js"></script>
<script>
    $(function () {
        $( "#date_in" ).daterangepicker({
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
        $( "#date_out" ).daterangepicker({
            singleDatePicker: true,
            // autoUpdateInput: false,
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
    });
</script>
<script>
    function setWebcam(){
        Webcam.set({
            width: 1280,
            height: 720,
            // width: 854,
            // height: 480,
            image_format: 'jpeg',
            jpeg_quality: 90
        });
        Webcam.attach('#camera');
    }
    setTimeout(setWebcam, 1000);
</script>
<script>
    $(function () {
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
@stack('scripts')

<script type="text/javascript">
    function no_cirilic(input){
        let re = /[а-яё\. ]/gi;
        input.value = input.value.replace(re, '')
    }
</script>
</body>
</html>
