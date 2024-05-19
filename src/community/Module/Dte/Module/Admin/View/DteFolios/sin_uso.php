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
    <p>Si estos folios no existen en otro sistema de facturación y no los recuperará, debe anularlos en el SII.</p>
    <div class="row row-cols-1 row-cols-xl-2">
        <div class="col">
            <div class="card mb-4">
                <div class="card-body" id="faq_anular_folios">
                    <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                    <strong>¿Qué debo hacer con estos folios sin uso?</strong><br/>
                    Los folios sin uso (saltados o vencidos) deben ser anulados en el <a href="https://www4<?=$Emisor->enCertificacion()?'c':''?>.sii.cl/anulacionMsvDteInternet/" target="_blank">sitio web del SII</a>. Si no los anula, esto afectará futuras solicitudes de nuevos folios.
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-4">
                <div class="card-body" id="faq_anular_folios">
                    <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                    <strong>Ya anulé los folios en SII, ¿por qué aún los veo acá?</strong><br/>
                    LibreDTE no sincroniza con SII los folios anulados. Esto no es un problema, ya que son folios que no se usarán. Puede eliminar el CAF de LibreDTE y dejará de ver el folio acá.
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <p>No hay CAF con folios sin uso menores al folio siguiente <?=$DteFolio->siguiente?> que se encuentren sin uso.</p>
<?php endif; ?>
