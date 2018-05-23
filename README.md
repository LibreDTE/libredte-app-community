LibreDTE: Aplicación web
========================

LibreDTE es un proyecto que tiene por objetivo proveer facturación electrónica
libre para Chile.

Aquí encontrarás el código fuente que ha sido liberado de la aplicación web
oficial disponible sólo en <https://libredte.cl>

Esta aplicación web utiliza la
[biblioteca PHP LibreDTE](https://github.com/LibreDTE/libredte-lib) y el
[módulo Dte](https://github.com/LibreDTE/libredte-modulo-Dte) del framework
[SowerPHP](http://sowerphp.org).

[Realiza una donación al proyecto](https://libredte.github.io/#donar)

Términos y condiciones de uso
-----------------------------

Al utilizar este proyecto, total o parcialmente, automáticamente se acepta
cumplir con los [términos y condiciones de uso](https://wiki.libredte.cl/doku.php/terminos)
que rigen a LibreDTE. La [Licencia Pública General Affero de GNU (AGPL)](https://raw.githubusercontent.com/LibreDTE/libredte-lib/master/COPYING)
sólo aplica para quienes respeten los términos y condiciones de uso. No existe
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
los [términos y condiciones de uso](https://wiki.libredte.cl/doku.php/terminos).

Instalación
-----------

Revisar el [manual de instalación](https://github.com/LibreDTE/libredte-webapp/blob/master/INSTALL.md)
para un paso a paso. La instalación sólo se hace una vez, luego basta actualizar la instancia.

Actualización
-------------

1. Actualizar framework: `sowerpkg update`
2. Actualizar aplicación web: `bin/libredte-update`
3. Ejecutar script actualización base de datos (si corresponde)

Si hay algún error después de actualizar verificar lo siguiente:

- Configuraciones nuevas en `Config/core.php` y `Config/routes.php`
- Script SQL podría haber fallado, sobre todo si hay alguna restricción nueva (resolver a mano).

Contribuir al proyecto
----------------------

Si deseas contribuir con el proyecto, especialmente resolviendo alguna de las
[*issues* abiertas](https://github.com/LibreDTE/libredte-webapp/issues), debes:

1. Hacer fork del proyecto en [GitHub](https://github.com/LibreDTE/libredte-webapp)
2. Crear una *branch* para los cambios: git checkout -b nombre-branch
3. Modificar código: git commit -am 'Se agrega...'
4. Publicar cambios: git push origin nombre-branch
5. Crear un *pull request* para unir la nueva *branch* con LibreDTE.

**IMPORTANTE**: antes de hacer un *pull request* verificar que el código
cumpla con los estándares [PSR-1](http://www.php-fig.org/psr/psr-1)
y [PSR-2](http://www.php-fig.org/psr/psr-2).

Contacto y redes sociales
-------------------------

- Sitio web: <https://libredte.cl>
- Twitter: <https://twitter.com/LibreDTE>
- Facebook: <https://www.facebook.com/LibreDTE>
- Google+: <https://plus.google.com/u/0/101078963971350176990/about>
- Linkedin: <https://www.linkedin.com/grp/home?gid=8403251>
- Youtube: <https://www.youtube.com/channel/UCnh5duQUXmo4l8AD28PakiQ>
