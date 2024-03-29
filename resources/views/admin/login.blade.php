<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Панель управления | Авторизоваться</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <style>
        .blackout {
            background: rgba(0,0,0,0.4) !important;
            margin: 0 auto;
            width: 100%;
            height: 100%;
            top: 0;
            position: absolute;
            z-index: 9999;
            left: 0;
        }
        .overlay {
            margin: 0 auto;
            width: 90px;
            top: 40%;
            position: absolute;
            z-index: 10000;
            left: 50%;
        }
    </style>

    <script lang="javascript">
        let doc = window.document;

        window.onload = function(){
            let bt = document.getElementById('blackout');
            bt.style.display = 'none';
        };

        document.addEventListener('keydown', function(event) {
            if (event.code === 'ENTER') {
                let bt = document.getElementById('blackout');
                bt.style.display = 'block';
            }
        });

    </script>
</head>
<body class="hold-transition login-page">
<div class="login-box">

    <div id="blackout" class="blackout">
        <div class="overlay">
            <i style="color: #605ca8;" class="fas fa-5x fa-sync-alt fa-spin"></i>
        </div>
    </div>

    <div class="login-logo">
        <img class="mb-4" src="/img/logo.png" alt="">
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Авторизуйтесь, чтобы войти</p>

            <form action="{{ route('admin.authenticate') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" required class="form-control" placeholder="Email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" required class="form-control" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">
                                Запомните меня
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Войти</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            {{--<p class="mb-1">
                <a href="{{ route('cab.forget-password') }}">Я забыль пароль</a>
            </p>--}}
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="/dist/js/adminlte.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('button[type="submit"]').click(function(){
            $("#blackout").css({
                'display' : 'block'
            });
        });
    });
</script>
</body>
</html>
