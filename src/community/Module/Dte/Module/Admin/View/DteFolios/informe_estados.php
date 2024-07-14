<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Generar informe de estados de los folios en SII </h1></div>
<p>Aquí podrá solicitar vía correo electrónico, que será enviado a <?=$user->email?>, un informe con los estados que el SII tiene registrado para los folios (recibidos, anulados o pendientes).</p>
<div class="row">
    <div class="col-md-8">
        <?php
        $f = new \sowerphp\general\View_Helper_Form();
        $f->setStyle(false);
        echo $f->begin(['onsubmit' => 'Form.check()']);
        echo $f->input([
            'type' => 'tablecheck',
            'name' => 'documentos',
            'label' => 'Documentos',
            'titles' => ['Código', 'Documento tributario que se desea conocer su estado'],
            'table' => $documentos,
        ]);
        echo $f->input([
            'type' => 'tablecheck',
            'name' => 'estados',
            'label' => 'Estados',
            'titles' => ['Estados por los que se desea consultar'],
            'table' => [
                ['recibidos', 'Recibidos en SII'],
                ['anulados', 'Anulados en SII'],
                ['pendientes', 'Pendientes (sin uso o disponibles)'],
            ],
            'mastercheck' => true,
            'display-key' => false,
        ]);
        echo $f->end('Solicitar informe con estado de folios');
        ?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Qué entrega este informe de folios?</strong><br/>
                Este informe obtiene los estados de los folios desde el SII. Entregando 3 estados para cada folio:<br/><br/>
                <ol>
                    <li>Folios <strong>recibidos</strong> por el SII, estos son DTE que su XML fue enviado al SII y el SII lo acepto, ya sea con o sin reparos.</li>
                    <li>Folios <strong>anulados</strong> en el SII, son folios que no se usaron ni se pueden usar. No son los DTE anulados con notas de crédito o débito, ese es otro tipo de anulación (de documento). Esta anulación se refiere a la del folio como número.</li>
                    <li>Folios <strong>pendientes</strong> de ser recibidos por el SII o pendientes de anular. Los primeros son folios que están vigentes y se espera que se envíen en el futuro al SII. Los segundos son folios que se saltaron o vencieron y no se usarán y se deben anular.</li>
                </ol>
                <strong>Importante</strong>: para el SII los folios pendientes están disponibles, ya sea de enviar o de anular. Por eso es muy importante anular en el SII los folios saltados o vencidos, ya que interfieren con los timbrajes nuevos que se quieran solicitar al SII.
            </div>
        </div>
        <div class="alert alert-warning text-center" role="alert">
            Esta funcionalidad se debe considerar experimental.<br/>
            <span class="small">Siempre corroborar los estados de folios en la web del SII.</span>
        </div>
    </div>
</div>
