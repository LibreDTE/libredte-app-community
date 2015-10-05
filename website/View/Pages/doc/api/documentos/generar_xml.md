Generación de XML EnvioDTE a partir de documentos en JSON
=========================================================

- Recurso: {_url}/api/dte/documentos/generar_xml

- Método: POST

- Datos como objeto JSON:
  - Emisor (obligatorio): tag Emisor del DTE
  - Receptor (obligatorio): tag Receptor del DTE
  - documentos (obligatorio): tag Documento (sin Emisor y sin Receptor) del DTE
  - folios (obligatorio): arreglo con el contenido de los archivos de los folios asociados a los documentos que se desean generar, codificados en base64
  - firma (obligatorio): objeto con *data* y *pass*. Donde *data* son los datos del archivo de la firma electrónica en base64 y *pass* es la contraseña asociada a la firma.
  - resolucion (opcional): objeto con *FchResol* y *NroResol*, si no se indica se generará sólo el DTE (sin EnvioDTE)
  - normalizar_dte (opcional): si es *true* entonces se tratará de normalizar el DTE completando aquellos tags que no se pasaron (esto se hace por defecto). Si se asigna en *false* se deberá proporcionar el DTE con todos sus datos (incluyendo: Totales, cálculos de IVA, descuentos, etc).

Ejemplo
-------

Ejemplo usando un shell script con curl, puedes descargar el script
[aquí]({_base}/codigo/generar_xml.sh):

	#!/bin/bash

	# datos para la consulta al servicio web
	USER_TOKEN='' # token/hash del usuario de LibreDTE
	RECURSO='{_url}/api/dte/documentos/generar_xml'
	ZIP='dte.zip' # nombre del archivo que se generará con la respuesta

	# datos para construir el objeto json
	Emisor='{
	    "RUTEmisor": "76192083-9",
	    "RznSoc": "SASCO SpA",
	    "GiroEmis": "Servicios integrales de informática",
	    "Acteco": 726000,
	    "DirOrigen": "Santiago",
	    "CmnaOrigen": "Santiago"
	}'
	Receptor='{
	    "RUTRecep": "55666777-8",
	    "RznSocRecep": "Empresa S.A.",
	    "GiroRecep": "Servicios jurídicos",
	    "DirRecep": "Santiago",
	    "CmnaRecep": "Santiago"
	}'
	documentos='[
	    {
                "Encabezado": {
                    "IdDoc": {
                        "TipoDTE": 34,
                        "Folio": 1
                    }
                },
                "Detalle": [
                    {
                        "NmbItem": "Asesoria y/o capacitacion",
                        "QtyItem": 1,
                        "PrcItem": 100000
                    }
                ]
            }
	]'
	folios='["'$(base64 -w 0 caf_34.xml)'"]'
	firma='{"data": "'$(base64 -w 0 firma_electronica.p12)'", "pass": "contraseña_firma"}'
	resolucion='{"FchResol": "2014-12-05", "NroResol": 0}'
	normalizar_dte='true'

	# construir objeto json con la consulta
	JSON='{
	    "Emisor": '$Emisor',
	    "Receptor": '$Receptor',
	    "documentos": '$documentos',
	    "folios": '$folios',
	    "firma": '$firma',
	    "resolucion": '$resolucion',
	    "normalizar_dte": '$normalizar_dte'
	}'

	# realizar consulta
	curl --request POST -u $USER_TOKEN:X -o $ZIP $RECURSO -d "$JSON"
