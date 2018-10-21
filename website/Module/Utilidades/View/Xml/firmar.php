<div class="page-header"><h1>Firmar XML</h1></div>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea firmar',
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
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->end('Generar XML firmado');
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Cómo se aplica la firma?</div>
            <div class="card-body">
                <p>Se firmará el primer nodo que sea hijo de la raíz, en el siguiente ejemplo la raíz es A y se firmaría el contenido del nodo B.</p>
                <pre>
                    <code>
&lt;A&gt;
    &lt;B&gt;...&lt;/B&gt;
&lt;/A&gt;
                    </code>
                </pre>
            </div>
        </div>
    </div>
</div>
