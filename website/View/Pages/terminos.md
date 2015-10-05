Términos y condiciones de uso de LibreDTE
=========================================

Sobre la biblioteca
-------------------

La biblioteca de LibreDTE en PHP es software libre bajo los términos y
condiciones de la licencia GPL versión 3. Puedes encontrar una copia de la
licencia, en inglés,
[aquí](https://raw.githubusercontent.com/sascocl/LibreDTE/master/COPYING). En
resumen la licencia indica que:

1. Puedes ejecutar el programa con cualquier propósito.

2. Puedes estudiar y modificar el programa.

3. Debes distribuir el programa de manera que ayudes al prójimo.

4. Debes distribuir las versiones modificadas propias, el código fuente, bajo la
   misma licencia GPL (reconociendo al autor original).

Sobre la interfaz web (incluyendo *webservices*)
------------------------------------------------

El sitio web de LibreDTE y los servicios web (o *webservices*) disponibles aquí
están sujetos a los siguientes términos:

1. El acceso a las funcionalidades de la interfaz web para certiicación por el
   momento no requiere de una cuenta de usuario. Sin embargo en el futuro esto
   se podría exigir.

2. Para acceder al módulo DTE es requisito contar con una cuenta de usuario.

3. Para acceder a la API es requisito contar con una cuenta de usuario.

4. LibreDTE no entregará ni compartirá los datos de las cuentas de usuarios con
   terceros.

5. En las funcionalidades de certificación, y aquellas que no requieren de una
   cuenta de usuario, LibreDTE no almacenará por ningún motivo en sus
   servidores:

    - Firmas electrónicas (aka: certificados digitales)
    - Contraseñas de las firmas
    - Archivos CAF (aka: folios)
    - Detalles de documentos tributarios electrónicos

   Lo anterior se solicita con el único objetivo de poder construir los
   documentos para el usuario y no son almacenados.

5. LibreDTE si almacenará:

    - Los puntos anteriores cuando el proceso se realiza a través del módulo
      DTE. O sea, existe una sesión de usuario creada y está trabajando con una
      empresa registrada en el sistema.

    - Datos de emisor y receptor al utilizar la opción de generar XML ya sea por
      interfaz web o por API. Específicamente se registrará:

          - RUT emisor
          - Razón social
          - Giro
          - Código actividad económica
          - Teléfono
          - Email
          - Dirección
          - Código de comuna
          - Código de sucursal del SII
          - Fecha de la resolución que autoriza a emitir DTE
          - Número de la resolución que autoriza a emitir DTE
          - Última vez que el contribuyente fue modificado

      Estos se guardan con el fin de autocompletar los formularios cuando un
      usuario desee generar un documento a través de la interfaz web a un
      contribuyente. Si el contribuyente está registrado en LibreDTE como
      empresa (asociada a alguna cuenta de usuario) sus datos no serán
      modificados a menos que lo haga el usuario administrador a través de la
      opción de "modificar empresa".
