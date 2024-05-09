<div class="page-header"><h1>Firmar XML</h1></div>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea firmar.',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12 o .pfx',
    'check' => 'notempty',
    'attr' => 'accept=".p12,.pfx"',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
    'check' => 'notempty',
]);
echo $f->end('Generar XML firmado');
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo se realiza la firma del XML?</strong>
                <p>Se firmará el primer nodo que sea hijo de la raíz.</p>
                <p>En el siguiente ejemplo el nodo raíz es <code>A</code>, por lo que se firmaría el contenido del primer nodo hijo que es <code>B</code>.</p>
                <code>&lt;A&gt; &lt;B&gt; ... &lt;/B&gt; &lt;/A&gt;</code>
            </div>
        </div>
    </div>
</div>
