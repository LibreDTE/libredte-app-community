#!/bin/bash

# datos para la consulta al servicio web
USER_TOKEN='' # token/hash del usuario de LibreDTE
RECURSO='https://libredte.sasco.cl/api/dte/documentos/generar_xml'
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
firma='{"data": "'$(base64 -w 0 firma.p12)'", "pass": "contraseña de la firma"}'
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
