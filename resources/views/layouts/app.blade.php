<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/driver.css">
{{--    <link rel="manifest" href="/manifest.json">--}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css">
    <title>КПП - разрешение на въезд</title>
    @stack('styles')
</head>
<body>
<div id="app">
    @yield('content')
</div>

<!-- Optional JavaScript -->
<script src="/js/app.js"></script>
{{--<script>
    window.onload = () => {
        'use strict';

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker
                .register('./sw.js');
        }
    }
</script>--}}
@stack('scripts')

<script type="text/javascript">
    function no_cirilic(input){
        let re = /[а-яё\. ]/gi;
        input.value = input.value.replace(re, '')
    }
	/*setTimeout(function(){
	   window.location.reload(1);
	}, 1000*60*60);*/
</script>
</body>
</html>
