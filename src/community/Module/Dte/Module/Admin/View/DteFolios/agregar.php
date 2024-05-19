<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Agregar tipo de documento</h1></div>
<p>Aquí podrá agregar un mantenedor de folios para un nuevo tipo de documento. En el paso siguiente se le pedirá que suba el primer archio XML del CAF (folios).</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de documento',
    'options' => ['' => 'Seleccione el tipo de documento'] + $dte_tipos,
    'check' => 'notempty',
    'help' => '¿Necesitas activar un documento que no está en la lista? <a href="'.$_base.'/dte/contribuyentes/modificar#facturacion:documentos_disponibles">Hazlo en la configuración del contribuyente</a>.',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta de folios',
    'placeholder' => $Emisor->config_sii_timbraje_multiplicador == 5 ? '¿Cuántos folios espera usar mensualmente para este tipo de documento?' : '',
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se tratará de timbrar automáticamente o se notificará al administrador de la empresa.',
    'check' => 'notempty integer',
]);
echo $f->end('Crear mantenedor de folios e ir al paso siguiente');
?>
<div class="card mb-4" id="faq_alerta">
    <div class="card-body">
        <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
        <strong>¿Para qué se utiliza la alerta de folios?</strong><br/>
        Se usa para generar una acción, timbraje o alerta de folios, cuando la cantidad de folios disponibles llega al valor de la alerta. Esto permite realizar timbraje automático o bien informar al administrador por correo electrónico que se alcanzó la alerta y quedan pocos folios disponibles en LibreDTE.<br/><br/>
        Si se tiene configurado el timbraje automático, LibreDTE tratará de timbrar de manera automática según la siguiente fórmula:<br/><br/>
        <code>folios a timbrar = alerta x multiplicador</code><br/><br/>
        El <code>multiplicador</code> se define en <a href="<?=$_base?>/dte/contribuyentes/modificar#facturacion">Configuración &raquo; Facturación</a> y los valores recomendados son:<br/><br/>
        <ul>
            <li><code>alerta</code>: el promedio mensual de folios que se esperan usar para el tipo de documento.</li>
            <li><code>multiplicador</code>: 5 (valor por defecto).
        </ul>
        De esta forma, en cada timbraje, se timbrarán folios para 5 meses. Con estos valores se disminuye la probabilidad de que los folios venzan a los 6 meses por no uso.<br/><br/>
        <strong>Importante</strong>: si el timbraje automático falla se enviará un correo electrónico a los administradores de la empresa para que carguen folios manualmente.
    </div>
</div>
