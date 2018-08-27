<div class="page-header"><h1>Verificar EnvioDTE</h1></div>
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

if (!empty($documentos)) {

    if (!$EnvioDTE->schemaValidate()) {
        echo '<h2>Validación de esquema</h2>',"\n";
        debug(implode("\n\n", \sasco\LibreDTE\Log::readAll()));
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
        foreach ($errores as $e)
            debug($e);
    }

}
