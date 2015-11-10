<h1>Firmar XML</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea firmar',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->end('Generar XML del libro');
?>
<p>Se firmará el primer nodo que sea hijo de la raíz, en el siguiente ejemplo la raíz es A y se firmaría el contenido del nodo B:</p>
<pre>
&lt;A&gt;
    &lt;B&gt;...&lt;/B&gt;
&lt;/A&gt;
</pre>
