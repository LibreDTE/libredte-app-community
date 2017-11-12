Instalación aplicación web LibreDTE
===================================

La aplicación web está soportada oficialmente en Debian GNU/Linux 9 de 64 bits.
Es posible usarla en otros sistemas GNU/Linux, como Arch Linux o Ubuntu, pero
no se entrega soporte para dichas plataformas.

Al instalar este software estás aceptando cumplir con
[los términos, condiciones y licencia](https://wiki.libredte.cl/doku.php/terminos)
de los proyectos asociados a LibreDTE.

Requisitos
----------

- Apache 2 y PHP 7.0 o superior
- Base de datos PostgreSQL 9
- Memcached con PHP usándolo como backend para las sesiones
- [Otros requisitos de SowerPHP](https://github.com/SowerPHP/sowerphp/blob/master/INSTALL.md)
- Tener [SowerPHP](https://github.com/SowerPHP/sowerphp) instalado con las
extensiones [app](https://github.com/SowerPHP/extension-app) y
[general](https://github.com/SowerPHP/extension-general)

### Instalación del framework

Usando [SowerPKG](https://github.com/SowerPHP/sowerpkg) se instala en el
directorio por defecto */usr/share/sowerphp*:

	$ wget -c https://github.com/SowerPHP/sowerpkg/raw/master/sowerpkg.sh
	$ chmod +x sowerpkg.sh
	$ ./sowerpkg.sh install -e "app general" -W

Esto instalará el framework SowerPHP, y las extensiones "general" y "app".

SowerPKG requiere que esté instalado curl, git y mercurial (hg).

Adicionalmente SowerPKG dejará composer en el directorio ~/bin por lo cual se
recomienda agregarlo al PATH del usuario para evitar tener que escribir la ruta
completa al momento de usar composer.

Instalación aplicación web
--------------------------

1.	Clonar código de la aplicación al directorio del servidor web:

		$ cd DIRECTORIO_SERVIDOR_WEB
		$ git clone --recursive https://github.com/LibreDTE/libredte-webapp.git libredte

	Esto instalará la aplicación web de LibreDTE dentro del directorio
	libredte en DIRECTORIO_SERVIDOR_WEB y se asume que se accederá vía
	navegador a través de <http://example.com/libredte>

	La instalación por defecto asume que SowerPHP está instalado en
	*/usr/share/sowerphp*, si esto no es así se deberá editar el archivo
	*libredte/website/webroot/index.php* con la ruta correcta al framework.

2.	Instalar dependencias de composer:

		$ cd libredte/website
		$ composer install

3.	Crear archivo de configuración y rutas de LibreDTE:

		$ cd Config
		$ cp core-dist.php core.php
		$ cp routes-dist.php routes.php

4.	Editar configuración de la aplicación en el archivo
	*website/Config/core.php*, obligatoriamente se deberá configurar:

	- Configuración para la base de datos
	- Configuración para el correo electrónico
	- Contraseña que se usará para encriptar datos sensibles en la BD

	Si se ofrecerán las utilidades y/o los servicios web se deberá
	configurar:

	- Configuración para firma electrónica
	- Configuración para autenticación en API a usuarios no logueados

	Adicionalmente se recomienda configurar, por razones de seguridad:

	- Configuración para autorización secundaria
	- Configuración para reCAPTCHA

5.	Crear base de datos (debe coincidir con configuración en core.php):

		$ createdb libredte

6.	Cargar *schema* y datos del módulo Sistema.Usuarios de la extensión app:

		$ psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/Usuarios/Model/Sql/PostgreSQL/usuarios.sql

7.	Cargar *schema* para cambios de moneda:

		$ psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Model/Sql/moneda.sql

8.	Cargar *schema* para actividades económicas del módulo Sistema.General:

		$ psql libredte < libredte/website/Module/Sistema/Module/General/Model/Sql/PostgreSQL/actividad_economica.sql

	Cargar datos de actividades económicas: se deberán cargar desde el archivo
	*libredte/website/Module/Sistema/Module/General/Model/Sql/actividad_economica.ods*,
	esto se puede realizar utilizando el módulo Dev y la opción disponible en <http://example.com/libredte/dev/bd/poblar> (se debe habilitar el módulo Dev en Config/core.php).

9.	Cargar *schema* para división geopolítica (regiones, provincias y comunas) del módulo Sistema.General.DivisionGeopolitica de la extensión app:

		$ psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Module/DivisionGeopolitica/Model/Sql/PostgreSQL/division_geopolitica.sql

	Cargar datos de división geopolítica: se deberán cargar desde el archivo
	*/usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Module/DivisionGeopolitica/Model/Sql/division_geopolitica.ods*,
	esto se puede realizar utilizando el módulo Dev y la opción disponible en <http://example.com/libredte/dev/bd/poblar> (se debe habilitar el módulo Dev en Config/core.php).

10.	Cargar *schema* del módulo Dte:

		$ psql libredte < libredte/website/Module/Dte/Model/Sql/PostgreSQL.sql

11.	Cargar datos del módulo Dte del archivo *libredte/website/Module/Dte/Model/Sql/datos.ods* a través de la página de carga de datos.


12.	Crear directorio *data/static/contribuyentes* y permitir que el usuario del servidor web pueda escribir en el.

13.	¡Listo! Ahora puede ingresar a la aplicación web de LibreDTE a través de
	<http://example.com/libredte>.

	El usuario por defecto es *admin* con contraseña *admin*. Se recomienda
	cambiar el nombre de usuario, contraseña y hash del mismo en
	<http://example.com/libredte/usuarios/perfil>.
