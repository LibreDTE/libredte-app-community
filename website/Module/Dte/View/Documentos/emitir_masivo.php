<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/buscar_masivo" title="Buscar documentos masivamente" class="nav-link">
            <i class="me-1 fa fa-search"></i>
            Buscar documentos masivos
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documentos de manera individual" class="nav-link">
            <i class="me-1 fa fa-file-invoice"></i>
            Emitir documento individual
        </a>
    </li>
</ul>
<div class="page-header"><h1>Emitir documentos masivos</h1></div>
<p>Aquí podrá solicitar la emisión masiva de DTE a partir de un archivo CSV (separado por punto y coma, codificado en UTF-8). El archivo debe tener el <a href="<?=$_base?>/dte/archivos/emision_masiva.csv" download="emision_masiva.csv">siguiente formato</a>:</p>
<div class="">
<?php
$col = 1;
new \sowerphp\general\View_Helper_Table([
    ['#', 'Columna', 'Tag XML', 'Nombre', 'Descripción y validaciones', 'Largo máximo', 'Obligatoriedad'],
    [$col++, 'A', 'TipoDTE', 'Tipo DTE', 'Ejemplos de códigos de documento:<br/>- 33: factura afecta<br/>- 34: factura exenta<br/>- 39: boleta afecta<br/>- 41: boleta exenta<br/>- 52: guía de despacho<br/>- 61: nota de crédito<br/>- 110: factura de exportación', '3', 'Obligatorio'],
    [$col++, 'B', 'Folio', 'Folio', 'Número que identifica de manera única dentro del CSV al DTE. Por defecto no se usa en el DTE final, sólo si está configurado el folio manual en la empresa. Normalmente puede partir en 1 y ser un correlativo.', '10', 'Obligatorio'],
    [$col++, 'C', 'FchEmis', 'Fecha emisión', 'En formato AAAA-MM-DD', '10', 'Opcional'],
    [$col++, 'D', 'FchVenc', 'Fecha vencimiento', 'En formato AAAA-MM-DD', '10', 'Opcional'],
    [$col++, 'E', 'RUTRecep', 'RUT Receptor', 'Sin puntos, con guión y dígito verificador', '10', 'Obligatorio'],
    [$col++, 'F', 'RznSocRecep', 'Razón social receptor', '', '100', 'Obligatorio, excepto en boletas no nominativas'],
    [$col++, 'G', 'GiroRecep', 'Giro del receptor', '', '40', 'Obligatorio, excepto en boletas'],
    [$col++, 'H', 'Telefono', 'Teléfono del receptor', 'Formato recomendado: +56 9 55443322', '20', 'Opcional'],
    [$col++, 'I', 'CorreoRecep', 'Email del receptor', '', '80', 'Obligatorio si se desea enviar a un correo específico y no usar los de LibreDTE'],
    [$col++, 'J', 'DirRecep', 'Dirección del receptor', '', '70', 'Obligatorio, excepto en boletas'],
    [$col++, 'K', 'CmnaRecep', 'Comuna del receptor', 'Sin abreviaciones, tal como se ve en LibreDTE', '20', 'Obligatorio, excepto en boletas'],
    [$col++, 'L', 'VlrCodigo', 'Código del ítem', '', '35', 'Opcional'],
    [$col++, 'M', 'IndExe', 'Indicador de facturación o exención', 'Si el ítem es exento se debe indicar un 1 (uno), si es no facturable se debe indicar un 2 (dos)', '1', 'Opcional'],
    [$col++, 'N', 'NmbItem', 'Nombre del ítem', '', '80', 'Obligatorio'],
    [$col++, 'O', 'DscItem', 'Descripción del ítem', '', '1000', 'Opcional'],
    [$col++, 'P', 'QtyItem', 'Cantidad del ítem', 'No usar coma, el separador decimal es punto.', '18', 'Obligatorio'],
    [$col++, 'Q', 'UnmdItem', 'Unidad del ítem', '', '4', 'Opcional'],
    [$col++, 'R', 'PrcItem', 'Precio del ítem', 'Monto bruto si es boleta (con IVA), cualquier otro documento monto neto (sin IVA). No usar coma, el separador decimal es punto.', '18', 'Obligatorio'],
    [$col++, 'S', 'Descuento', 'Descuento del ítem', 'Puede ser 50% para indicar descuento en porcentaje o un monto como 1000 para indicar descuento en cantidad. No usar coma, el separador decimal es punto.', '18', 'Opcional'],
    [$col++, 'T', 'TermPagoGlosa', 'Observación del documento', '', '100', 'Opcional'],
    [$col++, 'U', 'PeriodoDesde', 'Fecha periodo desde', 'En formato AAAA-MM-DD', '10', 'Opcional'],
    [$col++, 'V', 'PeriodoHasta', 'Fecha periodo hasta', 'En formato AAAA-MM-DD', '10', 'Opcional'],
    [$col++, 'W', 'Patente', 'Patente vehículo despacho', '', '8', 'Opcional'],
    [$col++, 'X', 'RUTTrans', 'RUT transportista despacho', 'Sin puntos, con guión y dígito verificador', '10', 'Opcional'],
    [$col++, 'Y', 'RUTChofer', 'RUT chofer vehículo despacho', 'Sin puntos, con guión y dígito verificador', '10', 'Obligatorio sólo si va el nombre del chofer'],
    [$col++, 'Z', 'NombreChofer', 'Nombre chofer vehículo despacho', '', '30', 'Obligatorio sólo si va el RUT del chofer'],
    [$col++, 'AA', 'DirDest', 'Dirección despacho', '', '70', 'Opcional'],
    [$col++, 'AB', 'CmnaDest', 'Comuna despacho', 'Sin abreviaciones, tal como se ve en LibreDTE', '20', 'Opcional'],
    [$col++, 'AC', 'TpoDocRef', 'Tipo documento referencia', 'Ejemplos de códigos de documento:<br/>- 33: factura afecta<br/>- 34: factura exenta<br/>- 39: boleta afecta<br/>- 41: boleta exenta<br/>- 52: guía de despacho<br/>- 801: orden de compra<br/>- HES: hoja de entrada de servicios', '3', 'Opcional'],
    [$col++, 'AD', 'FolioRef', 'Folio documento referencia', '', '18', 'Obligatorio si hay referencia'],
    [$col++, 'AE', 'FchRef', 'Fecha referencia', 'En formato AAAA-MM-DD', '10', 'Obligatorio si hay referencia'],
    [$col++, 'AF', 'CodRef', 'Código de referencia', 'Códigos: 1 para anula documento, 3 para corrige montos, 2 para corrige texto', '1', 'Obligatorio en nota de crédito y nota de débito'],
    [$col++, 'AG', 'RazonRef', 'Motivo de la referencia', 'Ej: Devolución de productos', '90', 'Obligatorio en nota de crédito y nota de débito'],
    [$col++, 'AH', 'Moneda', 'Moneda para documentos de exportación', 'Por defecto: USD', '3', 'Opcional'],
    [$col++, 'AI', 'NumId', 'ID extranjero', '', '20', 'Opcional'],
    [$col++, 'AJ', 'DscGlobal', 'Descuento global', 'Puede ser 50% para indicar descuento global en porcentaje o un monto como 1000 para indicar descuento global en cantidad. No usar coma, el separador decimal es punto.', '18', 'Opcional'],
    [$col++, 'AK', '-', 'Nombre del PDF', 'Permite especificar el nombre del PDF a descargar. Se pueden usar las variables: {rut}, {dv}, {dte} y {folio}', '100', 'Opcional'],
    [$col++, 'AL', 'FmaPago', 'Forma de Pago', 'Códigos: 1 para contado, 2 para crédito y 3 para sin costo (entrega gratuita)', '1', 'Opcional'],
]);
?>
<p class="mt-3">Si el documento tiene más de un item o referencia, se agrega una nueva fila donde sólo van las columnas correspondientes al item o la referencia, y las demás vacías.</p>
<p>El archivo subido se procesará de manera asíncrona y se notificará vía correo electrónico a <?=$_Auth->User->email?> cuando el proceso esté completo. El correo incluirá el mismo archivo CSV que se subió a la plataforma con 2 columnas nuevas que incluirán el código del resultado de la operación para ese documento y la glosa asociada a dicho estado. El significado macro de cada código de estado es:</p>
<?php
new \sowerphp\general\View_Helper_Table([
    ['Código de resultado', 'Descripción macro del resultado'],
    [1, 'Error en el formato del archivo (faltan campos o tienen formato incorrecto)'],
    [2, 'No autorizado a emitir el tipo de DTE solicitado'],
    [3, 'Solicitó enviar por correo y falta el correo del receptor'],
    [4, 'No fue posible emitir el DTE temporal'],
    [5, 'No fue posible generar el DTE real a partir del DTE temporal emitido'],
    [6, 'No fue posible enviar por correo el DTE generado (ya sea temporal o real)'],
    ['', 'DTE generado (ya sea temporal o real) y enviado al receptor por correo (si así se solicitó)'],
]);
?>
<p>Podrá encontrar el detalle de cada estado en caso de error en la glosa descriptiva en el archivo CSV de resultados.</p>
<p><strong>Importante</strong>: si el código de resultado es 1, 2 o 3 (validaciones de formato) no se generará ningún documento, independientemente que otras filas pasen las validaciones de formato.</p>
<hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Documentos',
    'check' => 'notempty',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con los documentos que se deben emitir masivamente. <a href="'.$_base.'/dte/archivos/emision_masiva.csv" download="emision_masiva.csv">Ejemplo formato</a>',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte_emitido',
    'label' => '¿DTE real?',
    'options' => ['No, sólo generar DTE temporal (cotización)', 'Si, generar DTE real (documento emitido)'],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'email',
    'label' => '¿Enviar email?',
    'options' => ['No enviar email al receptor', 'Si, enviar email al receptor con el documento'],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'pdf',
    'label' => '¿Generar PDF?',
    'options' => ['No generar PDF', 'Si, generar todos los PDF y enviar enlace para descarga en correo de resultado'],
]);
echo $f->end('Emitir DTE masivamente');
?>
</div>
