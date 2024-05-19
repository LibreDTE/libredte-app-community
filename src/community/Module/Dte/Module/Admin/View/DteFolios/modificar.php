<ul class="nav nav-pills float-end">
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
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'name' => 'siguiente',
    'label' => 'Siguiente folio',
    'value' => $DteFolio->siguiente,
    'help' => 'El folio que se debe asignar al siguiente documento que se emita.',
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'placeholder' => $Emisor->config_sii_timbraje_multiplicador == 5 ? '¿Cuántos folios espera usar mensualmente para este tipo de documento?' : '',
    'value' => $DteFolio->alerta,
    'help' => '¿Quiere saber para qué se utiliza la alerta de folios? Revise <a href="'.$_base.'/dte/admin/dte_folios/agregar#faq_alerta">aquí</a>.',
    'check' => 'notempty integer',
]);
echo $f->end('Modificar mantenedor de folios');
