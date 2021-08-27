<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Авторизоваться</title>
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
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<div id="login" class="login-box">
    <div class="login-logo">
        <img class="mb-4" src="/img/logo.png" alt="">
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Авторизуйтесь, чтобы войти</p>
            <div class="input-group mb-3">
                <input v-on:keyup.enter="authenticate()" type="email" v-model="email" class="form-control" placeholder="Email">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>
            </div>
            <div class="input-group mb-3">
                <input v-on:keyup.enter="authenticate()" type="password" v-model="password" class="form-control" placeholder="Password">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" v-model="remember" id="remember">
                        <label for="remember">
                            Запомните меня
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-4">
                    <button :disabled="disabled" type="button" @click="authenticate()" class="btn btn-primary btn-block">Войти</button>
                </div>
                <!-- /.col -->
            </div>

            <div class="row" style="margin-top: 20px;" v-if="errors.length">
                <div class="col-12">
                    <div v-for="error in errors" class="alert alert-danger">
                        @{{ error }}
                    </div>
                </div>
            </div>

            {{--<p class="mb-1">
                <a href="{{ route('cab.forget-password') }}">Я забыль пароль</a>
            </p>--}}
        </div>
        <!-- /.login-card-body -->
    </div>

    <v-overlay :value="overlay">
        <v-progress-circular
            indeterminate
            size="64"
        ></v-progress-circular>
    </v-overlay>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="/dist/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script>
    new Vue({
        el: "#login",
        vuetify: new Vuetify(),
        data(){
            return {
                email: '',
                password: '',
                remember: false,
                overlay: false,
                error: false,
                message: '',
                errors: [],
                disabled: false
            }
        },
        methods: {
            authenticate(){
                this.errors = [];
                if (!this.email) {
                    this.errors.push('Укажите электронную почту.');
                } else if (!this.validEmail(this.email)) {
                    this.errors.push('Укажите корректный адрес электронной почты.');
                }
                if (!this.password) {
                    this.errors.push('Укажите пароль.');
                }

                if (!this.errors.length) {
                    this.overlay = true;
                    this.disabled = true;
                    let formData = new FormData();
                    formData.append('email', this.email);
                    formData.append('password', this.password);
                    formData.append('remember', this.remember);
                    axios.post('/login', formData)
                    .then(res => {
                        console.log(res.data);
                        window.location.href = res.data;
                        this.overlay = false;
                    })
                    .catch(err => {
                        console.log(err);
                        this.overlay = false;
                        this.disabled = false;
                        if (err.response.status == 404 || err.response.status == 400) {
                            this.error = true;
                            this.errors.push(err.response.data);
                        }
                    })
                }
            },
            validEmail: function (email) {
                var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
        }
    });
</script>
</body>
</html>
