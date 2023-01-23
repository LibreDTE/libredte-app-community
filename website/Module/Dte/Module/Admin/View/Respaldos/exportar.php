<div class="page-header"><h1>Exportar datos del contribuyente</h1></div>
<p>Aquí podrá exportar los datos que existen en LibreDTE asociados a su empresa. El formato de los datos es idéntico a nuestra base de datos, por lo cual bajará un archivo por cada tabla que tenemos con todos los datos disponibles.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'tablas',
    'label' => 'Tablas',
    'titles' => ['Tabla de la base de datos'],
    'table' => $tablas,
    'mastercheck' => true,
]);
echo $f->end('Generar respaldo');
?>

<div class="row text-center mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=259">¿Cómo generar un respaldo de los datos de mi cuenta en LibreDTE?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
