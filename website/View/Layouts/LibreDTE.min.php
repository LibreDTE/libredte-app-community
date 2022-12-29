<!--
LibreDTE 2015 - 2019
Copyright SASCO SpA (https://sasco.cl)
Plataforma de facturación electrónica usando LibreDTE (https://facturacionlibre.cl)
LibreDTE es un proyecto de SASCO SpA que tiene como misión proveer facturación electrónica libre para Chile
Autor original: Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
Aplicación oficial: https://libredte.cl
Framework: SowerPHP (https://sowerphp.org)
Layout: oficial de Bootstrap 4
-->
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.2.0/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Oswald|Raleway" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="<?=$_base?>/css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
        <script>
            var _url = "<?=$_url?>",
                _base = "<?=$_base?>",
                _request = "<?=$_request?>"
            ;
        </script>
        <script src="https://cdn.sasco.cl/js/__.min.js"></script>
        <script src="https://cdn.sasco.cl/js/form.min.js"></script>
        <script src="https://cdn.sasco.cl/js/datepicker/bootstrap-datepicker.js"></script>
        <script src="https://cdn.sasco.cl/js/datepicker/bootstrap-datepicker.es.js"></script>
        <link rel="stylesheet" href="https://cdn.sasco.cl/js/datepicker/datepicker3.css" />
        <script src="https://cdn.jsdelivr.net/npm/bootbox@5.1.3/dist/bootbox.all.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    </head>
    <body>
<?php echo $_content; ?>
    </body>
</html>
