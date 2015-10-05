¿Cómo realizo la certificación con LibreDTE?
============================================

Se asume que el usuario administrador que realizará el proceso de certificación:

- Dispone de firma digital en formato .p12
- Ha descargado el [set de pruebas](https://maullin2.sii.cl/cvc_cgi/dte/pe_generar)
- Ha descargado los [folios necesarios](https://maullin2.sii.cl/cvc_cgi/dte/of_solicita_folios)

Se describirá el proceso de certificación para los siguientes documentos
electrónicos:

- Factura electrónica (33)
- Factura exenta electrónica (34)
- Nota de débito (56)
- Nota de crédito (61)

En el menú de certificación encontrará las 4 opciones que permitirán certificar
cada una de las 4 etapas del proceso:

![01_menu_certificacion]({_base}/img/certificacion/01_menu_certificacion.png)

Etapa 1: Set de pruebas
-----------------------

El SII generará un archivo llamado *SIISetDePruebas761920839.txt*, donde
*761920839* corresponde al RUT de la empresa sin dígito verificador. Este
archivo contiene todos los casos del set de pruebas para los documentos que se
están certificando, incluyendo aquellos casos para el libro de compras. De este
archivo se debe extraer el
[set básico](https://github.com/sascocl/LibreDTE/blob/master/examples/set_pruebas/001-basico.txt)
y el [set de factura exenta](https://github.com/sascocl/LibreDTE/blob/master/examples/set_pruebas/004-factura_exenta.txt).
Estos archivos deben iniciar con CASO XXXX-Y y terminar con los datos del
último caso, no debe contener líneas extras el archivo.

### Emisión de DTE set básico

1. Ir a la pestaña de *Emisión de DTE* en la etapa 1 y subir el archivo con los
casos del set de pruebas básico junto a los datos de los 3 folios solicitados.
Para esto último se deberá indicar el código del tipo de DTE y el primer folio
que se tiene disponible en el CAF. Se deberán usar folios del CAF que tengan
suficientes para todos los documentos que se emitirán, revisar los casos del set
de pruebas para verificar cuantos documentos de cada tipo se están solicitando:

	![02_emision_dte]({_base}/img/certificacion/02_emision_dte.png)

2. Click en siguiente para pasar a la página de generación de DTE. En esta
página se deberán ingresar los datos del emisor (con número de resolución 0) y
los datos del receptor:

	![03_generar_dte]({_base}/img/certificacion/03_generar_dte.png)

3. Estará creado el código JSON con los DTE, se deberá modificar la razón de las
referencias que modifiquen texto o giro, indicando: "Donde dice X debe decir Y"
(reemplazando X e Y con lo que era antes y el nuevo texto). Además, se deberán
subir los 3 CAF requeridos, la firma y la contraseña.

4. Generar el XML y guardar en el computador:

	![04_generar_dte_xml]({_base}/img/certificacion/04_generar_dte_xml.png)

5. Ir a la [página de carga del ambiente de certificación](https://maullin2.sii.cl/cgi_dte/UPL/DTEauth?1)
y subir el XML generado.

6. Al correo de contacto con el SII llegará un mensaje informando el resultado
del envío, se debe haber recibido todo aceptado (sin reparos ni rechazos):

        Resultado de Validacion de Envio de DTE
        =======================================

        Rut de Empresa Emisora   : 76192083-9
        Rut que Realizo el Envio : 11222333-4
        Identificador de Envio   :   34287XXX
        Fecha de Recepcion       : 18/09/2015 02:02:39
        Estado del Envio         : EPR - Envio Procesado

        Estadisticas del Envio
        ======================
        Tipo DTE  Informados  Rechazos  Reparos  Aceptados
        --------------------------------------------------
           33              4         0        0          4
           56              1         0        0          1
           61              3         0        0          3

6. Finalmente el identificador del envío *34287XXX* se utiliza para
[declarar el avance](https://maullin2.sii.cl/cvc_cgi/dte/pe_avance1) en el SII.
Esta acción genera un nuevo email con el resultado:

        Resultado de Revision del Set de Prueba de Certificacion
        ========================================================

        Rut de Empresa Emisora : 76192083-9
        Identificador del Set  :     423775
        Tipo de Set            : SET BASICO - Version 1
        Identificador de Envio :   34287XXX
        Estado del Set         : SOK - SET DE PRUEBA CORRECTO

Listo! :-)

### Emisión de DTE set factura exenta

Se repite el proceso del set básico, pero con los casos del set de factura
exenta. Los resultados de los correos debiesen ser:

1. EnvioDTE:

        Resultado de Validacion de Envio de DTE
        =======================================

        Rut de Empresa Emisora   : 76192083-9
        Rut que Realizo el Envio : 11222333-4
        Identificador de Envio   :   34288XXX
        Fecha de Recepcion       : 18/09/2015 02:17:39
        Estado del Envio         : EPR - Envio Procesado

        Estadisticas del Envio
        ======================
        Tipo DTE  Informados  Rechazos  Reparos  Aceptados
        --------------------------------------------------
           34              3         0        0          3
           56              2         0        0          2
           61              3         0        0          3


2. Declaración avance:

        Resultado de Revision del Set de Prueba de Certificacion
        ========================================================

        Rut de Empresa Emisora : 76192083-9
        Identificador del Set  :     423776
        Tipo de Set            : SET FACTURA EXENTA - Version 2
        Identificador de Envio :   34288XXX
        Estado del Set         : SOK - SET DE PRUEBA CORRECTO

Listo! :-)

### Generar libro de ventas

1. El libro de ventas se generará a partir del XML de EnvioDTE en la pestaña
*Libro de Ventas* en la etapa 1 de certificación. El XML debe ser el del set de
pruebas básico, además se debe generar desde 1980-01 (y sólo si hay fallos se
pasa a otro mes):

	![05_libro_ventas]({_base}/img/certificacion/05_libro_ventas.png)

2. Una vez generado el XML se sube al SII (mismo modo emisión DTE).

3. El correo que se debe recibir como respuesta al envío del libro es:

        Resultado de Validacion de Envio de Libro Tributario
        ====================================================

        Identificador de Envio      :   34288XXX
        Rut de Empresa Emisora      : 76192083-9
        Rut que Realizo el Envio    : 11222333-4
        Fecha de Recepcion          : 18/09/2015 02:32:04
        Estado del Envio de Libro   : LOK - Envio de Libro Aceptado - Cuadrado
        Tipo de Segmento            : TOTAL
        Numero de Segmento          :   0

        Situacion del Libro Tributario Asociado
        =======================================

        Tipo de Libro               : ESPECIAL
        Tipo de Operacion           : VENTA
        Periodo Tributario          : 1980-01
        Estado del Libro Tributario : LTC - Libro Cerrado - Informacion Cuadrada

4. Declaración de avance:

        Resultado de Revision del Set de Prueba de Certificacion
        ========================================================

        Rut de Empresa Emisora : 76192083-9
        Identificador del Set  :     423777
        Tipo de Set            : SET LIBRO DE VENTAS - Version 1
        Identificador de Envio :   34288XXX
        Estado del Set         : SOK - SET DE PRUEBA CORRECTO

Listo! :-)

### Generar libro de compras

Para generar el libro de compras se utilizará la opción de
[generación de libro de compras a partir de un archivo CSV]({_base}/utilidades/generar_libro).
Por lo anterior se deberán pasar los casos del libro de compras del set de
pruebas a un archivo CSV, .

1. Crear [archivo CSV con detalles de las compras]({_base}/ejemplos/libro_compras.csv)

2. Generar XML del libro de compras con el archivo CSV y la opción
[generación de libro de compras a partir de un archivo CSV]({_base}/utilidades/generar_libro).

	![06_libro_compras]({_base}/img/certificacion/06_libro_compras.png)

3. Subir el XML del libro de compras al SII igual que con el de ventas.

4. Correo resultado envío:

        Resultado de Validacion de Envio de Libro Tributario
        ====================================================

        Identificador de Envio      :   34288XXX
        Rut de Empresa Emisora      : 76192083-9
        Rut que Realizo el Envio    : 11222333-4
        Fecha de Recepcion          : 18/09/2015 02:52:07
        Estado del Envio de Libro   : LOK - Envio de Libro Aceptado - Cuadrado
        Tipo de Segmento            : TOTAL
        Numero de Segmento          :   0

        Situacion del Libro Tributario Asociado
        =======================================

        Tipo de Libro               : ESPECIAL
        Tipo de Operacion           : COMPRA
        Periodo Tributario          : 2000-02
        Estado del Libro Tributario : LTC - Libro Cerrado - Informacion Cuadrada

5. Declaración de avance:

        Resultado de Revision del Set de Prueba de Certificacion
        ========================================================

        Rut de Empresa Emisora : 76192083-9
        Identificador del Set  :     423778
        Tipo de Set            : LIBRO DE COMPRAS - Version 1
        Identificador de Envio :   34288XXX
        Estado del Set         : SOK - SET DE PRUEBA CORRECTO

Listo! :-)

Etapa 2: Simulación
-------------------

Aquí se debe repetir el proceso de la generación de DTE a través de la web, para
facilitar la creación de los documentos hay una plantilla con 21 documentos
(pero de todas formas se pueden enviar menos, mínimo 10). Una vez generado el
XML se sube a SII y se declara el avance (igual que con set de pruebas).

**IMPORTANTE**: en esta etapa se deben usar datos de la operación real del
contribuyente.

- Resultado envío:

        Resultado de Validacion de Envio de DTE
        =======================================

        Rut de Empresa Emisora   : 76192083-9
        Rut que Realizo el Envio : 11222333-4
        Identificador de Envio   :   34288XXX
        Fecha de Recepcion       : 18/09/2015 03:24:24
        Estado del Envio         : EPR - Envio Procesado

        Estadisticas del Envio
        ======================
        Tipo DTE  Informados  Rechazos  Reparos  Aceptados
        --------------------------------------------------
           33              7         0        0          7
           34              6         0        0          6
           56              1         0        0          1
           61              2         0        0          2

Listo! :-)

Etapa 3: Intercambio
--------------------

1. [Bajar un archivo de set de intercambio](https://www4.sii.cl/pfeInternet/#menu)

2. Usando el XML bajado desde el SII generar respuesta usando la opción de
[certificación de etapa 3]({_base}/certificacion/intercambio):

	![07_intercambio]({_base}/img/certificacion/07_intercambio.png)

3. Al generar el XML de respuesta de envío se bajará un comprimido con 3 XML,
cada uno de estos XML debe ser [subido al SII](https://www4.sii.cl/pfeInternet/#menu)

	![08_intercambio_respuesta]({_base}/img/certificacion/08_intercambio_respuesta.png)

Listo! :-)

Etapa 4: Muestras impresas
--------------------------

1. Utilizando la opción para
[generar PDF a partir de XML EnvioDTE]({_base}/utilidades/generar_pdf)
se deben crear las muestras impresas, que deben incluir copia cedible. Se deben
generar muestras impresas para todos los documentos de los set de pruebas básico
y exento, además se deben generar las muestras impresas para el envío de
simulación.

	![09_generar_pdf]({_base}/img/certificacion/09_generar_pdf.png)

2. Subir las muestras impresas, todas en el caso de sets de pruebas y una de
cada una en el caso de simulación, a la
[página de carga de muestras impresas del SII](https://www4.sii.cl/pdfdteInternet).

3. El proceso tarda máximo 7 días hábiles y llega un correo del SII avisando el
resultado de la revisión.

Listo! :-)

Fin del proceso de certificación ;-)

[¿dudas?]({_base}/contacto)
