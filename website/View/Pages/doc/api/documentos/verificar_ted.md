Verificación de TED
===================

- Recurso: {_url}/api/dte/documentos/verificar_ted

- Método: POST

- String JSON con el XML del TED codificado en base64

Ejemplo
-------

	$ curl --request POST -u hash:X \
		{_url}/api/dte/documentos/verificar_ted \
		-d '"'"$(base64 -w 0 ted.xml)"'"
