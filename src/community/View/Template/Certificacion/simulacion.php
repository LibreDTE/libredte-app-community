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

<div class="page-header"><h1>Certificación DTE  &raquo; Etapa 2: Simulación</h1></div>

<p>En esta etapa deberás generar los XML de documentos que emitirás en el futuro con transacciones simuladas basadas en la realidad.</p>
<a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/utilidades/documentos/xml" role="button">
    Generar XML EnvioDTE con datos de simulación
</a>
