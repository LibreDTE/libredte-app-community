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

Nos conectamos mediante SSH desde nuestra máquina local con:

```shell
ssh libredte.example.com -p 22 -l admin
```

Al presionar *Enter* nos pedirá la contraseña del usuario.

Si el ingreso es con llave pública, en vez de contraseña de usuario, se debe
conectar de la siguiente forma:

```shell
ssh libredte.example.com -p 22 -l admin -i llave_publica.pem
```

Donde `llave_publica.pem` es el certificado público del usuario `admin`.

Una vez dentro del servidor cambiamos al usuario `root` con `sudo su -` o `su -`
según corresponda. Toda acción de la instalación de ahora en adelante se
realizará con el usuario `root`.

### Verificar sistema instalado

Si la instalación del sistema operativo la realizó un tercero, se recomienda
verificar que el sistema sea el requerido.

Validamos versión de Debian:

```shell
lsb_release -d
Description:    Debian GNU/Linux 10 (buster)
```

Validamos arquitectura:

```shell
uname -a
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
export LIBREDTE_PASSWORD_USER=""     # Debe ser de largo 16 caracteres
export LIBREDTE_PASSWORD_DB=""       # Debe ser de largo 32 caracteres
```

### Actualizar sistema e instalar software necesario

```shell
apt-get update && apt-get -y dist-upgrade
apt-get -y install screen vim mailutils mutt sudo git apache2 openssl php \
    php-pear php-gd mercurial curl php-curl php-imap php-pgsql memcached \
    php-memcached php-mbstring php-soap php-zip zip php-gmp php-bcmath \
    rsync postgresql-client ifstat dnsutils ca-certificates
apt-get -y autoremove --purge && apt-get autoclean && apt-get clean
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
apt-get install certbot python-certbot-apache
certbot --apache
```

### Habilitar los módulos de Apache y probar

```shell
a2enmod rewrite ssl php7.3
systemctl restart apache2
```

Verificar acceso a los puertos 80 y 443 desde nuestra máquina local con `nmap`:

```shell
nmap libredte.example.com -p 80
nmap libredte.example.com -p 443
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
pear channel-update pear.php.net
pear install Mail Mail_mime Net_SMTP
```
### Instalar Composer

```shell
wget https://getcomposer.org/composer.phar
chmod +x ./composer.phar
mv ./composer.phar /usr/bin/composer
```

### Crear usuario libredte

```shell
useradd -g users -c "LibreDTE" -m -s /bin/bash libredte
usermod --password $(echo $LIBREDTE_PASSWORD_USER | openssl passwd -1 -stdin) libredte
echo -e "\nlibredte ALL=(ALL:ALL) ALL" >> /etc/sudoers
```

### Cambiar el directorio raíz de Apache

Se usará el directorio `www/htdocs` dentro del directorio del usuario `libredte`.

```shell
sudo -u libredte mkdir -p /home/libredte/www/htdocs
rm -rf /var/www/html
ln -s /home/libredte/www/htdocs /var/www/html
```

### Crear mensaje de entrada

Este es el mensaje que se mostrará al iniciar sesión mediante SSH en el servidor.

```shell
cat <<EOF > /etc/motd
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
apt-get -y install postgresql && pg_ctlcluster 11 main start
apt-get -y autoremove --purge && apt-get autoclean && apt-get clean
```

Ahora creamos el usuario de la base de datos que se debe llamar igual al usuario
del servidor `libredte`. Cuando se instala con la base de datos en el mismo
servidor, esto permite utilizar `psql` de manera más sencilla.

```shell
sudo -u postgres createuser --createdb --no-createrole --no-superuser libredte
sudo -u postgres psql template1 <<EOF
    ALTER USER libredte WITH PASSWORD '$LIBREDTE_PASSWORD_DB';
EOF
```

Finalmente creamos la base de datos que también se llamará libredte:

```shell
sudo -u libredte createdb libredte
```

**Nota**: de ser posible, se recomienda la instalación de la base de datos en un
servidor separado e idealmente con los datos replicados.

Si necesitamos conectaros a un servidor de base de datos remoto se debe utilizar:

```shell
psql -U libredte -h HOST -p 5432 -d libredte --set=sslmode=require -W
```

Instalar framework SowerPHP
---------------------------

```shell
mkdir /usr/share/sowerphp
chown libredte: /usr/share/sowerphp
sudo -u libredte git clone -b 21.10.0 https://github.com/SowerPHP/sowerphp.git /usr/share/sowerphp
cd /usr/share/sowerphp
sudo -u libredte composer install
```

**Nota**: donde `21.10.0` es la última versión disponible del SowerPHP o bien la
que se desea instalar. Esta versión debe coincidir en sus 2 primeros números con
la versión de LibreDTE que instalaremos.

**Importante**: desde la versión `22.12.0` ya no se debe ejecutar `composer install`
en el directorio del framework. Actualmente esta versión es la que está en desarrollo.

Instalar Aplicación Web de LibreDTE
-----------------------------------

Instalar aplicación de LibreDTE y dependencias:

```shell
cd /home/libredte/www
sudo -u libredte git clone -b 21.10.0 https://github.com/LibreDTE/libredte-webapp.git htdocs
cd htdocs/website
sudo -u libredte composer install
```

