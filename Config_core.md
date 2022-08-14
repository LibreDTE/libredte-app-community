Archivo de configuración: `core.php`
====================================

El archivo que está en `website/Config/core.php` contiene la configuración por
defecto de la Aplicación Web de LibreDTE Versión Comunidad.

Este archivo de configuración no debe ser editado, en cambio se podrá alterar
la configuración de la siguiente forma:

- **Usando variables de entorno**: permite configurar sólo algunos puntos, los
más comunes, como la base de datos o el correo electrónico.

- **Creando un archivo `Config/core.php` en una extensión**: permite un control
total de la configuración pudiendo agregar cualquier contenido e incluso
reemplazando alguna configuración que está por defecto en el archivo original.

A continuación se muestra como configurar el archivo `core.php` de manera
inicial, tanto las opciones obligatorias, como aquellas opcionales.

**Importante**: la configuración descrita acá es para la versión 20.12 de
LibreDTE. Las versiones anteriores copiaban el archivo de configuración y se
editaba directamente `core.php`:

```shell
sudo -u libredte cp website/Config/core-dist.php website/Config/core.php
```

Variables de entorno
--------------------

La primera parte de la configuración se realiza mediante variables de entorno.
Para esto debe existir el archivo `env` en la raíz del proyecto:

```shell
sudo -u libredte cp env-dist env
```

Luego se debe configurar el archivo `env` según corresponda.

Configuración en extensión
--------------------------

#### Configuración base de datos

Para realizar la configuración de la base de datos se debe agregar:

- `user` Usuario que es `libredte`.
- `pass` Contraseña (la que creamos al inicio para la base de datos)
- `name` Nombre de la base de datos que es `libredte`.

Estos datos ya fueron creados en los pasos anteriores, es importante que sean
exactamente los mismos con el fin de evitar algún error.

```php
\sowerphp\core\Configure::write('database.default', array(
    'type' => 'PostgreSQL',
    'user' => 'libredte',
    'pass' => '',
    'name' => 'libredte',
));
```

#### Configuración correo electrónico

Para configurar el correo electrónico se necesitan varios parámetros que se
describen a continuación:

- `type` este campo puede ser `smtp` o `smtp-phpmailer`. Se recomienda utilizar
  `smtp-phpmailer` (que es el valor por defecto).
- `host` dominio del proveedor del correo. Por ejemplo `smtp.example.com`.
  - Si el puerto es:
    - `25` entonces usar como `host` el valor `smtp.example.com`
    - `465` entonces usar como `host` el valor `ssl://smtp.example.com`
    - `587` entonces usar como `host` el valor `tls://smtp.example.com`
- `user` Nombre de usuario del correo para la autenticación.
- `pass` Contraseña del usuario del correo para la autenticación.
- `from` Correo que aparecerá como remitente al enviar un email.
- `to` Correo del administrador de la plataforma de LibreDTE para formulario de
   contacto (en caso que se desee usar).

Ahora podemos ver un ejemplo de como se debe configurar.

```php
\sowerphp\core\Configure::write('email.default', array(
    'type' => 'smtp-phpmailer',
    'host' => 'ssl://smtp.example.com',
    'port' => 465,
    'user' => 'libredte@example.com',
    'pass' => '',
    'from' => array('email'=>'libredte@example.com', 'name'=>'LibreDTE'),
    'to' => 'hola@example.com',
));
```

#### Configuración módulo de Facturación

Aquí es obligatorio configurar los siguientes parámetros.

- `pkey` Debe ser de 32 caracteres, es un campo obligatorio y es la contraseña
   creada previamente.
- `dtes` Documentos autorizados por defecto para ser usados por las nuevas
   empresas registradas.
- `clase_boletas` Se debe descomentar para utilizar el envío y consulta de
   estados de boletas a través de las funcionalidades extras de LibreDTE.
   Si no se desean usar las funcionalidades extras, el usuario deberá programar
   una clase propia que envíe y consulte los estados de boletas al SII.

```php
// configuración general del módulo DTE
\sowerphp\core\Configure::write('dte', [
    'pkey' => '',
    'dtes' => [33, 56, 61],
    'clase_boletas' => '\website\Dte\Utility_EnvioBoleta',
    // [...]
]);
```

#### Configuración Funcionalidades Extras (opcional)

