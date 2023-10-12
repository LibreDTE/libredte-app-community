<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/ver/<?=$DteFolio->dte?>" title="Ver el mantenedor de folios" class="nav-link">
            <i class="fas fa-search"></i> <?=$DteFolio->getTipo()->tipo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Folios sin uso de <?=$DteFolio->getTipo()->tipo?></h1></div>
<?php if ($folios_sin_uso) : ?>
    <?php
    foreach ($folios_sin_uso as &$folio) {
        $folio = '<a href="#" onclick="__.popup(\''.$_base.'/dte/admin/dte_folios/estado/'.$DteFolio->dte.'/'.$folio.'\', 750, 550); return false" title="Ver el estado del folio '.$folio.' en el SII">'.$folio.'</a>';
    }
    ?>
    <p>Los folios a continuación, que están entre el N° <?=$DteFolio->getPrimerFolio()?> (primer folio emitido en LibreDTE) y el N° <?=$DteFolio->siguiente?> (folio siguiente), se encuentran sin uso en el sistema:</p>
    <p><?=implode(', ', $folios_sin_uso)?></p>
    <p>Si estos folios no existen en otro sistema de facturación y no los recuperará, debe anularlos en el SII.
<?php else : ?>
    <p>No hay CAF con folios sin uso menores al folio siguiente <?=$DteFolio->siguiente?>.</p>
<?php endif; ?>
