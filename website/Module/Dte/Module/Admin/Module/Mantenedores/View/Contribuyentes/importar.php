<div class="page-header"><h1>Importar contribuyentes</h1></div>
<div class="row">
    <div class="col-md-8">
        <p>Aquí se podrán importar datos de contribuyentes usando un archivo CSV. El orden de las columnas es:</p>
        <ul>
            <li>
                RUT: alguno de los siguientes formatos (obligatorio):
                <ul>
                    <li>Sin puntos y sin dígito verificador. Ejemplo: 76192083</li>
                    <li>Sin puntos y con dígito verificador. Ejemplo: 76192083-9</li>
                    <li>Con puntos y con dígito verificador. Ejemplo: 76.192.083-9</li>
                </ul>
            </li>
            <li>Razón social: nombre según el registro en el SII del contribuyente (obligatorio si es RUT nuevo)</li>
            <li>Giro: giro principal del contribuyente (opcional)</li>
            <li>Dirección: dirección principal del contribuyente (opcional)</li>
            <li>Comuna: código de comuna (iniciando con 0 si corresponde) o bien nombre comuna principal del contribuyente</li>
            <li>Correo electrónico: correo electrónico principal del contribuyente, uno sólo (opcional)</li>
            <li>Teléfono: teléfono principal del contribuyente, uno sólo (opcional). Ejemplos: celular +56 9 88776655 / Santiago +56 2 22334455 / Santa Cruz +56 72 2821122</li>
            <li>Código de actividad económica: código de actividad económica principal según el SII (opcional)</li>
        </ul>
        <hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo',
    'check' => 'notempty',
    'help' => 'Archivo con datos de los contribuyentes en formato CSV (separado por punto y coma, codificado en UTF-8)',
]);
echo $f->end('Importar datos de contribuyentes');
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Qué datos puedo cargar?</div>
            <div class="card-body">
                <p>Sólo se pueden cargar datos de contribuyentes que no estén registrados por un usuario en el sistema. Si se incluye un contribuyente ya registrado, se omitirá en la actualización.</p>
                <p>Sólo se actualizarán datos que no estén previamente asignados al contribuyente. Por ejemplo, si un contribuyente ya tiene un correo electrónico, usando esta opción no podrá actualizar dicho correo. Sólo podrá agregar el correo si el contribuyente no tiene uno.</p>
            </div>
        </div>
    </div>
</div>
