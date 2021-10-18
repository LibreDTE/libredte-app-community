<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_guias" title="Ir al libro de guías de despacho" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de guías
        </a>
    </li>
</ul>
<div class="page-header"><h1>Facturación masiva de guías de despacho</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id'=>'buscarForm', 'onsubmit'=>'Form.check(\'buscarForm\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => !empty($_POST['desde']) ? $_POST['desde'] : date('Y-m-01'),
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => !empty($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor',
    'check' => 'rut',
    'help' => 'Si se busca por un RUT en específico se podrá ingresar además: orden de compra y/o descuento global',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'con_referencia',
    'label' => '¿Con referencia?',
    'options' => ['Sólo guías sin referencia (sin facturar)', 'Incluir guías que tengan una referencia (podrían estar facturadas)'],
    'help' => 'Por defecto sólo se buscan guías que no tengan una referencia asociada (se asumen sin facturar)',
]);
echo $f->end('Buscar guías a facturar');
// mostrar guías
if (isset($guias)) {
    echo '<hr/>';
    echo '<p>Se encontraron las siguientes guías de despacho sin facturar:</p>';
    foreach ($guias as &$g) {
        $g['total'] = num($g['total']);
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/52/'.$g['folio'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/52/'.$g['folio'].'" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
        $g[] = $acciones;
    }
    echo $f->begin(['id'=>'facturarForm', 'onsubmit'=>'Form.check(\'facturarForm\') && Form.loading(\'Facturando...\')']);
    echo $f->input([
        'type'=>'tablecheck',
        'name'=>'guias',
        'label'=>'Guías',
        'titles'=>['Guía', 'Receptor', 'Fecha', 'Total', 'Acciones'],
        'table'=>$guias,
    ]);
    echo '<hr/>';
    echo $f->input([
        'type' => 'date',
        'name' => 'FchEmis',
        'label' => 'Fecha facturación',
        'value' => !empty($_POST['FchEmis']) ? $_POST['FchEmis'] : date('Y-m-d'),
        'check' => 'notempty date',
        'help' => 'Fecha de emisión de la factura',
    ]);
    echo $f->input([
        'type' => 'date',
        'name' => 'FchVenc',
        'label' => 'Vencimiento',
        'help' => 'Fecha de vencimiento de la factura',
        'check' => 'date',
    ]);
    echo $f->input([
        'name' => 'CdgVendedor',
        'label' => 'Vendedor',
        'help' => 'Código del vendedor del emisor de la factura',
    ]);
    echo $f->input([
        'name' => 'TermPagoGlosa',
        'label' => 'Observación',
        'help' => 'Glosa que describe las condiciones del pago del DTE',
    ]);
    echo $f->input([
        'type' => 'select',
        'name' => 'agrupar',
        'label' => '¿Agrupar?',
        'options' => ['Se agruparán sólo si son más de 10 guías, tanto en referencias como en detalle', 'Se agruparán siempre en el detalle, en referencias irán una a una'],
        'help' => '¿Cómo se deben agrupar las guías al generar la factura?',
    ]);
    echo '<hr/>';
    echo $f->input([
        'name' => 'MedioPago',
        'type'=>'hidden',
        'value'=>'PE',
    ]);
    echo $f->input([
        'name' => 'BcoPago',
        'label' => 'Banco',
        'attr' => 'maxlength="40"',
    ]);
    echo $f->input([
        'name' => 'TpoCtaPago',
        'label' => 'Tipo cuenta',
        'type' => 'select',
        'options' => [''=>'Sin cuenta bancaria', 'CORRIENTE'=>'Cuenta corriente', 'VISTA'=>'Cuenta vista', 'AHORRO'=>'Cuenta de ahorro'],
    ]);
    echo $f->input([
        'name' => 'NumCtaPago',
        'label' => 'Número cuenta',
        'attr' => 'maxlength="20"',
    ]);
    echo '<hr/>';
    if (!empty($_POST['receptor'])) {
        echo $f->input([
            'name' => 'CdgIntRecep',
            'label' => 'Código receptor',
            'help' => 'Código interno asociado al receptor de la factura',
        ]);
        echo $f->input([
            'name' => 'referencia_801',
            'label' => 'Orden de compra',
            'attr' => 'maxlength="18"',
            'help' => 'Número de orden de compra asociado a todas las guías que se están facturando',
        ]);
        echo $f->input([
            'name' => 'referencia_hes',
            'label' => 'HES',
            'attr' => 'maxlength="18"',
            'help' => 'Número de Hoja de Entrada de Servicios',
        ]);
        echo $f->input([
            'name' => 'ValorDR_global',
            'label' => 'Descuento global',
            'check' => 'integer',
            'help' => 'Descuento global neto (sin IVA) que se debe aplicar a la factura de las guías',
        ]);
    }
    echo $f->end('Facturar guías seleccionadas');
}
// mostrar resultado de temporales creados
if (isset($temporales)) {
    echo '<hr/>';
    echo '<p>Se generaron los siguientes documentos temporales:</p>';
    $tabla = [];
    foreach ($temporales as $DteTmp) {
        $acciones = '<a href="'.$_base.'/dte/dte_tmps/cotizacion/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'" title="Descargar cotización" class="btn btn-primary"><i class="fas fa-dollar-sign fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_tmps/pdf/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'" title="Descargar previsualización" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_tmps/ver/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'" title="Ver el documento temporal" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/documentos/generar/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'" title="Generar DTE y enviar al SII" onclick="return Form.confirm(this, \'¿Está seguro de querer generar el DTE?\')" class="btn btn-primary"><i class="far fa-paper-plane fa-fw"></i></a>';
        $tabla[] = [
            $DteTmp->getFolio(),
            $DteTmp->getReceptor()->razon_social,
            num($DteTmp->total),
            $acciones
        ];
    }
    array_unshift($tabla, ['Folio', 'Receptor', 'Total', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, 200]);
    echo $t->generate($tabla);
}
