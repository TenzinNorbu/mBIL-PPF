<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ public_path('bootstrap/bootstrap.min.css') }}"/>
</head>

<body>
<style>
    /** Define the margins of your page **/
    @page {
        margin: 100px 25px;
    }

    @media print {
        .export-table {
            overflow: visible !important;
        }
    }

    header {
        position: fixed;
        top: -60px;
        left: 0px;
        right: 0px;
        height: 50px;
    }

    footer {
        position: fixed;
        bottom: -60px;
        left: 0px;
        right: 0px;
        height: 50px;
    }
</style>

{{--header area--}}
<header>
    <center>
        <img src="{{ public_path('images/letter-head-bil.png') }}" alt="" style="width: 260px;height: 58px">
    </center>
</header>
{{--header area end--}}

{{--footer--}}
<footer>
    <img src="{{ public_path('images/letter-head-footer.jpg') }}" alt="" style="max-width: 100%;">
</footer>
{{--footer end--}}

{{--main content--}}
<main>
    @yield('content')
</main>
{{--main content end--}}

</body>
</html>
