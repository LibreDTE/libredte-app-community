Documentación de la API
=======================

A través de la API de LibreDTE podrás consumir diferentes recursos con los
cuales podrás realizar diferentes acciones asociadas a los documentos
tributarios electrónicos.

Para poder usar la API es requisito contar con una cuenta de usuario de
LibreDTE, [¡regístrate gratis!]({_base}/usuarios/registrar)

Recursos disponibles
--------------------

- Documentos:
  - [Generación de XML EnvioDTE a partir de documentos en JSON](api/documentos/generar_xml)
  - [Generación de PDF a partir de XML EnvioDTE](api/documentos/generar_pdf)
  - [Verificación de TED](api/documentos/verificar_ted)

Autenticación
-------------

La autenticación en la API es realizada a través de
[HTTP Basic Auth](https://es.wikipedia.org/wiki/Autenticaci%C3%B3n_de_acceso_b%C3%A1sica).
Se deberá utilizar el *hash* asociado a la cuenta del usuario, por ejemplo si el
hash es *Bgw* mediante curl se consultará la API de la siguiente forma:

	$ curl -u Bgw:X {_url}/api/dte/:controlador/:recurso

Después del *hash* es obligatorio colocar *:X*

Finalmente, recordar que si el *hash* se ve comprometido puede ser fácilmente
cambiado en la página del [perfil de usuario]({_base}/usuarios/perfil).