**Nota**: donde `21.10.0` es la última versión disponible de LibreDTE o bien la
que se desea instalar. Esta versión debe coincidir en sus 2 primeros números con
la versión de SowerPHP previamente instalada.

Crear carpetas usadas por LibreDTE:

```shell
sudo -u libredte mkdir -p /home/libredte/www/htdocs/data/static/contribuyentes
sudo -u libredte chmod 777 /home/libredte/www/htdocs/data/static/contribuyentes
sudo -u libredte mkdir /home/libredte/www/htdocs/data/static/emision_masiva_pdf
sudo -u libredte chmod 777 /home/libredte/www/htdocs/data/static/emision_masiva_pdf
sudo -u libredte mkdir /home/libredte/www/htdocs/tmp
sudo -u libredte chmod 777 /home/libredte/www/htdocs/tmp
```

### Cargar tablas y datos a la base de datos

**¡¡¡MUY IMPORTANTE!!!** Ejecutar cada línea de a una. No copiar y pegar todas
las líneas. Esto debido a que cada *script* tiene una transacción. Esto con el
fin de ir verificando que todo se ejecute de manera correcta.

```shell
sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/Usuarios/Model/Sql/PostgreSQL/usuarios.sql
sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Sistema/Module/General/Model/Sql/PostgreSQL/actividad_economica.sql
sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Sistema/Module/General/Model/Sql/PostgreSQL/banco.sql
sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Module/DivisionGeopolitica/Model/Sql/PostgreSQL/division_geopolitica.sql
sudo -u libredte psql libredte < /usr/share/sowerphp/extensions/sowerphp/app/Module/Sistema/Module/General/Model/Sql/moneda.sql
sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Dte/Model/Sql/PostgreSQL.sql
sudo -u libredte psql libredte < /home/libredte/www/htdocs/website/Module/Honorarios/Model/Sql/PostgreSQL.sql
```

### Archivo de configuración

Para la configuración de la aplicación web revisar el
[manual de configuración](https://github.com/LibreDTE/libredte-webapp/blob/master/Config_core.md).

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

#### Creación Grupos `dte_plus`, `soporte` y sus permisos

Esta es una propuesta de permisos, es obligatorio crear el grupo y los permisos.
Pero no es obligatorio que sean exactamente estos permisos. Dependiendo de los
requerimientos de la instancia los permisos a crear podrían ser otros.

```shell
sudo -u libredte psql libredte <<EOF
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
    ((SELECT id FROM grupo WHERE grupo = 'dte_plus'), '/dte/dte_intercambio*'),
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
INSERT INTO grupo (grupo, activo) VALUES ('soporte', true);
INSERT INTO usuario_grupo (usuario, grupo) VALUES
    (1000, (SELECT id FROM grupo WHERE grupo = 'dte_plus')),
    (1000, (SELECT id FROM grupo WHERE grupo = 'soporte'))
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

#### Creación usuario de SASCO para soporte (opcional)

**Nota**: esto es requerido sólo por instancias de LibreDTE que usarán el
soporte de SASCO SpA ([Servicio Local](https://libredte.cl/precios))

Ir a `https://libredte.example.com/sistema/usuarios/usuarios/listar` y crear el
siguiente usuario:

 - Nombre: SASCO SpA
 - Usuario: sasco
 - Contraseña: definida por el equipo de soporte de SASCO
 - Grupos: `dte_plus`, `soporte` y `sysadmin`

#### Crear RUT para Boletas No Nominativas (opcional)

Si se usarán boletas no nominativas hacer el siguiente INSERT:

```shell
sudo -u libredte psql libredte <<EOF
INSERT INTO contribuyente VALUES (66666666, '6', 'Sin razón social informada', 'Sin giro informado', NULL, NULL, NULL, 'Sin dirección informada', '13101', NULL, NOW());
EOF
```

#### Crear RUT para Documentos de Exportación (opcional)

Si se usarán facturas de exportación hacer el siguiente INSERT

```shell
sudo -u libredte psql libredte <<EOF
INSERT INTO contribuyente VALUES (55555555, '5', 'Extranjero', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NOW());
EOF
```

### Configuración Tareas Programadas

Para agregar las tareas programadas ejecutar:

```shell
sudo -u libredte crontab -e
```

Y agregamos:

```
# Enviar lo que no esté enviado al SII y actualizar el estado de los documentos emitidos
0 */4 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteEmitidos_Actualizar dte_plus -v

# Actualizar la bandeja de intercambio (recupera los correos de proveedores)
0 1 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.DteIntercambios_Actualizar dte_plus -v

# Enviar el resumen de ventas diarias (ex RCOF) al SII
# Los RCOF no se envían al SII desde el 1ero de agosto de 2022
#0 2 * * * /home/libredte/www/htdocs/website/Shell/shell.php Dte.Boletas_EnviarRCOF dte_plus -v

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
sudo -u libredte cat <<EOF > /home/libredte/.muttrc
set date_format="%F %R"
set index_format="%4C %Z %D %-15.15L (%4l) %s"
EOF
```
## Limpieza de comandos ejecutados (opcional)

Para limpiar el historial de la consola (recomendado sobre todo por las
contraseñas definidas al inicio como variables de entorno). Se debe cerrar
sesión de SSH y desde la máquina local ejecutar:

```shell
ssh libredte.example.com -p 22 -l admin sudo shred -n 10 -u /root/.bash_history
```
