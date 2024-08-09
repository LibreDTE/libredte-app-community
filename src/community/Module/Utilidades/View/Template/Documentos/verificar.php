<div class="page-header"><h1>Verificar EnvioDTE</h1></div>
<div class="row">
    <div class="col-md-8">
        <div class="alert alert-warning" role="alert">
            <i class="fa-solid fa-exclamation-triangle fa-fw"></i>
            Esta  validación se conecta al SII, por lo cual si son muchos DTE demorará en ser procesada la consulta.
        </div>
        <p></p>
        <?php
        $f = new \sowerphp\general\View_Helper_Form();
        echo $f->begin(['onsubmit' => 'Form.check()']);
        echo $f->input([
            'type' => 'file',
            'name' => 'xml',
            'label' => 'Archivo XML',
            'check' => 'notempty',
            'help' => 'Archivo XML con el EnvioDTE que se desea verificar.',
            'attr' => 'accept=".xml"',
        ]);
        echo $f->input([
            'type' => 'file',
            'name' => 'firma',
            'label' => 'Firma electrónica',
            'help' => 'Certificado digital con extensión .p12 o .pfx',
            'check' => 'notempty',
            'attr' => 'accept=".p12,.pfx"',
        ]);
        echo $f->input([
            'type' => 'password',
            'name' => 'contrasenia',
            'label' => 'Contraseña firma',
            'check' => 'notempty',
            'help' => 'Contraseña que permite utilizar la firma electrónica.',
        ]);
        echo $f->end('Realizar verificación de los DTE con el SII');
        ?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo se verifica el EnvioDTE?</strong>
                <p>Primero, se verifica la firma del EnvioDTE usando el validador interno de LibreDTE.</p>
                <p>Segundo, se verifica la firma de cada DTE usando el servicio web de validación de firma del SII, comparando el DTE del archivo con el DTE que fue enviado al SII.</p>
                <p>Finalmente, se obtiene el estado actual del DTE en el SII, esto indica si fue anulado o si algún dato no coincide.</p>
            </div>
        </div>
    </div>
</div>


<?php
if (!empty($documentos)) {

    if (!$EnvioDTE->schemaValidate()) {
        echo '<h2>Validación de esquema</h2>',"\n";
        echo '<pre>',"\n";
        print_r(implode("\n\n", \sasco\LibreDTE\Log::readAll()));
        echo '</pre>',"\n";
    }

    // cabecera del envío
    echo '<h2>Cabecera del envío</h2>',"\n";
    new \sowerphp\general\View_Helper_Table([
        ['Emisor', 'Envía', 'Receptor', 'Fecha resolución', 'N° resolución', 'Fecha y hora firma', 'Firma'],
        [
            $EnvioDTE->getCaratula()['RutEmisor'],
            $EnvioDTE->getCaratula()['RutEnvia'],
            $EnvioDTE->getCaratula()['RutReceptor'],
            $EnvioDTE->getCaratula()['FchResol'],
            $EnvioDTE->getCaratula()['NroResol'],
            str_replace('T', ' ', $EnvioDTE->getCaratula()['TmstFirmaEnv']),
            $EnvioDTE->checkFirma() ? 'Ok' : ':-(',
        ]
    ]);

    // resultados de los documentos
    echo '<h2>Resultado documentos</h2>',"\n";
    array_unshift($documentos, ['DTE', 'Folio', 'Fecha', 'Total', 'Firma', 'Resultado verificación']);
    new \sowerphp\general\View_Helper_Table($documentos, 'verificacion_enviodte_'.date('U'), true);

    // mostrar errores
    if ($errores) {
        echo '<h2>Errores ocurridos durante las verificaciones</h2>',"\n";
        foreach ($errores as $e) {
            echo '<pre>',"\n";
            print_r($e);
            echo '</pre>',"\n";

        }
    }

}
