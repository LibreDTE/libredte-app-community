Instalación de LibreDTE en Debian GNU/Linux 10
==============================================

Servidor
--------

### Requisitos

1. Máquina real o virtual para LibreDTE (no compartida con otros servicios):
   - Mínimo doble núcleo.
   - Mínimo 2 GB de RAM.
   - Mínimo 50 GB disco.

2. Sistema operativo Debian GNU/Linux versión 10 de 64 bits con acceso SSH.

3. Dominio libredte.example.com (o similar) configurado en el DNS (opcional).

### Conexión al servidor

Se asumirán los siguientes datos para trabajar con el servidor:

- Host (dominio o IP): libredte.example.com
- Puerto: 22
- Usuario: admin
- Contraseña: pwdadmin

Nos conectamos mediante SSH con:

```shell
$ ssh libredte.example.com -p 22 -l admin
```

Al presionar *Enter* nos pedirá la contraseña del usuario.

Si el ingreso es con llave pública, en vez de contraseña de usuario, se debe
conectar de la siguiente forma:

```shell
$ ssh libredte.example.com -p 22 -l admin -i llave_publica.pem
```

Donde `llave_publica.pem` es el certificado público del usuario `admin`.

Una vez dentro del servidor cambiamos al usuario `root` con `sudo su -` o `su -`
según corresponda. Toda acción de la instalación se realizará con el usuario
`root`. El símbollo `#` se usará para indicar un comando que se debe ejecutar
con el usuario `root` de ahora en adelante.

### Verificar sistema instalado

Si la instalación del sistema operativo la realizó un tercero, se recomienda
verificar que el sistema sea el requerido.

Validamos versión de Debian:

```shell
# lsb_release -d
Description:    Debian GNU/Linux 10 (buster)
```

Validamos arquitectura:

```shell
# uname -a
Linux goku 4.19.0-17-amd64 #1 SMP Debian 4.19.194-3 (2021-07-18) x86_64 GNU/Linux
```

La parte relevante es la que dice `x86_64` e indica que es un sistema de 64 bits.

También podemos usar `hostnamectl` que nos entregará ambos datos juntos.

Preparación del Servidor para la instalación de LibreDTE
--------------------------------------------------------

Se debe preparar el ambiente donde se instalará LibreDTE, para esto es necesario
seguir los siguientes pasos.

### Contraseñas que se asignarán

Se requieren 4 contraseñas para instalar:

- Contraseña **usuario `admin`** de la aplicación web. Recomendada de 8 caracteres.
- Contraseña **usuario servidor**. Recomendada de 16 caracteres.
- Contraseña **base de datos**. Recomendada de 32 caracteres.
- Contraseña **encriptación** de datos en base de datos. **Debe ser de 32 caracteres**.

