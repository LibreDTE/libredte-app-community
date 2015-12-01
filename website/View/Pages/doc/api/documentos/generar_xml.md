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

Ejemplos
--------

- [Shell script con curl]({_base}/ejemplos/api/generar_xml.sh)
