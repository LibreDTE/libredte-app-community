[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2FLibreDTE%2Flibredte-app-community.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2FLibreDTE%2Flibredte-app-community?ref=badge_shield)

LibreDTE: Aplicación Web - Edición Comunidad
============================================

LibreDTE es un proyecto que tiene por objetivo
proveer Facturación Electrónica Libre para Chile.

Aquí encontrarás el código fuente de la Edición Comunidad de LibreDTE,
el cual es la base de la Edición Enterprise, disponible solo a través de
[www.libredte.cl](https://www.libredte.cl).

Esta aplicación web utiliza como núcleo para el sistema de facturación la
[Biblioteca de LibreDTE en PHP](https://github.com/LibreDTE/libredte-lib) y el
[framework SowerPHP](https://www.sowerphp.org) para la plataforma web.

Términos y condiciones de uso
-----------------------------

Al utilizar este proyecto, total o parcialmente, automáticamente se acepta
cumplir con los [términos y condiciones de uso](https://www.libredte.cl/legal)
que rigen a LibreDTE. La [Licencia Pública General Affero de GNU (AGPL)](https://raw.githubusercontent.com/LibreDTE/libredte-app-community/master/COPYING)
solo aplica para quienes respeten los términos y condiciones de uso. No existe
una licencia comercial de LibreDTE, por lo cual no es posible usar el proyecto
si no aceptas cumplir dichos términos y condiciones.

La versión resumida de los términos y condiciones de uso de LibreDTE que
permiten utilizar el proyecto, son los siguientes:

- Tienes la libertad de: usar, estudiar, distribuir y cambiar LibreDTE.
- Si utilizas LibreDTE en tu software, el código fuente de dicho software deberá
  ser liberado de manera pública bajo licencia AGPL.
- Si haces cambios a LibreDTE deberás liberar de manera pública el código fuente
  de dichos cambios bajo licencia AGPL.
- Debes hacer referencia de manera pública en tu software al proyecto y autor
  original de LibreDTE, tanto si usas LibreDTE sin modificar o realizando
  cambios al código.

Es obligación de quienes quieran usar el proyecto leer y aceptar por completo
los [términos y condiciones de uso](https://www.libredte.cl/legal).

Ambiente DEV o QA (no producción)
---------------------------------


[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2FLibreDTE%2Flibredte-app-community.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2FLibreDTE%2Flibredte-app-community?ref=badge_large)

### Instalación

```shell
mkdir -p $HOME/dev/www
cd $HOME/dev/www
git clone git@github.com:sascocl/sowerphp.git sowerphp-framework
git clone git@github.com:LibreDTE/libredte-app-community.git libredte-community
cd libredte-community
composer install
```

### Ejecución de pruebas

```shell
XDEBUG_MODE=coverage ./vendor/bin/phpunit
```