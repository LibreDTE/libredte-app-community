<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Solicitar folios desde LibreDTE</h1></div>
<p>Aquí podrá solicitar un archivo de folios (CAF) al SII y cargarlo automáticamente a LibreDTE.</p>
<?php if (!$Emisor->config_sii_timbraje_automatico) : ?>
    <div class="alert alert-warning mb-4 text-center">¿Ha considerado activar el timbraje automático de folios? Revise la <a href="<?=$_base?>/dte/contribuyentes/modificar#facturacion" class="alert-link">configuración de la empresa</a>.</div>
<?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <?php
        $f = new \sowerphp\general\View_Helper_Form();
        echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Solicitando CAF al SII...\')']);
        echo $f->input([
            'type' => 'select',
            'name' => 'dte',
            'label' => 'Tipo de documento',
            'options' => [''=>'Seleccione un tipo de documento'] + $dte_tipos,
            'value' => $dte,
            'check' => 'notempty',
        ]);
        echo $f->input([
            'name' => 'cantidad',
            'label' => 'Cantidad',
            'help' => 'Cantidad de folios máximo que se tratarán de solicitar (se podrían obtener menos si no hay suficientes autorizados por el SII).',
            'check' => 'notempty integer',
        ]);
        echo $f->end('Solicitar folios al SII y cargar en LibreDTE');
        ?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Primer timbraje del tipo de documento?</strong><br/>
                Si no ha timbrado folios para este tipo de documento en el SII, o sea, es el primer CAF a generar, debe hacerlo en el sitio del SII y luego <a href="<?=$_base?>/dte/admin/dte_folios/subir_caf">cargar el archivo del CAF</a>.<br/><br/>
                Timbrajes futuros se pueden realizar mediante LibreDTE.
            </div>
        </div>
    </div>
</div>
