<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa-solid fa-list me-2"></span>
            Etapas
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <?php foreach ($nav as $link => $info) : ?>
                <a href="<?=$_base?>/certificacion<?=$link?>" class="dropdown-item">
                    <span class="<?=$info['icon']?>"></span>
                    <?=$info['name']?>
                </a>
            <?php endforeach; ?>
        </div>
    </li>
</ul>

<div class="page-header"><h1>Certificación DTE &raquo; Etapa 4: Muestras en PDF</h1></div>

<p>En esta etapa deberás generar los PDF de los documentos que se enviaron al SII en los EnvioDTE de las etapas 1 y 2 del proceso de certificación.</p>
<a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/utilidades/documentos/pdf" role="button">
    Generar muestras de PDF a partir de los XML de EnvioDTE
</a>
