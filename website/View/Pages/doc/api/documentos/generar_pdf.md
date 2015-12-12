Generación de PDF a partir de XML EnvioDTE
==========================================

- Recurso: {_url}/api/dte/documentos/generar_pdf

- Método: POST

- Datos como objeto JSON:
  - xml (obligatorio): archivo XML de EnvioDTE codificado en base64
  - cedible (opcional): indica si se debe (true) o no (false u omitido) generar la copia cedible del documento
  - logo (opcional): archivo PNG con la imagen del logo del emisor codificada en base64
  - papelContinuo: =false se usa hoja carta, =X será papel contínuo de X mm de ancho
  - webVerificacion: =false se usará www.sii.cl, si se asigna un valor se usará como web de verificación bajo el timbre
  - compress (opcional): =false (y sólo hay un DTE en EnvioDTE) se entregará el PDF sin comprimir

Ejemplos
--------

Con logo, con copia cedible:

	$ curl --request POST -u hash:X -o documento.zip \
		{_url}/api/dte/documentos/generar_pdf \
		-d '{"xml": "'"$(base64 -w 0 EnvioDTE.xml)"'", "cedible": true, "logo": "'"$(base64 -w 0 logo.png)"'"}'

Con logo, sin copia cedible:

	$ curl --request POST -u hash:X -o documento.zip \
		{_url}/api/dte/documentos/generar_pdf \
		-d '{"xml": "'"$(base64 -w 0 EnvioDTE.xml)"'", "logo": "'"$(base64 -w 0 logo.png)"'"}'

Sin logo, con copia cedible:

	$ curl --request POST -u hash:X -o documento.zip \
		{_url}/api/dte/documentos/generar_pdf \
		-d '{"xml": "'"$(base64 -w 0 EnvioDTE.xml)"'", "cedible": true}'

Sin logo, sin copia cedible:

	$ curl --request POST -u hash:X -o documento.zip \
		{_url}/api/dte/documentos/generar_pdf \
		-d '{"xml": "'"$(base64 -w 0 EnvioDTE.xml)"'"}'
