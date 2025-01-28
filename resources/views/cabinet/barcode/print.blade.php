<html>
<head>
    <title>JTI Code Print</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>

    </style>
</head>
<body>
<br><br>
<div class="row">
    <div class="col-md-12">
        @foreach(json_decode($_GET['code']) as $code)
        <div id="printSelection" style="background: #fff; text-align: center;">
            <h1 style="font-size: 200px; font-weight: bold;"><?=$_GET['client']?></h1>
            <img style="width: 100%; height: 100%; background: #fff;" src="/lib/sample-gd.php?code={{ $code }}">
            <p style="font-size: 200px; font-weight: bold; text-align: center;"><?=$code?></p>
        </div>
        @endforeach
    </div>
</div>
</body>
</html>