Esta configuración te permite desbloquear [funcionalidades extras de
LibreDTE](https://apisii.cl/#extras). Estas permitirán automatizar tareas
y entregar un mejor servicio de facturación a las empresas.

Para acceder a los servicios es necesario tener una cuenta en
[apisii.cl](https://apisii.cl/#extras) y
[generar un token](https://apisii.cl/home#api-auth). Este token se agrega
a la configuración:

```php
\sowerphp\core\Configure::write('proveedores.api', [
    // Desbloquea las funcionalidades Extra de LibreDTE
    // Regístrate Gratis en https://apisii.cl
    'libredte' => 'AGREGAR TOKEN AQUI',
]);
```

**¿Qué precio tiene usar estas funcionalidades?** Desde $0.-
[Tenemos planes](https://apisii.cl/#precios) según la cantidad de
consultas que estimes hacer cada 24 horas.

#### Configuración Aplicaciones de Terceros (opcional)

LibreDTE soporta el concepto de `aplicaciones`, estas permiten agregar funciones
extras a la aplicación web. Normalmente son aplicaciones de terceros para
acciones con sistemas fuera de LibreDTE.

Por ejemplo, es posible añadir soporte para respaldos con Dropbox. Esto se hace
descomentando la sección `apps`. La aplicación `dtepdfs` viene descomentada por
defecto ya que se requiere para la creación de los PDF de los DTE.

```php
// configuración para las aplicaciones de terceros que se pueden usar en LibreDTE
\sowerphp\core\Configure::write('apps_3rd_party', [
    'apps' => [
        'directory' => __DIR__.'/../../website/Module/Apps/Utility/Apps',
        'namespace' => '\website\Apps',
    ],
    'dtepdfs' => [
        'directory' => __DIR__.'/../../website/Module/Dte/Module/Pdf/Utility/Apps',
        'namespace' => '\website\Dte\Pdf',
    ],
]);
```

Si activas las aplicaciones de terceros, deberás agregar la configuración que te
permite usarla. Por ejemplo, para usar Dropbox debes configurar lo siguiente:

```php
// configuración módulo Apps
\sowerphp\core\Configure::write('module.Apps', [
    'Dropbox' => [
        'key' => '',
        'secret' => '',
    ],
]);
```

#### Configuración Firma Electrónica (opcional)

Es muy importante mantener actualizados los correos de intercambio de todos los
contribuyentes de Chile. Así se cumple con la obligatoriedad de enviar el XML al
correo de intercambio correcto.

El SII provee este correo y la forma más simple de cargarlo es mediante las
funcionalidades extras. Esto requiere usar una firma electrónica para obtener el
listado desde el SII. Aquí se verán las 2 opciones disponibles para configurar
la firma.

##### Firma de una de las empresas registradas (recomendado)

Se define una empresa como "proveedora" del servicio de facturación con
LibreDTE. Y es la firma asociada a esta empresa, la que se usará en las
consultas para actualizar el listado de contribuyentes

```php
\sowerphp\core\Configure::write('libredte', [
    'proveedor' => [
        'rut' => 76192083,
    ],
]);
```

Debes cambiar `76192083` por el RUT (sin puntos ni DV) del proveedor registrado
en la aplicación que desees usar.

##### Firma instalada en el servidor (no recomendado)

Esta opción se usa cuando no se quiere definir un proveedor, pero en general no
se recomienda, ya que requiere subir la firma al servidor de LibreDTE.

El archivo de la clave de la firma debe estar en formato .p12 o .pfx y se
requiere además configurar la contraseña de la firma (clave para abrir el
archivo del certificado digital).

```php
// configuración para firma electrónica
\sowerphp\core\Configure::write('firma_electronica.default', [
    'file' => DIR_PROJECT.'/data/firma_electronica/default.p12',
    'pass' => '',
]);
```

#### Configuración Autenticación Secundaria (opcional)

Habilitar en la configuración:

```php
// Configuración para autorización secundaria (extensión: sowerphp/app)
\sowerphp\core\Configure::write('auth2', [
    '2FA' => [
        'app_url' => 'libredte.example.com',
    ],
]);
```

#### Configuración reCAPTCHA (opcional)

Si se configura, se usará en:

- Registro de usuarios.
- Inicio de sesión al existir un intento previo fallido.
- Consulta pública de DTE.

Se utiliza reCAPTCHA versión 3.
Desde [www.google.com/recaptcha/admin?hl=es](https://www.google.com/recaptcha/admin?hl=es)
puedes registrarte y obtener las claves publicas y privada.

**Importante**: la versión 3 de reCAPTCHA es para la versión 20.12 de LibreDTE.
Las versiones anteriores de LibreDTE usan la versión 2 de reCAPTCHA.

```php
// Configuración para reCAPTCHA (extensión: sowerphp/general)
\sowerphp\core\Configure::write('recaptcha', [
    'public_key' => '',
    'private_key' => '',
]);
```

#### Configuración Auto Registro de Usuarios (opcional)

De  manera predeterminada sólo los administradores podrán registrar un usuario.
En el caso que se desee que el usuario se autoregistre, sólo se debe descomentar
el siguiente código.

```php
// Configuración para auto registro de usuarios (extensión: sowerphp/app)
\sowerphp\core\Configure::write('app.self_register', [
    'groups' => ['usuarios', 'dte_plus'],
    'terms' => 'https://legal.libredte.cl',
]);
```

Se deben ajustar los grupos de ser necesario y los términos y condiciones de uso
del servicio de LibreDTE. Los cuales siempre deberán hacer referencia a los
originales.

**Nota**: si se activará el autoregistro se recomienda activar reCAPTCHA.

#### Configuración Preautenticación (opcional)

La autenticación previa es un mecanismo que permite a un usuario acceder a la
aplicación web sin tener que volver a colocar sus credenciales, ya que un
tercero confiable lo ha autorizado previamente.

En [wiki.sowerphp.org](http://wiki.sowerphp.org/doku.php/howto/autenticacion_y_autorizacion#autenticacion_previa_preauth) se encuentra la
documentación de manera mas detalla.

Para usar se debe activar lo siguiente:

```php
// configuración para preautenticación
\sowerphp\core\Configure::write('preauth', [
    'enabled' => true,
]);
```

#### Configuración Handler para Triggers (opcional)

LibreDTE tiene `triggers` que se ejecutan para poder llamar a código que no es
parte de la aplicación base. Esto permite, por ejemplo, ejecutar un código que:

- Provea correos electrónicos desde fuentes diferentes a las por defecto para el
  envío de los DTE al receptor.
- Tomar alguna acción o realizar una validación previo a la emisión de un
  documento temporal (borrador).

Existen muchos `triggers` que permiten extender las funcionalidades de LibreDTE.

El `handler` es el que procesará la ejecución del `trigger`. Para usarlo, se
debe indicar el `handler` a usar en:

```php
// handler para triggers de la app
\sowerphp\core\Configure::write('app.trigger_handler', '');
```