Se recomienda que las contraseñas sean todas diferentes y aleatorias. Puede
utilizar el siguiente sitio [www.clavesegura.org](https://www.clavesegura.org)
para la generación aleatoria de contraseñas.

Las contraseñas de usuario del servidor y de la base de datos se deben asignar
como variables de entorno para ser usadas por los comandos posteriores. Sólo
como variables de la sesión y se limpiará el historial de la terminal al
finalizar el proceso de instalación (no deben quedar en el historial).

```shell
# export LIBREDTE_PASSWORD_USER=""     # Debe ser de largo 16 caracteres
# export LIBREDTE_PASSWORD_DB=""       # Debe ser de largo 32 caracteres
```

### Actualizar sistema e instalar software necesario

```shell
# apt-get update && apt-get -y dist-upgrade
# apt-get -y install screen vim mailutils mutt sudo git apache2 openssl php \
    php-pear php-gd mercurial curl php-curl php-imap php-pgsql memcached \
    php-memcached php-mbstring php-soap php-zip zip php-gmp php-bcmath \
    rsync postgresql-client ifstat dnsutils ca-certificates
# apt-get -y autoremove --purge && apt-get autoclean && apt-get clean
```

### Configuración de Apache y PHP

Habilitar `AllowOverride All` para `/var/www` en `/etc/apache2/apache2.conf`:

```
<Directory /var/www/>
    options Indexes FollowSymLinks
    AllowOverride all
    Required all granted
</Directory>
```

En `/etc/php/7.3/apache2/php.ini` modificar las sesiones para usar Memcache:

```
session.save_handler = memcached
session.save_path = "127.0.0.1:11211"
```

### SSL con Let's Encrypt (opcional)

```shell
# apt-get install certbot python-certbot-apache
# certbot --apache
```

### Habilitar los módulos de Apache y probar

```shell
# a2enmod rewrite ssl php7.3
# systemctl restart apache2
```

Verificar acceso a los puertos 80 y 443 desde nuestra máquina local con `nmap`:

```shell
$ nmap libredte.example.com -p 80
$ nmap libredte.example.com -p 443
```

Donde la salida para cada caso debe ser similar a:

```shell
80/tcp open http
443/tcp open https
```

Finalmente, acceder al servidor web mediante `https://libredte.example.com` esto
mostrará la página por defecto de Apache.

### Instalar bibliotecas de PEAR

```shell
# pear channel-update pear.php.net
# pear install Mail Mail_mime Net_SMTP
```
### Instalar Composer

```shell
# wget https://getcomposer.org/composer.phar
# chmod +x ./composer.phar
# mv ./composer.phar /usr/bin/composer
```

### Crear usuario libredte

```shell
# useradd -g users -c "LibreDTE" -m -s /bin/bash libredte
# usermod --password $(echo $LIBREDTE_PASSWORD_USER | openssl passwd -1 -stdin) libredte
# echo -e "\nlibredte ALL=(ALL:ALL) ALL" >> /etc/sudoers
```

### Cambiar el directorio raíz de Apache

Se usará el directorio `www/htdocs` dentro del directorio del usuario `libredte`.

```shell
# sudo -u libredte mkdir -p /home/libredte/www/htdocs
# rm -rf /var/www/html
# ln -s /home/libredte/www/htdocs /var/www/html
```

### Crear mensaje de entrada

Este es el mensaje que se mostrará al iniciar sesión mediante SSH en el servidor.

```shell
# cat <<EOF > /etc/motd
Servidor con Aplicación Web de LibreDTE Versión Comunidad
En caso de consultas o soporte abrir un ticket en https://libredte.cl/soporte
LibreDTE ¡facturación electrónica libre para Chile! www.libredte.cl
EOF
```

### Instalación de PostgreSQL

En este caso se asume la instalación de la base de datos en el mismo servidor de
LibreDTE.

Primero, instalamos la base de datos:

```shell
# apt-get -y install postgresql && pg_ctlcluster 11 main start
# apt-get -y autoremove --purge && apt-get autoclean && apt-get clean
```

Ahora creamos el usuario de la base de datos que se debe llamar igual al usuario
del servidor `libredte`. Cuando se instala con la base de datos en el mismo
servidor, esto permite utilizar `psql` de manera más sencilla.

```shell
# sudo -u postgres createuser --createdb --no-createrole --no-superuser libredte
# sudo -u postgres psql template1 <<EOF
    ALTER USER libredte WITH PASSWORD '$LIBREDTE_PASSWORD_DB';
EOF
```

Finalmente creamos la base de datos que también se llamará libredte:

```shell
# sudo -u libredte createdb libredte
```

**Nota**: de ser posible, se recomienda la instalación de la base de datos en un
servidor separado e idealmente con los datos replicados.

Si necesitamos conectaros a un servidor de base de datos remoto se debe utilizar:

```shell
# psql -U libredte -h HOST -p 5432 -d libredte --set=sslmode=require -W
```

Instalar framework SowerPHP
---------------------------

```shell
# mkdir /usr/share/sowerphp
# chown libredte: /usr/share/sowerphp
# sudo -u libredte git clone -b 21.10.0 https://github.com/SowerPHP/sowerphp.git /usr/share/sowerphp
# cd /usr/share/sowerphp
# sudo -u libredte composer install
```

**Nota**: donde `21.10.0` es la última versión disponible del SowerPHP o bien la
que se desea instalar. Esta versión debe coincidir en sus 2 primeros números con
la versión de LibreDTE que instalaremos.

Instalar Aplicación Web de LibreDTE
-----------------------------------

Instalar aplicación de LibreDTE y dependencias:

```shell
# cd /home/libredte/www
# sudo -u libredte git clone -b 21.10.0 https://github.com/LibreDTE/libredte-webapp.git htdocs
# cd htdocs/website
# sudo -u libredte composer install
```

**Nota**: donde `21.10.0` es la última versión disponible de LibreDTE o bien la
que se desea instalar. Esta versión debe coincidir en sus 2 primeros números con
la versión de SowerPHP previamente instalada.

Copiar archivos para configuraciones:

```shell
# cd Config
# sudo -u libredte cp core-dist.php core.php
# sudo -u libredte cp routes-dist.php routes.php
```

Crear carpetas usadas por LibreDTE:

```shell
# sudo -u libredte mkdir -p /home/libredte/www/htdocs/data/static/contribuyentes
# sudo -u libredte chmod 777 /home/libredte/www/htdocs/data/static/contribuyentes
# sudo -u libredte mkdir /home/libredte/www/htdocs/data/static/emision_masiva_pdf
# sudo -u libredte chmod 777 /home/libredte/www/htdocs/data/static/emision_masiva_pdf
# sudo -u libredte mkdir /home/libredte/www/htdocs/tmp
# sudo -u libredte chmod 777 /home/libredte/www/htdocs/tmp
```

### Cargar tablas y datos a la base de datos

**¡¡¡MUY IMPORTANTE!!!** Ejecutar cada línea de a una. No copiar y pegar todas
las líneas. Esto debido a que cada *script* tiene una transacción. Esto con el
fin de ir verificando que todo se ejecute de manera correcta.

```shell
# sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/Usuarios/Model/Sql/PostgreSQL/usuarios.sql
# sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Sistema/Module/General/Model/Sql/PostgreSQL/actividad_economica.sql
# sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Sistema/Module/General/Model/Sql/PostgreSQL/banco.sql
# sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Module/DivisionGeopolitica/Model/Sql/PostgreSQL/division_geopolitica.sql
# sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Model/Sql/moneda.sql
# sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Dte/Model/Sql/PostgreSQL.sql
```

### Configuración archivo `website/Config/core.php`

A continuación se muestra como configurar el archivo `website/Config/core.php`
de manera inicial, tanto las opciones obligatorias, como aquellas opcionales.

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
    'type' => 'smtp',
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
\sowerphp\core\Configure::write('dte', [
    'pkey' => '',
    'dtes' => [33, 56, 61],
    'clase_boletas' => '\website\Dte\Utility_EnvioBoleta',
    // [...]
]);
```

#### Configuración Funcionalidades Extras (opcional)

Esta configuración te permite desbloquear [funcionalidades extras de
LibreDTE](https://api.libredte.cl/#extras). Estas permitirán automatizar tareas
y entregar un mejor servicio de facturación a las empresas.

Para acceder a los servicios es necesario tener una cuenta en
[api.libredte.cl](https://api.libredte.cl/#extras) y
[generar un token](https://api.libredte.cl/home#api-auth). Este token se agrega
a la configuración:

```php
\sowerphp\core\Configure::write('proveedores.api', [
    // Desbloquea las funcionalidades Extra de LibreDTE
    // Regístrate Gratis en https://api.libredte.cl
    'libredte' => 'AGREGAR TOKEN AQUI',
]);
```

**¿Qué precio tiene usar estas funcionalidades?** Desde $0.-
[Tenemos planes](https://api.libredte.cl/#precios) según la cantidad de
consultas que estimes hacer cada 24 horas.

#### Configuración Aplicaciones de Terceros (opcional)

LibreDTE soporta el concepto de `aplicaciones`, estas permiten agregar funciones
extras a la aplicación web. Normalmente son aplicaciones de terceros para
acciones con sistemas fuera de LibreDTE.

Por ejemplo, es posible añadir soporte para respaldos con Dropbox. Esto se hace
descomentando la sección `apps`. La aplicación `dtepdfs` viene descomentada por
defecto ya que se requiere para la creación de los PDF de los DTE.

```php
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
\sowerphp\core\Configure::write('firma_electronica.default', [
    'file' => DIR_PROJECT.'/data/firma_electronica/default.p12',
    'pass' => '',
]);
```

#### Configuración Autenticación Secundaria (opcional)

Habilitar en la configuración:

```php
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

Se utiliza reCAPTCHA versión 2.
Desde [www.google.com/recaptcha/admin?hl=es](https://www.google.com/recaptcha/admin?hl=es)
puedes registrarte y obtener las claves publicas y privada.

```php
\sowerphp\core\Configure::write('recaptcha', [
    'public_key' => '',
    'private_key' => '',
]);
```

#### Configuración Auto Registro de Usuarios (opcional)

De  manera predeterminada solo los administradores podrán registrar un usuario.
En el caso que se desee que el usuario se autoregistre, sólo se debe descomentar
el siguiente código.

```php
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

Para usar se debe descomentar y activar lo siguiente:

```php
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
\sowerphp\core\Configure::write('app.trigger_handler', '');
```

### Configuración de la Aplicación Web

#### Configuración Perfil del Usuario

Para iniciar sesión ingresar a `https://libredte.example.com/usuarios/ingresar`:

- Usuario: `admin`
- Contraseña: `admin`

Luego ir a `https://libredte.example.com/usuarios/perfil` y realizar las
siguientes acciones:

1. Cambiar nombre y correo del usuario `admin`. Borrar el el valor del campo
   *Hash* y luego guardar los cambios.
2. Cambiar la contraseña del usuario `admin`. Aquí se usará la contraseña creada
   al inicio.

#### Creación Grupo `dte_plus` y sus permisos

Esta es una propuesta de permisos, es obligatorio crear el grupo y los permisos.
Pero no es obligatorio que sean exactamente estos permisos. Dependiendo de los
requerimientos de la instancia los permisos a crear podrían ser otros.

```shell
# sudo -u libredte psql libredte <<EOF
BEGIN;
INSERT INTO grupo (grupo, activo) VALUES ('dte_plus', true);
INSERT INTO auth (grupo, recurso) VALUES
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/documentos*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/admin/dte_folios*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/admin/firma_electronicas*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/contribuyentes*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_tmps*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_emitidos*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_recibidos*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_compras*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_ventas*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_intercambios*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/sii*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_guias*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/admin/respaldos*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/informes*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dashboard*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_boletas*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_boleta_consumos*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/cobranzas*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/admin/item*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/api/dte/*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/boleta_honorarios*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/boleta_terceros*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/cesiones/*'),
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/registro_compras*')
;
COMMIT;
EOF
```

#### Carga de datos iniciales

Antes de empezar a utilizar el sistema es necesario realizar la carga de datos
inicial en la base de datos.

Los datos a cargar son:

- [Actividades Económicas](https://github.com/LibreDTE/libredte-webapp/raw/master/website/Module/Sistema/Module/General/Model/Sql/actividad_economica.ods)
- [División Geopolítica](https://github.com/SowerPHP/sowerphp/raw/master/extensions/sowerphp/app/Module/Sistema/Module/General/Module/DivisionGeopolitica/Model/Sql/division_geopolitica.ods)
- [Módulo Facturación](https://github.com/LibreDTE/libredte-webapp/raw/master/website/Module/Dte/Model/Sql/datos.ods)

Para cargar los datos acceder a `https://libredte.example.com/dev/bd/poblar` y
subir los archivos ODS uno a uno.

#### Configuración de Permisos (opcional)

1. Ir a `https://libredte.example.com/sistema/usuarios/grupos` y agregar el
   grupo soporte.

2. Ir a `https://libredte.example.com/sistema/usuarios/usuarios` y editar el
   usuario administrador y agregarlo al grupo `dte_plus` y `soporte`.

#### Creación usuario de SASCO para soporte (opcional)

**Nota**: esto es requerido sólo por instancias de LibreDTE que usarán el
soporte de SASCO SpA ([Servicio Local](https://libredte.cl/precios))

Ir a `https://libredte.example.com/sistema/usuarios/usuarios` y crear el
siguiente usuario:

 - Nombre: SASCO SpA
 - Usuario: sasco
 - Contraseña: definida por el equipo de soporte de SASCO
 - Grupos: `dte_plus`, `soporte` y `sysadmin`

#### Crear RUT para Boletas No Nominativas (opcional)

Si se usarán boletas no nominativas hacer el siguiente INSERT:

```shell
# sudo -u libredte psql libredte <<EOF
INSERT INTO contribuyente VALUES (66666666, '6', 'Sin razón social informada', 'Sin giro informado', NULL, NULL, NULL, 'Sin dirección informada', '13101', NULL, NOW());
EOF
```

#### Crear RUT para Documentos de Exportación (opcional)

Si se usarán facturas de exportación hacer el siguiente INSERT

```shell
# sudo -u libredte psql libredte <<EOF
INSERT INTO contribuyente VALUES (55555555, '5', 'Extranjero', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NOW());
EOF
```

### Configuración Tareas Programadas

Para agregar las tareas programadas ejecutar:

```shell
# sudo -u libredte crontab -e
```

Y agregamos:

```
# Enviar lo que no esté enviado al SII y actualizar el estado de los documentos emitidos
0 */4 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteEmitidos_Actualizar dte_plus -v

# Actualizar la bandeja de intercambio (recupera los correos de proveedores)
0 1 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteIntercambios_Actualizar dte_plus -v

# Enviar el resumen de ventas diarias (ex RCOF) al SII
0 2 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.Boletas_EnviarRCOF dte_plus -v

# Envía por correo los XML de DTE emitidos al correo de intercambio del receptor
0 3 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteEmitidos_Intercambio

# Sincronizar eventos del receptor de los DTE emitidos (requiere funcionalidades extras)
#0 4 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteEmitidos_EventosReceptor dte_plus -v

# Sincronizar BHE, BTE y RCV (requiere funcionalidades extras)
#0 5 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.Sii_Sincronizar -v

# Sincronizar datos de Contribuyentes que usan el Portal MIPYME (requiere funcionalidades extras)
#0 6 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.Sii_Mipyme dte_plus -v

# Actualización de contribuyentes y correo de intercambio
# Ejecutar manualmente una vez este comando y luego que termine descomentar acá
# (requiere funcionalidades extras)
#45 23 * * 1 /home/libredte/www/htdocs/website/Shell/shell.php Dte.Contribuyentes_Actualizar libredte

# Respaldo de Dropbox (opcional)
#0 7 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.Respaldos_Dropbox dte_plus -v

# respaldos (opcional)
#0 2 * * * pg_dump libredte > /home/libredte/libredte_`date +\%u`.sql && gzip -f /home/libredte/libredte_`date +\%u`.sql
#0 2 * * * tar czf /home/libredte/www.tgz /home/libredte/www/htdocs > /dev/null
```

**Nota**: los tiempos de ejecución son propuestas. Cada instancia puede definir
sus propios tiempos.

#### Configurar mutt (opcional)

`mutt` es un visor de correo electrónico que usaremos para revisar los
resultados de ejecución de las tareas programadas.

Para configurar `mutt` ejecutar:

```shell
# sudo -u libredte cat <<EOF > /home/libredte/.muttrc
set date_format="%F %R"
set index_format="%4C %Z %D %-15.15L (%4l) %s"
EOF
```
## Limpieza de comandos ejecutados (opcional)

Para limpiar el historial de la consola (recomendado sobre todo por las
contraseñas definidas al inicio como variables de entorno). Se debe cerrar
sesión de SSH y desde la máquina local ejecutar:

```shell
$ ssh libredte.example.com -p 22 -l admin shred -n 10 -u /tmp/sowerpkg*
$ ssh libredte.example.com -p 22 -l admin shred -n 10 -u /root/.bash_history
```
