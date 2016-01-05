<script>
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '997103020310751',
            xfbml      : true,
            version    : 'v2.4'
        });
    };
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/es_LA/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>

<div class="page-header">
  <h1>LibreDTE <small>¡facturación electrónica libre para Chile!</small></h1>
</div>

<div class="row">
    <div class="col-sm-8 col-md-8">
        <p class="objetivo lead">LibreDTE corresponde a un proyecto para interactuar con el SII de Chile, específicamente el sistema de Documentos Tributarios Electrónicos (DTE). Se considera la generación de DTE, timbraje, firma electrónica, envío de documentos y generación de versión en PDF de los mismos.</p>
        <div class="fb-like" data-share="true" data-width="450" data-show-faces="true"></div>
    </div>
    <div class="col-sm-4 col-md-4">
        <p><img src="<?=$_base?>/img/logo.png" class="img-responsive center" alt="Logo LibreDTE" style="max-width:200px" /></p>
    </div>
</div>

<div class="row" style="margin-top:2em">
    <div class="col-md-6">
        <div class="center video-container">
            <iframe width="420" height="315" src="https://www.youtube.com/embed/u8MmH-16hKI" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <div class="col-md-6">
        <div class="center video-container">
            <iframe width="420" height="315" src="https://www.youtube.com/embed/CeOFxVmC1Z4" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</div>

<div class="row" style="margin-top:2em">
    <div class="col-md-7">
        <div class="jumbotron">
            <h1>¡Únete a la comunidad!</h1>
            <p>¿Tienes dudas, sugerencias o comentarios? No dudes en escribirnos a través de nuestras redes sociales.</p>
            <p>
                <a class="btn btn-primary btn-lg" href="https://groups.google.com/forum/#!forum/libredte" role="button">Lista de correo</a>
                <a class="btn btn-primary btn-lg" href="http://wiki.libredte.cl/doku.php" role="button">Wiki</a>
            </p>
        </div>
    </div>
    <div class="col-md-5">
        <div class="jumbotron">
            <h1>Soporte oficial</h1>
            <p>¿Necesitas asesoría o consultoría con el uso de LibreDTE?</p>
            <p>
                <a class="btn btn-primary btn-lg" href="<?=$_base?>/contacto" role="button">¡Solicítala aquí!</a>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-md-6">
        <p class="bg-primary lead center" style="padding:1em">Empresas</p>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="thumbnail">
                    <a href="<?=$_base?>/dte" title="Módulo DTE">
                        <img src="<?=$_base?>/img/menus/dte.png" alt="Logo DTE" />
                    </a>
                    <div class="caption">
                        <h3>Módulo DTE</h3>
                        <p>Podrás generar los documentos tributarios a través de una interfaz web. ¡Será necesario que tengas a mano tu firma digital!</p>
                        <p><a href="<?=$_base?>/dte" class="btn btn-primary btn-block" role="button">ir al módulo &raquo;</a></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="thumbnail">
                    <a href="<?=$_base?>/certificacion" title="Certificar empresa">
                        <img src="<?=$_base?>/img/menus/certificacion.png" alt="Logo certificación" />
                    </a>
                    <div class="caption">
                        <h3>Certificación</h3>
                        <p>Funcionalidad que te ayudará a realizar el proceso de certificación requerido por el SII para ser autorizado a emitir DTE</p>
                        <p><a href="<?=$_base?>/certificacion" class="btn btn-primary btn-block" role="button">certificar empresa &raquo;</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-6">
        <p class="bg-primary lead center" style="padding:1em">Desarrolladores</p>
        <div class="row">
            <div class="col-sm-6 col-md-6">
                <div class="thumbnail">
                    <a href="<?=$_base?>/biblioteca/libredte" title="Leer sobre la biblioteca en PHP LibreDTE">
                        <img src="<?=$_base?>/img/menus/php.png" alt="Logo PHP" />
                    </a>
                    <div class="caption">
                        <h3>Biblioteca PHP</h3>
                        <p>Podrás integrar LibreDTE directamente en proyectos que estén desarrollados con PHP 5.5 o superior usando composer</p>
                        <p><a href="http://wiki.libredte.cl/doku.php/lib" class="btn btn-primary btn-block" role="button">leer más &raquo;</a></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-6">
                <div class="thumbnail">
                    <a href="<?=$_base?>/doc/api" title="Ver documentación de la API de LibreDTE">
                        <img src="<?=$_base?>/img/menus/api.png" alt="Logo API" />
                    </a>
                    <div class="caption">
                        <h3>Servicios web</h3>
                        <p>Si no usas PHP puedes utilizar la API disponible para consumir vía HTTP los servicios que ofrece en la nube LibreDTE</p>
                        <p><a href="http://wiki.libredte.cl/doku.php/sowerphp/api" class="btn btn-primary btn-block" role="button">ver documentación &raquo;</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top:3em">
    <div class="col-xs-12 col-md-offset-1 col-md-5 center">
        <a class="twitter-timeline"  href="https://twitter.com/LibreDTE" data-widget-id="643568969484488704">Tweets de @LibreDTE.</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    </div>
    <div class="col-xs-12 col-md-5 center">
        <div class="row">
            <div class="col-xs-6 col-md-4 center">
                <a href="https://twitter.com/LibreDTE" title="Síguenos en Twitter">
                    <span class="fa fa-twitter-square" style="font-size:128px"></span>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 center">
                <a href="https://www.facebook.com/LibreDTE" title="Síguenos en Facebook">
                    <span class="fa fa-facebook-square" style="font-size:128px"></span>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 center">
                <a href="https://plus.google.com/+LibredteCl" title="Síguenos en Google+">
                    <span class="fa fa-google-plus-square" style="font-size:128px"></span>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 center">
                <a href="https://www.linkedin.com/grp/home?gid=8403251" title="Síguenos en Linkedin">
                    <span class="fa fa-linkedin-square" style="font-size:128px"></span>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 center">
                <a href="https://www.youtube.com/c/LibredteCl" title="Síguenos en Youtube">
                    <span class="fa fa-youtube-square" style="font-size:128px"></span>
                </a>
            </div>
            <div class="col-xs-6 col-md-4 center">
                <a href="https://github.com/LibreDTE" title="Código fuente en GitHub">
                    <span class="fa fa-github-square" style="font-size:128px"></span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default"  style="margin-top:3em">
    <div class="panel-heading"><h3 class="panel-title">Firmas oficialmente soportadas</h3></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4 center">
                <a href="https://www.acepta.com" title="Ir al sitio de Acepta">
                    <img src="<?=$_base?>/img/ca/acepta.png" alt="Acepta" class="img-responsibe" />
                </a>
            </div>
            <div class="col-md-4 center">
                <a href="https://www.e-sign.cl" title="Ir al sitio de E-Sign">
                    <img src="<?=$_base?>/img/ca/e-sign.jpg" alt="E-Sign" class="img-responsibe" />
                </a>
            </div>
            <div class="col-md-4 center">
                <a href="https://www.e-certchile.cl" title="Ir al sitio de E-CERTCHILE">
                    <img src="<?=$_base?>/img/ca/e-certchile.png" alt="E-CERTCHILE" class="img-responsibe" />
                </a>
            </div>
        </div>
    </div>
</div>
