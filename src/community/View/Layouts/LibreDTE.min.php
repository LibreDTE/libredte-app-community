<?php $Contribuyente = \sowerphp\core\Model_Datasource_Session::read('dte.Contribuyente'); ?>
<!--
LibreDTE: Edición Comunidad (2015 - 2024).
Copyright (C) LibreDTE <https://www.libredte.cl>
Edición Enterprise de LibreDTE, con soporte oficial, disponible en <https://www.libredte.cl>
¿Te gusta ver código? ¿Necesitas una integración? Revisa <https://www.billmysales.com>
-->
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
        <meta name="author" content="LibreDTE" />
        <meta name="description" content="LibreDTE Edición Comunidad." />
        <meta name="keywords" content="facturas, boletas, sii, dte" />
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.0/css/all.css">
        <link href="https://fonts.googleapis.com/css?family=Oswald|Raleway" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="<?=$_base?>/css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
        <script>var _url = "<?=$_url?>", _base = "<?=$_base?>", _request = "<?=$_request?>";</script>
        <script src="https://cdn.libredte.cl/js/__.js"></script>
        <script src="https://cdn.libredte.cl/js/form.js"></script>
        <script src="https://cdn.libredte.cl/js/datepicker/bootstrap-datepicker.js"></script>
        <script src="https://cdn.libredte.cl/js/datepicker/bootstrap-datepicker.es.js"></script>
        <link rel="stylesheet" href="https://cdn.libredte.cl/js/datepicker/datepicker3.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    </head>
    <body>
        <?php echo $_content; ?>
    </body>
</html>
