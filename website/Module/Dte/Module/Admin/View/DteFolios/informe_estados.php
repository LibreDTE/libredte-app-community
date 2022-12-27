<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Generar informe de estados en SII de los folios</h1></div>
<p>Aquí podrá solicitar vía correo electrónico a <?=$_Auth->User->email?> un informe con los estados que el SII tiene registrado para los folios (recibidos, anulados o pendientes).</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['Código', 'Documento'],
    'table' => $documentos,
]);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'estados',
    'label' => 'Estados',
    'titles' => ['Estado'],
    'table' => [
        ['recibidos', 'Recibidos en SII'],
        ['anulados', 'Anulados en SII'],
        ['pendientes', 'Pendientes (sin uso o disponibles)'],
    ],
    'mastercheck' => true,
    'display-key' => false,
]);
echo $f->end('Solicitar informe por correo');
