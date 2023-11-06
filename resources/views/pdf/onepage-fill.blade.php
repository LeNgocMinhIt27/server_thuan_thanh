<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <style>
        @page {
            margin: 2.5cm 1cm 2cm;
        }

        body * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
        }

        header {
            position: fixed;
            width: 100%;
            left: 0px;
            right: 0px;
            top: -2cm;
            height: 15mm;
            line-height: 30px;
            background-color: #fff;
            color: #E43924;
            font-weight: bold;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
            border-bottom: solid 1px #000;
        }

        header .title {
            margin: auto;
        }

        footer>* {
            display: table-cell;
            vertical-align: middle;
        }

        header>* {
            vertical-align: middle;
        }

        footer {
            display: table;
            table-layout: fixed;
            width: 100%;
            border-top: solid 1px #000;
            position: fixed;
            bottom: -1.5cm;
            left: 0px;
            right: 0px;
            height: 1cm;
            line-height: 25px;
            background-color: #fff;
            font-size: 11px;
        }

        footer>.copyright,
        footer>.website {
            width: 150px;
        }

        .logo {
            width: 12mm;
            height: 12mm;
            padding-right: 1mm;
        }

        main img {
            height: 100%;
        }

        .page-break {
            page-break-after: always;
        }

        .title-page-a5 {
            font-size: 14px
        }
    </style>
</head>

<body>
    <header>
        <img class="logo" src="logo.png">
        <span class="{{$pdf_size??'a4'=='a5'?'title title-page-a5':'title'}}">
            {{Config::get('app.export_name')}}
        </span>
    </header>
    <footer>
        <span class="copyright">
            Copyright &copy; <?php echo date("Y"); ?>
        </span>
        <span>
        </span>
        <span class="website">
            {{$app_domain}}
        </span>
    </footer>
    <main>
        <img src="{{$image}}">
    </main>
</body>

</html>
