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
        <script>
            var _url = "<?=$_url?>",
                _base = "<?=$_base?>",
                _request = "<?=$_request?>"
            ;
        </script>
        <script src="https://cdn.libredte.cl/js/__.js"></script>
        <script src="https://cdn.libredte.cl/js/form.js"></script>
        <script src="https://cdn.libredte.cl/js/datepicker/bootstrap-datepicker.js"></script>
        <script src="https://cdn.libredte.cl/js/datepicker/bootstrap-datepicker.es.js"></script>
        <link rel="stylesheet" href="https://cdn.libredte.cl/js/datepicker/datepicker3.css" />
        <link type="text/css" href="https://cdn.libredte.cl/css/typeahead.css" rel="stylesheet" />
        <script src="https://cdn.libredte.cl/js/typeahead.bundle.js"></script>
        <script src="<?=$_base?>/js/app.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" />
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.js" integrity="sha512-467grL09I/ffq86LVdwDzi86uaxuAhFZyjC99D6CC1vghMp1YAs+DqCgRvhEtZIKX+o9lR0F2bro6qniyeCMEQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/notyf/3.10.0/notyf.min.css" integrity="sha512-ZX18S8AwqoIm9QCd1EYun82IryFikdJt7lxj6583zx5Rvr5HoreO9tWY6f2VhSxvK+48vYFSf4zFtX/t2ge62g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<?=$_header_extra?>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?=$_base?>/"><?=$_body_title?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto">
                    <?php
                        $dropdown_id_count = 1;
                        foreach ($_nav_website as $link=>$name) {
                            $active = $_page == $link ? ' active' : '';
                            if ($link[0] == '/') $link = $_base.$link;
                            if (isset($name['nav'])) {
                                $dropdown_id = 'dropdown_'.$dropdown_id_count++;
                                $title = isset($name['desc']) ? $name['desc'] : (isset($name['title']) ? $name['title'] : '');
                                $icon = isset($name['icon']) ? '<span class="'.$name['icon'].'"></span> ' : '';
                                echo '<li class="nav-item dropdown',$active,'">',"\n";
                                echo '<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false" id="',$dropdown_id,'" title="',$title,'">',$icon,$name['name'],'</a>',"\n";
                                echo '<div class="dropdown-menu" aria-labelledby="',$dropdown_id,'">',"\n";
                                foreach($name['nav'] as $l=>$n) {
                                    if ($l[0] == '/') $l = $link.$l;
                                    echo '<a href="',$l,'" class="dropdown-item">',$n,'</a>',"\n";
                                }
                                echo '</div>',"\n";
                                echo '</li>',"\n";
                            } else {
                                if (is_array($name)) {
                                    $title = isset($name['desc']) ? $name['desc'] : (isset($name['title']) ? $name['title'] : '');
                                    $icon = isset($name['icon']) ? '<span class="'.$name['icon'].'"></span> ' : '';
                                    $name = $name['name'];
                                } else $title = $icon = '';
                                echo '<li class="nav-item'.$active.'"><a href="',$link,'" title="',$title,'" class="nav-link">',$icon,$name,'</a></li>',"\n";
                            }
                        }
                    ?>
                </ul>
                <ul class="nav navbar-nav ms-auto">
                    <?php if (!$_Auth->logged()) : ?>
                        <li class="nav-item"><a href="<?=$_base?>/usuarios/ingresar" class="nav-link"><span class="fas fa-sign-in-alt" aria-hidden="true"></span> Iniciar sesión</a></li>
                    <?php else : ?>
                    <?php
                    $Account = $_Auth->User->getEmailAccount();
                    if ($Account) {
                        $emails = $Account->countUnreadMessages();
                        echo '<li class="nav-item"><a href="'.$Account->getUserUrl().'" class="nav-link"><i class="far fa-envelope"></i> '.($emails?' <span class="badge bg-primary">'.num($emails).'</span>':'').'</a></li>',"\n";
                    }
                    ?>
                        <?php if ($Contribuyente) : ?>
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false" id="dropdown_contribuyente">
                                    <strong><?=$Contribuyente->getRUT()?></strong>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown_contribuyente">
                                    <?php
                                        $n_links = 0;
                                        foreach ($Contribuyente->getLinks() as $l) {
                                            if ($l->nombre == '-') {
                                                if ($n_links) {
                                                    echo '<div class="dropdown-divider"></div>',"\n";
                                                }
                                                $n_links = 0;
                                            } else {
                                                if ($l->enlace[0] == '/') {
                                                    if ($_Auth->check($l->enlace)) {
                                                        $n_links++;
                                                        echo '<a href="',$_base,$l->enlace,'" class="dropdown-item"><i class="'.$l->icono.' fa-fw"></i> '.$l->nombre.'</a>',"\n";
                                                    }
                                                } else {
                                                    $n_links++;
                                                    echo '<a href="',$l->enlace,'" class="dropdown-item"><i class="'.$l->icono.' fa-fw"></i> ',$l->nombre,'</a>',"\n";
                                                }
                                            }
                                        }
                                    ?>
                                    <?php if ($Contribuyente->usuarioAutorizado($_Auth->User, 'admin')) : ?>
                                        <div class="dropdown-divider"></div>
                                        <a href="<?=$_base?>/dte/contribuyentes/modificar" class="dropdown-item">
                                            <i class="fas fa-edit fa-fw"></i> Configuración de la empresa
                                        </a>
                                        <a href="<?=$_base?>/dte/contribuyentes/usuarios" class="dropdown-item">
                                            <i class="fas fa-users fa-fw"></i> Usuarios de la empresa
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside" aria-expanded="false" id="dropdown_menu"><strong>Menú</strong></a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                                <?php $anio = date('Y'); $dia = date('Y-m-d'); ?>
                                <?php foreach ($_nav_app as $module => $nav) : ?>
                                <?php if ($_Auth->check($nav['link'])) : ?>
                                    <?php if (isset($nav['menu'])) : ?>
                                        <li class="dropstart">
                                        <a type="button" class="dropdown-item" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="<?=$nav['icon']?> fa-fw"></span> <?=$nav['name']?>
                                        </a>
                                        <ul class="dropdown-menu dropdown-submenu dropdown-submenu-left">
                                            <?php foreach ($nav['menu'] as $link => $menu) : ?>
                                                <?php $link = str_replace(['{anio}', '{dia}'], [$anio, $dia], $link); ?>
                                                <?php if ($_Auth->check($nav['link'].$link)) : ?>
                                                <li>
                                                    <a href="<?=$_base.$nav['link'].$link?>" class="dropdown-item" title="<?=!empty($menu['desc'])?$menu['desc']:''?>">
                                                        <i class="<?=$menu['icon']?> fa-fw"></i>
                                                        <?=$menu['name']?>
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                        </li>
                                    <?php else: ?>
                                        <li><a href="<?=$_base.$nav['link']?>" class="dropdown-item"><span class="<?=$nav['icon']?>"></span> <?=$nav['name']?></a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="dropdown-divider"></div>
                                <?php if ($_Auth->User->inGroup('soporte')) : ?>
                                    <li><a class="dropdown-item" href="<?=$_base?>/sistema"><span class="fa fa-cogs fa-fw" aria-hidden="true"></span> Sistema</a></li>
                                <?php endif; ?>
                                <li><a href="<?=$_base?>/usuarios/perfil" class="dropdown-item">
                                    <span class="fa fa-user fa-fw" aria-hidden="true"></span>
                                    Perfil de usuario
                                </a></li>
                                <li><a href="<?=$_base?>/usuarios/salir" class="dropdown-item">
                                    <span class="fas fa-sign-out-alt fa-fw" aria-hidden="true"></span>
                                    Cerrar sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        </nav>
        <div class="container main-container">
