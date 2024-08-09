<div class="page-header"><h1>Generar XML DTE y EnvioDTE</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id' => 'generar_xml', 'onsubmit' => 'dte_generar_xml_validar(this)']);
?>

<div class="card-group">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Emisor</h5>
            <?php
            $f->setStyle(false);
            echo $f->input([
                'name' => 'RUTEmisor',
                'label' => 'RUT',
                'placeholder' => 'RUT del emisor: 11222333-4',
                'check' => 'notempty rut',
                'attr' => 'maxlength="12" onblur="Emisor.setDatos(\'generar_xml\')"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'RznSoc',
                'label' => 'Razón social',
                'placeholder' => 'Razón social del emisor: Empresa S.A.',
                'check' => 'notempty',
                'attr' => 'maxlength="100"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'GiroEmis',
                'label' => 'Giro',
                'placeholder' => 'Giro del emisor',
                'check' => 'notempty',
                'attr' => 'maxlength="80"',
            ]); echo '<br/>';
            echo $f->input([
                'type' => 'select',
                'name' => 'Acteco',
                'label' => 'Actividad económica',
                'options' => ['' => 'Actividad económica del emisor'] + $actividades_economicas,
                'check' => 'notempty',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'DirOrigen',
                'label' => 'Dirección',
                'placeholder' => 'Dirección del emisor',
                'check' => 'notempty',
                'attr' => 'maxlength="70"',
            ]); echo '<br/>';
            echo $f->input([
                'type' => 'select',
                'name' => 'CmnaOrigen',
                'label' => 'Comuna',
                'options' => ['' => 'Comuna del emisor'] + $comunas,
                'check' => 'notempty',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'Telefono',
                'label' => 'Teléfono',
                'placeholder' => 'Teléfono del emisor (opcional)',
                'attr' => 'maxlength="20"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'CorreoEmisor',
                'label' => 'Email',
                'placeholder' => 'Email del emisor (opcional)',
                'attr' => 'maxlength="80"',
            ]); echo '<br/>';
            ?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Receptor</h5>
            <?php
            echo $f->input([
                'name' => 'RUTRecep',
                'label' => 'RUT',
                'placeholder' => 'RUT del receptor: 55666777-8',
                'check' => 'notempty rut',
                'attr' => 'maxlength="12" onblur="Receptor.setDatos(\'generar_xml\', \'contribuyente\')"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'RznSocRecep',
                'label' => 'Razón social',
                'placeholder' => 'Razón social del receptor: Cliente S.A.',
                'check' => 'notempty',
                'attr' => 'maxlength="100"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'GiroRecep',
                'label' => 'Giro',
                'placeholder' => 'Giro del receptor',
                'check' => 'notempty',
                'attr' => 'maxlength="80"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'DirRecep',
                'label' => 'Dirección',
                'placeholder' => 'Dirección del receptor',
                'check' => 'notempty',
                'attr' => 'maxlength="70"',
            ]); echo '<br/>';
            echo $f->input([
                'type' => 'select',
                'name' => 'CmnaRecep',
                'label' => 'Comuna',
                'options' => ['' => 'Comuna del receptor'] + $comunas,
                'check' => 'notempty',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'Contacto',
                'label' => 'Teléfono',
                'placeholder' => 'Teléfono del receptor (opcional)',
                'attr' => 'maxlength="20"',
            ]); echo '<br/>';
            echo $f->input([
                'name' => 'CorreoRecep',
                'label' => 'Email',
                'placeholder' => 'Email del receptor (opcional)',
                'attr' => 'maxlength="80"',
            ]); echo '<br/>';
            ?>
        </div>
    </div>
</div>

<div class="card-group">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Documentos</h5>
            <?php
            echo $f->input([
                'type' => 'textarea',
                'name' => 'documentos',
                'value' => $documentos_json,
                'label' => 'Documento(s)',
                'placeholder' => 'Arreglo JSON con los objetos que representan los DTE que se desean generar.',
                'check' => 'notempty',
                'rows' => 15,
            ]).'<br/>';
            ?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Normalizar los datos</h5>
            <?php
            echo $f->input([
                'type' => 'checkbox',
                'name' => 'normalizar_dte',
                'label' => 'Normalizar los datos',
                'checked' => isset($_POST['submit']) ? isset($_POST['normalizar_dte']) : true,
                'help' => 'Si esta opción está seleccionada los datos de los DTE pasarán por un proceso llamado "normalización". Se agregarán y/o calcularán los datos faltantes. Si no se normaliza, se deberán proporcionar todos los datos del DTE (incluyendo totales, cálculo de IVA, montos de descuentos, etc).',
            ]);
            ?>
            <h5 class="card-title mt-4">Folios</h5>
            <?php
            echo $f->input([
                'type' => 'js',
                'name' => 'folios',
                'label' => 'Folios',
                'titles' => ['Archivo XML del CAF a utilizar'],
                'inputs' => [['type' => 'file', 'name' => 'folios', 'attr' => 'accept=".xml"']],
                'help' => 'Los folios a utilizar por cada tipo de documento deben estar contenidos en un único archivo CAF.',
                'check' => 'notempty',
            ]);
            ?>
        </div>
    </div>
</div>
<div class="card-group">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Firma electrónica</h5>
            <?php
            $f->setStyle('horizontal');
            echo $f->input([
                'type' => 'file',
                'name' => 'firma',
                'label' => 'Archivo',
                'help' => 'Certificado digital con extensión .p12 o .pfx',
                'check' => 'notempty',
                'attr' => 'accept=".p12,.pfx"',
            ]);
            echo $f->input([
                'type' => 'password',
                'name' => 'contrasenia',
                'label' => 'Contraseña',
                'check' => 'notempty',
                'help' => 'Contraseña que permite utilizar la firma electrónica.',
            ]);
            ?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Fecha y número de resolución</h5>
            <?php
            echo $f->input([
                'type' => 'date',
                'name' => 'FchResol',
                'label' => 'Fecha',
                'help' => 'Fecha en que fue otorgada la autorización.',
                'check' => 'date',
            ]);
            echo $f->input([
                'name' => 'NroResol',
                'label' => 'Número',
                'value' => 0,
                'help' => 'En ambiente de certificación el valor siempre es: 0.',
                'check' => 'integer',
            ]);
            ?>
        </div>
    </div>
</div>
<?php
echo $f->end('Generar XML');
