<?php $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Contribuyente'); ?>
<!--
Copyright SASCO SpA (https://sasco.cl)
Plataforma de facturación electrónica usando LibreDTE (https://libredte.github.io)
LibreDTE es un proyecto de SASCO SpA que tiene como misión proveer facturación electrónica libre para Chile
Autor original: Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
Aplicación oficial en https://libredte.cl
-->
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="LibreDTE ¡facturación electrónica libre para Chile!">
        <meta name="keywords" content="factura electrónica, facturación electrónica, sii, dte, software libre, open source">
        <meta name="author" content="SASCO SpA">
        <title><?=$_header_title?></title>
        <link rel="shortcut icon" href="<?=$_base?>/img/favicon.png" />
        <link rel="stylesheet" href="<?=$_base?>/layouts/Bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?=$_base?>/layouts/Bootstrap/css/bootstrap-theme.min.css" />
        <link rel="stylesheet" href="<?=$_base?>/layouts/Bootstrap/css/style.css" />
        <link rel="stylesheet" href="<?=$_base?>/css/style.css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="<?=$_base?>/js/html5shiv.js"></script>
            <script src="<?=$_base?>/js/respond.js"></script>
        <![endif]-->
        <script src="<?=$_base?>/js/jquery.js"></script>
        <script src="<?=$_base?>/layouts/Bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript">
            var _url = "<?=$_url?>",
                _base = "<?=$_base?>",
                _request = "<?=$_request?>"
            ;
        </script>
        <script type="text/javascript" src="<?=$_base?>/js/__.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/form.js"></script>
        <link rel="stylesheet" href="<?=$_base?>/css/navpanel.css" />
        <script type="text/javascript" src="<?=$_base?>/js/datepicker/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?=$_base?>/js/datepicker/bootstrap-datepicker.es.js"></script>
        <link rel="stylesheet" href="<?=$_base?>/js/datepicker/datepicker3.css" />
        <link rel="stylesheet" href="<?=$_base?>/css/font-awesome.min.css" />
        <script type="text/javascript" src="<?=$_base?>/js/app.js"></script>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
        <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
<?=$_header_extra?>
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Ocultar mostrar navegación</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?=$_base?>/"><?=$_body_title?></a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
<?php
foreach ($_nav_website as $link=>$name) {
    $active = $_page == $link ? ' active' : '';
    if ($link[0]=='/') $link = $_base.$link;
    if (isset($name['nav'])) {
        $title = isset($name['desc']) ? $name['desc'] : (isset($name['title']) ? $name['title'] : '');
        $icon = isset($name['icon']) ? '<span class="'.$name['icon'].'"></span> ' : '';
        echo '                        <li class="dropdown',$active,'">',"\n";
        echo '                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" title="',$title,'">',$icon,$name['name'],' <span class="caret"></span></a>',"\n";
        echo '                            <ul class="dropdown-menu" role="menu">',"\n";
        foreach($name['nav'] as $l=>$n) {
            if ($l[0]=='/') $l = $link.$l;
            echo '                                <li><a href="',$l,'">',$n,'</a></li>',"\n";
        }
        echo '                            </ul>',"\n";
        echo '                        </li>',"\n";
    } else {
        if (is_array($name)) {
            $title = isset($name['desc']) ? $name['desc'] : (isset($name['title']) ? $name['title'] : '');
            $icon = isset($name['icon']) ? '<span class="'.$name['icon'].'"></span> ' : '';
            $name = $name['name'];
        } else $title = $icon = '';
        echo '                        <li class="'.$active.'"><a href="',$link,'" title="',$title,'">',$icon.$name,'</a></li>',"\n";
    }
}
?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
<?php if (!$_Auth->logged()) : ?>
<?php if (\sowerphp\core\Configure::read('app.self_register')) : ?>
                        <li><a href="<?=$_base?>/usuarios/registrar"><span class="text-primary"> ¡Regístrate!</span></a></li>
<?php endif; ?>
                        <li><a href="<?=$_base?>/usuarios/ingresar"><span class="fas fa-sign-in-alt" aria-hidden="true"></span> Iniciar sesión</a></li>
<?php else : ?>
<?php if($Emisor) : ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                <strong><?=$Emisor->getRUT()?> <span class="caret"></span></strong>
                            </a>
                            <ul class="dropdown-menu" role="menu">
<?php
$n_links = 0;
foreach ($Emisor->getLinks() as $link => $name) {
    if ($name == '-') {
        if ($n_links) {
            echo '                                <li class="divider"></li>',"\n";
        }
        $n_links = 0;
    } else {
        if ($link[0]=='/') {
            if ($_Auth->check($link)) {
                $n_links++;
                echo '                                <li><a href="',$_base,$link,'">',$name,'</a></li>',"\n";
            }
        } else {
            $n_links++;
            echo '                                <li><a href="',$link,'">',$name,'</a></li>',"\n";
        }
    }
}
?>
<?php if ($Emisor->usuarioAutorizado($_Auth->User, 'admin')) : ?>
                                <li class="divider"></li>
                                <li><a href="<?=$_base?>/dte/contribuyentes/modificar/<?=$Emisor->rut?>"><span class="fa fa-building"></span> Modificar empresa</a></li>
                                <li><a href="<?=$_base?>/dte/contribuyentes/usuarios/<?=$Emisor->rut?>"><span class="fa fa-users"></span> Autorizar usuarios</a></li>
<?php endif; ?>
                            </ul>
                        </li>
<?php endif; ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><strong>Aplicación <span class="caret"></span></strong></a>
                            <ul class="dropdown-menu" role="menu">
<?php
foreach ($_nav_app as $link=>&$info) {
    if ($_Auth->check($link)) {
        if(!is_array($info)) $info = ['name'=>$info];
        echo '                                <li><a href="',$_base,$link,'">',$info['name'],'</a></li>',"\n";
    }
}
?>
                                <li class="divider"></li>
                                <li><a href="<?=$_base?>/usuarios/perfil"><span class="fa fa-user fa-fw" aria-hidden="true"></span> Perfil de usuario</a></li>
                                <li><a href="<?=$_base?>/usuarios/salir"><span class="fas fa-sign-out-alt fa-fw" aria-hidden="true"></span> Cerrar sesión</a></li>
                            </ul>
                        </li>
<?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container main-container">
<!-- BEGIN MAIN CONTENT -->
<?php
// mensaje si la empresa está en certificación
if ($Emisor and $Emisor->config_ambiente_en_certificacion) {
    echo '<div class="bg-warning center lead" style="padding:0.5em"><strong>AMBIENTE DE CERTIFICACIÓN / PRUEBAS: '.$Emisor->razon_social.'</strong></div>',"\n";
}
// menú de módulos si hay sesión iniciada
if ($_Auth->logged() and $_module_breadcrumb) {
    echo '<ol class="breadcrumb hidden-print">',"\n";
    $url = '/';
    foreach ($_module_breadcrumb as $link => &$name) {
        if (is_string($link)) {
            echo '    <li><a href="',$_base,$url,$link,'">',$name,'</a></li>',"\n";
            $url .= $link.'/';
        } else {
            echo '    <li class="active">',$name,'</li>';
        }
    }
    echo '</ol>',"\n";
}
// mensaje de sesión
$message = \sowerphp\core\Model_Datasource_Session::message();
if ($message) {
    $icons = [
        'success' => 'ok',
        'info' => 'info-sign',
        'warning' => 'warning-sign',
        'danger' => 'exclamation-sign',
    ];
    echo '<div class="alert alert-',$message['type'],'" role="alert">',"\n";
    echo '    <span class="glyphicon glyphicon-',$icons[$message['type']],'" aria-hidden="true"></span>',"\n";
    echo '    <span class="sr-only">',$message['type'],': </span>',$message['text'],"\n";
    echo '    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="Cerrar">&times;</a>',"\n";
    echo '</div>'."\n";
}
// contenido de la página
echo $_content;
?>
<!-- END MAIN CONTENT -->
        </div>
        <footer class="footer hidden-print">
            <div class="container">
                <div class="text-muted pull-left">
                    <?=(is_array($_footer)?$_footer['left']:$_footer)."\n"?>
                </div>
                <div class="text-muted pull-right" style="text-align:right">
<?=!empty($_footer['right'])?'                    '.$_footer['right'].'<br/>'."\n":''?>
<?php
if ($_Auth->logged()) {
    echo '                    <span>';
    echo '[stats] time: ',round(microtime(true)-TIME_START, 2),' [s] - ';
    echo 'memory: ',round(memory_get_usage()/1024/1024,2),' [MiB] - ';
    echo 'querys: ',\sowerphp\core\Model_Datasource_Database_Manager::$querysCount,' - ';
    echo 'cache: ',\sowerphp\core\Cache::$setCount,'/',\sowerphp\core\Cache::$getCount,'</span>',"\n";
}
?>
                </div>
            </div>
        </footer>
    </body>
</html>
