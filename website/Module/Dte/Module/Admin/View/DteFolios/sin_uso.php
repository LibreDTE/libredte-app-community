<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/ver/<?=$DteFolio->dte?>" title="Ver el mantenedor de folios" class="nav-link">
            <i class="fas fa-search"></i> <?=$DteFolio->getTipo()->tipo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Folios sin uso de <?=$DteFolio->getTipo()->tipo?></h1></div>
<?php
if ($folios_sin_uso) :
    foreach ($folios_sin_uso as &$folio) {
        $folio = '<a href="#" onclick="__.popup(\''.$_base.'/dte/admin/dte_folios/estado/'.$DteFolio->dte.'/'.$folio.'\', 750, 550); return false" title="Ver el estado del folio '.$folio.' en el SII">'.$folio.'</a>';
    }
?>
<p>Los folios a continuación, que están entre el N° <?=$DteFolio->getPrimerFolio()?> (primer folio emitido en LibreDTE) y el N° <?=$DteFolio->siguiente?> (folio siguiente), se encuentran sin uso en el sistema:</p>
<p><?=implode(', ', $folios_sin_uso)?></p>
<p>Si estos folios no existen en otro sistema de facturación y no los recuperará, debe anularlos.
<div class="row row-cols-3 g-3 mt-4">
    <div class="col">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=103">¿Por qué se saltan folios?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=122">¿Cómo anulo folios en LibreDTE?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=179">¿Cómo anulo folios masivamente?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
<?php else : ?>
<p>No hay CAF con folios sin uso menores al folio siguiente <?=$DteFolio->siguiente?>.</p>
<?php endif; ?>
