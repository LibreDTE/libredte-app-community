<div class="page-header"><h1>Exportar datos del contribuyente</h1></div>
<p>Aquí podrá exportar los datos que existen en LibreDTE asociados a su empresa. El formato de los datos es idéntico a nuestra base de datos, por lo cual bajará un archivo CSV por cada tabla que existe en LibreDTE con todos los datos disponibles.</p>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Es posible elegir el rango de fechas a descargar?</strong><br/>
                No es posible. Esta opción realiza una descarga de todos los datos.
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cuánto demora la descarga del respaldo?</strong><br/>
                Dependerá de la cantidad de datos que tenga, pueden ser minutos hasta varias horas.
            </div>
        </div>
    </div>
</div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
$f->setStyle(false);
echo $f->begin(['onsubit' => 'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'tablas',
    'label' => 'Tablas',
    'titles' => ['Tabla de la base de datos'],
    'table' => $tablas,
    'mastercheck' => true,
]);
echo $f->end('Generar respaldo');
