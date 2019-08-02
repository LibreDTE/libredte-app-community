<div class="page-header"><h1>Verificar EnvioDTE</h1></div>
<div class="row">
    <div class="col-md-8">
        <p>Esta funcionalidad permite verificar los datos de un archivo XML con el tag EnvioDTE. Se verificará la firma y que los datos del mismo sean válidos</p>
        <p>La validación se conecta al SII, por lo cual si son muchos DTE en el EnvioDTE tomará más tiempo en ser procesada la consulta.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea verificar',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Realizar verificación');
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Cómo se verifica el EnvioDTE?</div>
            <div class="card-body">
                <p>Se verifica la firma del EnvioDTE usando el validador interno de LibreDTE.</p>
                <p>Se verifica la firma de cada DTE usando el validador de firma del SII (servicio web y compara con DTE enviado al SII).</p>
                <p>Se obtiene el estado actual del DTE en el SII, esto indica si fue anulado o si los datos no coinciden.</p>
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
