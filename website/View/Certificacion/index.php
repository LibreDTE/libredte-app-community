<div class="page-header"><h1><?=$title?></h1></div>
<div class="card-deck">
<?php foreach ($nav as $link=>&$info): ?>
    <div class="card mb-4 text-center">
        <div class="card-body">
            <a href="<?=$_base,'/',$module,$link?>" title="<?=$info['desc']?>" class="nav-link p-0">
                <i class="<?=$info['icon']?> fa-3x" aria-hidden="true"></i>
                <p class="card-text small"><?=$info['name']?></p>
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <a class="btn btn-primary btn-lg btn-block" href="http://wiki.libredte.cl/doku.php/faq/libredte/webapp/certificacion" role="button">
            <span class="fas fa-list"></span>
            Manual paso a paso
        </a>
    </div>
    <div class="col-md-6 mb-4">
        <a class="btn btn-primary btn-lg btn-block" href="https://wiki.libredte.cl/doku.php/videos#videos_del_proceso_de_certificacion" role="button">
            <span class="fas fa-video"></span>
            Video tutoriales
        </a>
    </div>
</div>