<!-- BEGIN MAIN CONTENT -->
<?php
// mensaje si la empresa está en certificación
if ($Contribuyente && $Contribuyente->enCertificacion()) {
    echo '<div class="bg-info text-white text-center lead mt-2 mb-2" style="padding:0.5em"><strong>AMBIENTE DE CERTIFICACIÓN / PRUEBAS: '.$Contribuyente->razon_social.'</strong></div>',"\n";
}
// mensaje de sesión
$messages = \sowerphp\core\Model_Datasource_Session::message();
foreach ($messages as $message) {
    $icons = [
        'success' => 'ok',
        'info' => 'info-sign',
        'warning' => 'warning-sign',
        'danger' => 'exclamation-sign',
    ];
    $message['text'] = message_format($message['text']);
    echo '<div class="alert alert-',$message['type'],'" role="alert">',"\n";
    echo '<div class="float-end"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>',"\n";
    echo '<span class="glyphicon glyphicon-',$icons[$message['type']],'" aria-hidden="true"></span>',"\n";
    echo '<span class="visually-hidden">',$message['type'],': </span>',$message['text'],"\n";
    echo '</div>'."\n";
}
// contenido de la página
echo $_content;
?>
<!-- END MAIN CONTENT -->
            <div class="clearfix"></div>
            <br/>
        </div>
        <footer class="footer d-print-none">
            <div class="container">
                <div class="float-start">
                    <?=(is_array($_footer)?$_footer['left']:$_footer)."\n"?>
                </div>
                <div class="float-end text-end">
<?=!empty($_footer['right'])?$_footer['right'].'<br/>'."\n":''?>
<?php
if (isset($_Auth) && $_Auth->logged()) {
    echo '<span class="small">';
    echo 'time: ',round(microtime(true)-TIME_START, 2),' [s] - ';
    echo 'memory: ',round(memory_get_usage()/1024/1024,2),' [MiB] - ';
    echo 'querys: ',\sowerphp\core\Model_Datasource_Database_Manager::$querysCount,' - ';
    echo 'cache: ',\sowerphp\core\Cache::$setCount,'/',\sowerphp\core\Cache::$getCount,'</span>',"\n";
}
?>
                </div>
                <div class="clearfix"></div>
            </div>
        </footer>
    </body>
</html>
