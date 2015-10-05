¿Qué es el proceso de normalización de LibreDTE?
================================================

El proceso de normalización permite disminuir la cantidad de datos que se deben
proporcionar al momento de construir un DTE. Por ejemplo: el IVA de una factura
es un valor que es calculable a partir del monto neto y la tasa de IVA, el
usuario (o desarrollador) puede no proporcionar el monto del IVA y será
calculado para aquellos casos que se normalicen.

Específicamente los procesos de normalización realizan:

- Se agregan números de descuentos/recargos y referencias
- Se normaliza el detalle: números de item y descuentos de item
- Se aplican descuentos y recargos globales (calculando montos)
- Se calcula el IVA y/o montos totales


Los documentos tributarios electrónicos que actualmente están soportados y se
normalizan son:

- Factura electrónica
- Factura exenta electrónica
- Nota de débito electrónica
- Nota de crédito electrónica

Lo anterior no implica que no pueda [generar otros DTE](otros_dte).

Si eres desarrollador
---------------------

Los métodos que realizan normalizaciones son los que en su nombre inician con
*normalizar* y están en la clase
[\sasco\LibreDTE\Sii\Dte](https://github.com/sascocl/LibreDTE/blob/master/lib/Sii/Dte.php).

Si deseas forzar el no normalizado de los DTE, especialmente si estás generando
un DTE no soportado oficinalmente, deberás crear el objeto del DTE indicando un
segundo parámetro para evitar el proceso de normalización:

	$Dte = new \sasco\LibreDTE\Dte($datos_dte, false);
