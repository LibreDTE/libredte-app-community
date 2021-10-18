<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/ver/<?=$DteFolio->dte?>" title="Ver el mantenedor de folios" class="nav-link">
            <i class="fas fa-search"></i> <?=$DteFolio->getTipo()->tipo?>
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Modificar mantenedor <?=$DteFolio->getTipo()->tipo?></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'siguiente',
    'label' => 'Siguiente folio',
    'value' => $DteFolio->siguiente,
    'help' => 'Número de folio que es el siguiente que se debe asignar al documento que se emita',
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'value' => $DteFolio->alerta,
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se tratará de timbrar automáticamente o se notificará al administrador de la empresa',
    'check' => 'notempty integer',
]);
echo $f->end('Modificar mantenedor de folios');
?>
<div class="card-deck mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=93">¿Cómo cambio el folio siguiente?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=250">¿Para qué es la alerta de folios?</a>
            </h5>
        </div>
    </div>
</div>
