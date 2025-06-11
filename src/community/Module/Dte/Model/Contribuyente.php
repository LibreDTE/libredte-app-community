<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero
 * de GNU publicada por la Fundación para el Software Libre, ya sea la
 * versión 3 de la Licencia, o (a su elección) cualquier versión
 * posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU
 * para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General
 * Affero de GNU junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website\Dte;

use stdClass;
use sowerphp\autoload\Model;
use sowerphp\core\Network_Email;
use sowerphp\core\Network_Email_Imap;
use sowerphp\core\Network_Http_Rest;
use sowerphp\core\Utility_Array;
use sowerphp\app\Utility_Apps;
use sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna;
use sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas;
use sowerphp\app\Sistema\Usuarios\Model_Usuario;
use sowerphp\app\Sistema\Usuarios\Model_Usuarios;
use sowerphp\general\Utility_Date;
use sowerphp\general\Utility_Mapas_Google;
use website\Dte\Admin\Model_DteFolio;
use website\Sistema\General\Model_ActividadEconomica;

/**
 * Modelo singular de la tabla "contribuyente" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_Contribuyente extends Model
{
    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'db_table_comment' => 'Contribuyentes de la aplicación. Esto incluye emisores, receptores, personas naturales, sin inicio de actividades, etc.',
            'ordering' => ['rut'],
            'list_display' => [
                'rut',
                'razon_social',
                //'telefono',
                //'email',
                'comuna',
                'usuario',
            ],
            'choices' => ['id' => 'rut', 'name' => 'razon_social'],
            'get_latest_by' => ['modificado'],
            'default_permissions' => ['list', 'view'],
            'audit' => [
                'fields' => [
                    'updated_at' => 'modificado',
                ],
            ],
        ],
        'fields' => [
            'rut' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'RUT',
                'help_text' => 'Rol único tributario (RUT) del contribuyente. Para personas naturales será su rol único nacional (RUN).',
                'display' => '(rut)"-"(dv)',
                'min_value' => 1000000,
                'max_value' => 99999999,
                'sanitize' => ['for_rut'],
            ],
            'dv' => [
                'type' => self::TYPE_CHAR,
                'length' => 1,
                'verbose_name' => 'DV',
                'help_text' => 'Dígito verificador del RUT.',
                'show_in_list' => false,
                'searchable' => false,
                'sanitize' => [
                    'remove_non_printable',
                    'strip_tags',
                    'spaces',
                    'trim',
                    'strtoupper'
                ],
            ],
            'razon_social' => [
                'type' => self::TYPE_STRING,
                'max_length' => 100,
                'verbose_name' => 'Razón social',
                'help_text' => 'Razón social de empresas o nombre de personas naturales.',
            ],
            'giro' => [
                'type' => self::TYPE_STRING,
                'max_length' => 80,
                'verbose_name' => 'Giro',
                'help_text' => 'Descripción de la actividad económica que realiza la empresa. En el caso de personas naturales se puede indicar "Particular".',
                'show_in_list' => false,
            ],
            'actividad_economica' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_ActividadEconomica::class,
                'belongs_to' => 'actividad_economica',
                'related_field' => 'codigo',
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Actividad económica',
                'help_text' => 'Actividad económica principal del contribuyente si es de 1era categoría.',
                'display' => '(actividad_economica.actividad_economica)',
                'searchable' => 'actividad_economica:string|categoria:integer',
            ],
            'telefono' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 20,
                'verbose_name' => 'Teléfono',
                'show_in_list' => false,
            ],
            'email' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 80,
                'verbose_name' => 'Email',
                'help_text' => 'Correo electrónico de la empresa.',
                'show_in_list' => false,
            ],
            'direccion' => [
                'type' => self::TYPE_STRING,
                'max_length' => 70,
                'verbose_name' => 'Dirección',
                'help_text' => 'Dirección principal de la empresa.',
                'show_in_list' => false,
            ],
            'comuna' => [
                'type' => self::TYPE_CHAR,
                'max_length' => 5,
                'relation' => Model_Comuna::class,
                'belongs_to' => 'comuna',
                'related_field' => 'codigo',
                'verbose_name' => 'Comuna',
                'help_text' => 'Comuna de la dirección principal de la empresa.',
                'display' => '(comuna.comuna)',
                'searchable' => 'codigo:string|comuna:string',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Usuario',
                'help_text' => 'Administrador principal de la empresa.',
                'display' => '(usuario.usuario)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'modificado' => [
                'type' => self::TYPE_DATE_TIME,
                'auto' => true,
                'verbose_name' => 'Modificado',
                'help_text' => 'Fecha y hora de la última modificación del contribuyente.',
                'show_in_list' => false,
                'searchable' => false,
            ],
        ],
        'configurations' => [
            'model' => [
                'relation' => [
                    'contribuyente' => 'rut',
                ],
            ],
            'fields' => [
                'extra_otras_actividades' => [
                    'default' => [],
                ],
                'extra_web' => [
                    'serializable' => true,
                ],
                'sii_pass' => [
                    'encrypt' => true,
                ],
                'ambiente_produccion_fecha' => [
                    'serializable' => true,
                ],
                'ambiente_produccion_numero' => [
                    'serializable' => true,
                ],
                'email_intercambio_user' => [
                    'serializable' => true,
                ],
                'email_intercambio_pass' => [
                    'encrypt' => true,
                ],
                'email_sii_pass' => [
                    'encrypt' => true,
                ],
            ],
        ],
    ];

    /**
     * Configuración del contribuyente que se mantendrá cuando sea eliminado.
     *
     * @var array
     */
    protected static $onDeleteSaveConfig = [
        'email' => ['intercambio_user'],
    ];

    /**
     * RUTs que están reservados y no serán modificados al guardar el
     * contribuyente.
     *
     * @var array
     */
    protected $rutReservados = [
        0,        // Sin RUT.
        44444444, // RUT para boletas de honorarios a extranjeros.
        55555555, // RUT para exportación.
        66666666, // RUT para boletas no nominativas.
        88888888, // Empresa privada de prueba.
    ];

    /**
     * Atributos que son obligatorios cuando el contribuyente tiene un usuario
     * asociado (está registrado).
     *
     * @var array
     */
    protected $requiredAttributesWithUser = [
        'razon_social',
        'giro',
        'actividad_economica',
        'direccion',
        'comuna',
    ];

    /**
     * Caché de las firmas asociadas al contribuyente.
     *
     * @var array
     */
    protected $firmas = [];

    /**
     * Accessor para el atributo Contribuyente::$dv.
     *
     * Permite calcular automáticamente el DV si no está asignado y el atributo
     * RUT si lo está.
     *
     * @return string|null
     */
    public function getDvAttribute(): ?string
    {
        $value = $this->attributes['dv'] ?? $this->getDefaultValue('dv');
        if ($value === null && $this->rut) {
            $value = \sowerphp\app\Utility_Rut::dv($this->rut);
        }
        return $value;
    }

    /**
     * Accessor para el atributo Contribuyente::$contribuyente.
     *
     * @return void
     */
    public function getContribuyenteAttribute()
    {
        return $this->razon_social;
    }

    /**
     * Accessor para el atributo Contribuyente::$storage_path.
     *
     * Entrega la ruta base en los storage del contribuyente.
     *
     * Es la misma ruta base en el storage static (public) y private.
     *
     * @return string String con la ruta base del contribuyente en el storage.
     */
    public function getStoragePathAttribute(): string
    {
        return sprintf('/contribuyentes/%d', $this->rut);
    }

    /**
     * Accessor para el atributo Contribuyente::$public_disk.
     *
     * Entrega la instancia del disco (storage) público del contribuyente.
     *
     * @return object Instancia con el disco público.
     */
    public function getPublicDiskAttribute(): object
    {
        return app('storage')->subdisk($this->storage_path, 'static');
    }

    /**
     * Accessor para el atributo Contribuyente::$public_url.
     *
     * Entrega la URL para acceder a los recursos del disco público del
     * contribuyente.
     *
     * @param string|null $resource Recurso dentro del disco público.
     * @return string URL completa. La base si no se pasó un recurso o completa
     * al recurso si se pasó como argumento uno.
     */
    public function getPublicUrlAttribute(?string $resource = null): string
    {
        $basepath = sprintf('/static/contribuyentes/%d', $this->rut);
        $fullpath = $resource ? $basepath . $resource : $basepath;
        return url($fullpath);
    }

    /**
     * Obtiene el contribuyente solicitado.
     *
     * @param array $id Clave primaria del modelo.
     * @return stdClass|null
     */
    protected function retrieve(array $id, array $options = []): ?stdClass
    {
        // Determinar variable que tiene el RUT del contribuyente y asignar.
        $rut = $id['rut'] ?? $id[0] ?? null;
        if (is_array($rut)) {
            $rut = $rut['rut'] ?? $rut[0] ?? null;
        }
        // Si el RUT es un string y tiene guión se asume formato "RUT-DV".
        if (!is_numeric($rut) && strpos($rut, '-')) {
            $rut = (explode('-', str_replace('.', '', $rut))[0]);
        }
        // Obtener los datos del contribuyente utilizando su RUT.
        try {
            $result = parent::retrieve(['rut' => $rut]);
        } catch (\Exception $e) {
            $result = null;
        }
        // Si el contribuyente no se logró obtener se buscarán sus datos, si
        // se encuentran, se creará el contribuyente.
        if ($result === null) {
            $autocompletar = config('modules.Dte.contribuyentes.autocompletar');
            if ($autocompletar) {
                $datosActuales = array_merge($this->toArray(), ['rut' => $rut]);
                $datosNuevos = $this->getPluralInstance()->getDatosDesdeSii(
                    $datosActuales
                );
                if ($datosActuales != $datosNuevos) {
                    $this->forceFill($datosNuevos);
                    $result = (object)$datosNuevos;
                    $this->save();
                    $this->exists = true;
                }
            }
        }
        // Entregar el resultados obtenido para el contribuyente.
        return $result;
    }

    /**
     * Guarda los datos del contribuyente, incluyendo su configuración y
     * parámetros adicionales.
     *
     * @param bool $registrado Se usa para indicar que el contribuyente que se
     * está guardando es uno registrado por un usuario (se validan otros datos).
     * @param bool $no_modificar =true Evita que se modifiquen ciertos contribuyentes reservados.
     */
    public function save(array $options = []): bool
    {
        // Si es un contribuyente reservado (que no se debe modificar), se
        // retorna directamente `true` (se hace creer que se guardó).
        if (in_array($this->rut, $this->rutReservados)) {
            return true;
        }

        // Si es contribuyente registrado se hacen algunas verificaciones
        // adicionales respecto a los datos que se podrían haber pasado al
        // guardar.
        if (!empty($this->usuario)) {

            // Atributos requeridos cuando es un contribuyente registrado.
            foreach ($this->requiredAttributesWithUser as $attribute) {
                if (!$this->$attribute) {
                    throw new \Exception(__(
                        'Debe especificar el atributo del contribuyente: %s.',
                        $attribute
                    ));
                }
            }

        }

        // Guardar contribuyente.
        return parent::save($options);
    }

    /**
     * Método que 'elimina' al contribuyente. En realidad los contribuyentes
     * nunca se eliminan. Lo que se hace es desasociar al contribuyente de su
     * usuario administrador.
     * Los datos del contribuyente de documentos emitidos, recibidos, config
     * extra, etc no se eliminan por defecto, se debe solicitar específicamente.
     */
    public function delete(array $options = []): bool
    {
        $all = $options['all'] ?? false;
        $this->getDatabaseConnection()->beginTransaction();
        // desasociar contribuyente del usuario
        $this->usuario = null;
        if (!$this->save()) {
            $this->getDatabaseConnection()->rollback();
            return false;
        }
        // eliminar todos los registros de la empresa de la base de datos
        if ($all) {
            // mantener cierta configuración extra del contribuyente y eliminar todo el resto
            $this->config = [];
            foreach (static::$onDeleteSaveConfig as $configuracion => $variables) {
                foreach ($variables as $variable) {
                    $valor = 'config_' . $configuracion . '_' . $variable;
                    $this->config[$configuracion][$variable] = $this->$valor;
                }
            }
            $vars = [':rut' => $this->rut];
            $this->getDatabaseConnection()->executeRawQuery('
                DELETE
                FROM contribuyente_config
                WHERE contribuyente = :rut
            ', $vars);
            if (!$this->save()) {
                $this->getDatabaseConnection()->rollback();
                return false;
            }
            // módulo Dte
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM contribuyente_dte WHERE contribuyente = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM contribuyente_usuario WHERE contribuyente = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM contribuyente_usuario_dte WHERE contribuyente = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM contribuyente_usuario_sucursal WHERE contribuyente = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_boleta_consumo WHERE emisor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_compra WHERE receptor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_emitido WHERE emisor = :rut', $vars); // borra: dte_emitido_email, cobranza
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_folio WHERE emisor = :rut', $vars); // borra: dte_caf
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_guia WHERE emisor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_recibido WHERE receptor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_intercambio WHERE receptor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_intercambio_recepcion WHERE recibe = :rut', $vars); // borra: dte_intercambio_recepcion_dte
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_intercambio_recibo WHERE recibe = :rut', $vars); // borra: dte_intercambio_recibo_dte
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_intercambio_resultado WHERE recibe = :rut', $vars); // borra: dte_intercambio_resultado_dte
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_referencia WHERE emisor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_tmp WHERE emisor = :rut', $vars); // borra: dte_tmp_email
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM dte_venta WHERE emisor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM item_clasificacion WHERE contribuyente = :rut', $vars); // borra: item
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM registro_compra WHERE receptor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM boleta_honorario WHERE receptor = :rut', $vars);
            $this->getDatabaseConnection()->executeRawQuery('DELETE FROM boleta_tercero WHERE emisor = :rut', $vars);
            // eliminar archivos asociados al contribuyente (carpeta: /storage/static/contribuyentes/RUT)
            // TODO: implementar eliminación.
        }
        // aplicar cambios
        return $this->getDatabaseConnection()->commit();
    }

    /**
     * Entrega el nombre del contribuyente. Entregará el nombre de
     * fantasía si existe o la razón social del contribuyente.
     */
    public function getNombre()
    {
        return $this->config_extra_nombre_fantasia
            ? $this->config_extra_nombre_fantasia
            : $this->razon_social
        ;
    }

    /**
     * Envía un correo electrónico al contribuyente.
     */
    public function notificar(string $asunto, string $mensaje, $para = null, $responder_a = null, $attach = null): bool
    {
        $email = new Network_Email();
        $email->to($para ? $para : $this->getUsuariosEmail());
        if ($responder_a) {
            $email->replyTo($responder_a);
        }
        if ($attach) {
            $email->attach($attach);
        }
        $app = config('app.name');
        $subject = __('[%(app)s] %(rut)s: %(asunto)s', [
            'app' => $app,
            'rut' => $this->rut . '-' . $this->dv,
            'asunto' => $asunto,
        ]);
        $email->subject($subject);
        $msg = $mensaje."\n\n";
        $msg .= 'PD: este correo es generado de forma automática, por favor no contestar.'."\n\n";
        $msg .= '-- ' . "\n" . $app;
        return $email->send($msg) === true ? true : false;
    }

    /**
     * Entrega el RUT formateado del contribuyente.
     */
    public function getRUT(): string
    {
        return num($this->rut) . '-' . $this->dv;
    }

    /**
     * Entrega la glosa del ambiente en el que se encuentra el
     * contribuyente.
     */
    public function getAmbiente(): string
    {
        return $this->enCertificacion() ? 'certificación' : 'producción';
    }

    /**
     * Entrega las actividades económicas del contribuyente.
     */
    public function getListActividades(): array
    {
        $actividades = [$this->actividad_economica];
        if ($this->config_extra_otras_actividades) {
            foreach ($this->config_extra_otras_actividades as $a) {
                $actividades[] = is_object($a) ? $a->actividad : $a;
            }
        }
        $where = [];
        $vars = [];
        foreach ($actividades as $key => $a) {
            $where[] = ':a'.$key;
            $vars[':a'.$key] = $a;
        }
        return $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            SELECT codigo, actividad_economica
            FROM actividad_economica
            WHERE codigo IN ('.implode(',', $where).')
            ORDER BY actividad_economica
        ', $vars);
    }

    /**
     * Entrega el listado de giros del contribuyente por cada
     * actividad económmica que tiene registrada.
     */
    public function getListGiros()
    {
        $giros = [$this->actividad_economica => $this->giro];
        if ($this->config_extra_otras_actividades) {
            foreach ($this->config_extra_otras_actividades as $a) {
                $key = is_object($a) ? $a->actividad : $a;
                $giros[$key] = is_object($a)
                    ? ($a->giro ? $a->giro : $this->giro)
                    : $this->giro
                ;
            }
        }
        return $giros;
    }

    /**
     * Asigna los usuarios autorizados a operar con el contribuyente.
     * @param array $usuarios Arreglo con índice nombre de usuario y valores un arreglo con los permisos a asignar.
     */
    public function setUsuarios(array $usuarios): bool
    {
        $this->getDatabaseConnection()->beginTransaction();
        $this->getDatabaseConnection()->executeRawQuery('
            DELETE
            FROM contribuyente_usuario
            WHERE contribuyente = :rut
        ', [':rut' => $this->rut]);
        foreach ($usuarios as $usuario => $permisos) {
            if (!$permisos) {
                continue;
            }
            $Usuario = new Model_Usuario($usuario);
            if (!$Usuario->exists()) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('Usuario '.$usuario.' no existe');
                return false;
            }
            foreach ($permisos as $permiso) {
                $ContribuyenteUsuario = new Model_ContribuyenteUsuario(
                    $this->rut,
                    $Usuario->id,
                    $permiso
                );
                $ContribuyenteUsuario->save();
            }
        }
        $this->getDatabaseConnection()->commit();
        return true;
    }

    /**
     * Entrega los correos electrónicos asociados a cierto permiso.
     * Por defecto se entregan los correos de los usuarios administradores.
     * @return array Arreglo con los correos electrónicos solicitados.
     */
    public function getUsuariosEmail(string $permiso = 'admin'): array
    {
        $emails = $this->getDatabaseConnection()->getCol('
            (
                SELECT u.email
                FROM contribuyente AS c JOIN usuario AS u ON u.id = c.usuario
                WHERE c.rut = :rut AND u.activo = true
            ) UNION (
                SELECT u.email
                FROM contribuyente_usuario AS c JOIN usuario AS u ON u.id = c.usuario
                WHERE c.contribuyente = :rut AND c.permiso = :permiso AND u.activo = true
            )
        ', [
            ':rut' => $this->rut,
            ':permiso' => $permiso,
        ]);
        return $emails;
    }

    /**
     * Entrega el listado de usuarios autorizados y sus permisos.
     * @return array Tabla con los usuarios y sus permisos.
     */
    public function getUsuarios(): array
    {
        $usuarios = $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            SELECT u.usuario, c.permiso
            FROM usuario AS u, contribuyente_usuario AS c
            WHERE u.id = c.usuario AND c.contribuyente = :rut
            ORDER BY u.usuario
        ', [':rut' => $this->rut]);
        foreach ($usuarios as &$permisos) {
            if (!is_array($permisos)) {
                $permisos = [$permisos];
            }
        }
        return $usuarios;
    }

    /**
     * Entrega el listado de usuarios para los campos select.
     * @return array Listado de usuarios.
     */
    public function getListUsuarios(): array
    {
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT DISTINCT u.id, u.usuario
                FROM usuario AS u JOIN contribuyente_usuario AS c ON u.id = c.usuario
                WHERE c.contribuyente = :rut
            ) UNION (
                SELECT DISTINCT u.id, u.usuario
                FROM usuario AS u JOIN contribuyente AS c ON u.id = c.usuario
                WHERE c.rut = :rut
            )
            ORDER BY usuario
        ', [':rut' => $this->rut]);
    }

    /**
     * Determina si el usuario está o no autorizado a trabajar con
     * el contribuyente.
     *
     * @param Model_Usuario $Usuario con el usuario a verificar.
     * @param string|array $permisos Permisos que se desean verificar que tenga
     * el usuario.
     * @return bool `true` si está autorizado.
     */
    public function usuarioAutorizado($Usuario, $permisos = []): bool
    {
        // Si es el usuario que registró la empresa se le autoriza.
        if ($this->usuario == $Usuario->id) {
            return true;
        }
        // Normalizar permisos.
        if (!is_array($permisos)) {
            $permisos = [$permisos];
        }
        // Ver si el usuario es del grupo de soporte.
        if ($this->config_app_soporte && $Usuario->inGroup(['soporte'])) {
            return true;
        }
        // Ver si el usuario tiene acceso a la empresa.
        $usuario_permisos = $this->getDatabaseConnection()->getCol('
            SELECT permiso
            FROM contribuyente_usuario
            WHERE contribuyente = :rut AND usuario = :usuario
        ', [
            ':rut' => $this->rut,
            ':usuario' => $Usuario->id,
        ]);
        if (!$usuario_permisos) {
            return false;
        }
        // Si se está buscando por un recurso en particular entonces se valida
        // contra los permisos del sistema.
        if (isset($permisos[0]) && $permisos[0][0] == '/') {
            // Actualizar permisos del usuario (útil para llamada vía API).
            $this->setPermisos($Usuario);
            // Verificar permisos.
            foreach ($permisos as $permiso) {
                if ($Usuario->auth($permiso)) {
                    return true;
                }
            }
        }
        // Se está pidiendo un permiso por tipo de permiso.
        // Agrupación, se verifica si pertenece.
        else {
            // Si no se está pidiendo ningún permiso en particular, solo se
            // quiere saber si el usuario tiene acceso a la empresa.
            if (!$permisos) {
                if ($usuario_permisos) {
                    return true;
                }
            }
            // Si se está pidiendo algún permiso en particular,
            // se verifica si existe.
            else {
                foreach ($permisos as $p) {
                    if (in_array($p, $usuario_permisos)) {
                        return true;
                    }
                }
            }
        }
        // Si no se logró determinar el permiso no se autoriza.
        return false;
    }

    /**
     * Asigna los permisos al usuario.
     *
     * @param Model_Usuario $Usuario Usuario al que se asignarán permisos.
     */
    public function setPermisos(&$Usuario)
    {
        $usuario_grupos_reales = $Usuario->groups(true);
        // si el usuario es el administrador de la empresa se asignan sus
        // propios grupos como los que tiene acceso
        if ($this->usuario == $Usuario->id) {
            $usuario_grupos_sesion = $usuario_grupos_reales;
        }
        // si el usuario es de soporte se colocan los permisos completos del
        // usuario principal de la empresa
        else if ($this->config_app_soporte && in_array('soporte', $usuario_grupos_reales)) {
            $usuario_grupos_sesion = $this->getUsuario()->groups(true);
        }
        // si es un usuario autorizado, entonces se copian los permisos
        // asignados de los disponibles en el administrador
        else {
            $usuario_grupos_sesion = [];
            // siempre asignar el grupo 'usuarios' para mantener permisos básicos
            $usuario_grupos_sesion[] = 'usuarios';
            // buscar los permisos que tiene el usuario autorizado sobre la empresa
            $usuario_permisos = $this->getDatabaseConnection()->getCol('
                SELECT permiso
                FROM contribuyente_usuario
                WHERE contribuyente = :rut AND usuario = :usuario
            ', [
                ':rut' => $this->rut,
                ':usuario' => $Usuario->id,
            ]);
            // mapa de permisos definidos por la configuración y la empresa
            $permisos = config('modules.Dte.contribuyentes.permisos');
            // asignar los grupos del sistema a los que se podría tener acceso
            // por el permisos de la empresa
            $admin_grupos = $this->getUsuario()->getGroups();
            foreach ($usuario_permisos as $p) {
                foreach ($permisos[$p]['grupos'] as $g) {
                    if (
                        !in_array($g, $usuario_grupos_sesion)
                        && in_array($g, $admin_grupos)
                    ) {
                        $usuario_grupos_sesion[] = $g;
                    }
                }
            }
        }
        // buscar permisos y grupos del usuario principal administrador
        $usuario_auths_sesion = $this->getUsuario()
            ->getAuths($usuario_grupos_sesion)
        ;
        // corregir permisos de soporte
        // si el contribuyente tiene activado soporte: los del usuario
        // si el contribuyente no tiene soporte activo: se añaden los de soporte/backoffice
        if (in_array('soporte', $usuario_grupos_reales)) {
            $grupos_soporte = $this->config_app_soporte
                ? $usuario_grupos_reales
                : [
                    'sysadmin',
                    'appadmin',
                    'passwd',
                    'soporte',
                    'mantenedores',
                ]
            ;
            foreach ($grupos_soporte as $grupo) {
                if (
                    in_array($grupo, $usuario_grupos_reales)
                    && !in_array($grupo, $usuario_grupos_sesion)
                ) {
                    $grupo_auths = $Usuario->getAuths([$grupo]);
                    $usuario_auths_sesion = array_merge(
                        $usuario_auths_sesion,
                        $grupo_auths
                    );
                    $usuario_grupos_sesion[] = $grupo;
                }
            }
        }
        // guardar los permisos y los grupos que tendrá el usuario durante la sesión
        $Usuario->setAuths($usuario_auths_sesion);
        $Usuario->setGroups($usuario_grupos_sesion);
    }

    /**
     * Determina si el usuario está o no autorizado a asignar
     * manualmente el Folio de un DTE.
     * @param Model_Usuario $Usuario Usuario con el usuario a verificar.
     * @return bool `true` si está autorizado a cambiar el folio.
     */
    public function puedeAsignarFolio($Usuario): bool
    {
        if (!$this->config_emision_asignar_folio) {
            return false;
        }
        if ($this->config_emision_asignar_folio == 1) {
            return $this->usuarioAutorizado($Usuario, 'admin');
        }
        if ($this->config_emision_asignar_folio == 2) {
            return $this->usuarioAutorizado($Usuario, ['admin', 'dte']);
        }
        return false;
    }

    /**
     * Entrega los documentos que el contribuyente tiene autorizados
     * a emitir en la aplicación.
     * @param bool|object $onlyPK
     * @return array Listado de documentos autorizados.
     */
    public function getDocumentosAutorizados($onlyPK = false, $User = null): array
    {
        // invertir parámetros recibidos si User es objeto (se pasó el objeto del usuario)
        if (is_object($onlyPK)) {
            $aux = $onlyPK;
            $onlyPK = (bool)$User;
            $User = $aux;
        }
        // buscar documentos
        if ($onlyPK) {
            $documentos = $this->getDatabaseConnection()->getCol('
                SELECT
                    t.codigo
                FROM
                    contribuyente_dte AS c
                    JOIN dte_tipo AS t ON
                        t.codigo = c.dte
                WHERE
                    c.contribuyente = :rut
                    AND c.activo = :activo
                ORDER BY
                    t.codigo
            ', [
                ':rut' => $this->rut,
                ':activo' => 1,
            ]);
        } else {
            $documentos = $this->getDatabaseConnection()->getTable('
                SELECT
                    t.codigo,
                    t.tipo
                FROM
                    contribuyente_dte AS c
                    JOIN dte_tipo AS t ON
                        t.codigo = c.dte
                WHERE
                    c.contribuyente = :rut
                    AND c.activo = :activo
                ORDER BY
                    t.codigo
            ', [
                ':rut' => $this->rut,
                ':activo' => 1,
            ]);
        }
        // entregar todos los documentos si no se pidió filtrar por usuario
        // o el usuario es administrador o el usuario es de soporte
        if (!$User || $User->id == $this->usuario || $User->inGroup(['soporte'])) {
            return $documentos;
        }
        // obtener solo los documentos autorizados si se pidió por usuario
        $documentos_autorizados = [];
        foreach ($documentos as $d) {
            if (is_array($d)) {
                if ($this->documentoAutorizado($d['codigo'], $User)) {
                    $documentos_autorizados[] = $d;
                }
            } else {
                if ($this->documentoAutorizado($d, $User)) {
                    $documentos_autorizados[] = $d;
                }
            }
        }
        return $documentos_autorizados;
    }

    /**
     * Entrega los documentos que el contribuyente tiene autorizados
     * a emitir en la aplicación por cada usuario autorizado que tiene.
     * @return array Listado de documentos autorizados por usuario.
     */
    public function getDocumentosAutorizadosPorUsuario(): array
    {
        $autorizados = $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            SELECT
                u.usuario,
                d.dte
            FROM
                usuario AS u
                JOIN contribuyente_usuario_dte AS d ON
                    d.usuario = u.id
            WHERE
                d.contribuyente = :contribuyente
        ', [':contribuyente' => $this->rut]);
        foreach ($autorizados as &$a) {
            if (!isset($a[0])) {
                $a = [$a];
            }
        }
        return $autorizados;
    }

    /**
     * Asigna los documentos autorizados por cada usuario del
     * contribuyente.
     * @param usuarios Arreglo asociativo (usuario) con los los documentos.
     */
    public function setDocumentosAutorizadosPorUsuario(array $usuarios)
    {
        $this->getDatabaseConnection()->beginTransaction();
        $this->getDatabaseConnection()->executeRawQuery('
            DELETE
            FROM contribuyente_usuario_dte
            WHERE contribuyente = :rut
        ', [':rut' => $this->rut]);
        foreach ($usuarios as $usuario => $documentos) {
            if (!$documentos) {
                continue;
            }
            $Usuario = new Model_Usuario($usuario);
            if (!$Usuario->exists()) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('Usuario '.$usuario.' no existe.');
                return false;
            }
            foreach ($documentos as $dte) {
                $ContribuyenteUsuarioDte = new Model_ContribuyenteUsuarioDte(
                    $this->rut,
                    $Usuario->id,
                    $dte
                );
                $ContribuyenteUsuarioDte->save();
            }
        }
        $this->getDatabaseConnection()->commit();
        return true;
    }

    /**
     * Determina si el documento puede o no ser emitido por el
     * contribuyente a través de la aplicación.
     * @param int $dte Código del DTE que se quiere saber si está autorizado.
     * @param Model_Usuario $Usuario Permite determinar el permiso para un usuario autorizado.
     * @return bool `true` si está autorizado.
     */
    public function documentoAutorizado($dte, $Usuario = null): bool
    {
        $dte_autorizado = (bool)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM contribuyente_dte
            WHERE contribuyente = :rut AND dte = :dte AND activo = true
        ', [
            ':rut' => $this->rut,
            ':dte' => $dte,
        ]);
        if (!$dte_autorizado) {
            return false;
        }
        if ($Usuario) {
            if ($Usuario->id == $this->usuario || $Usuario->inGroup(['soporte'])) {
                return true;
            }
            $dtes = $this->getDatabaseConnection()->getCol('
                SELECT dte
                FROM contribuyente_usuario_dte
                WHERE contribuyente = :contribuyente AND usuario = :usuario
            ', [
                ':contribuyente' => $this->rut,
                ':usuario' => $Usuario->id,
            ]);
            // Si no hay documentos autorizados o el solicitado no está
            // autorizado se rechaza.
            if (!$dtes || !in_array($dte, $dtes)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Entrega el listado de folios que el Contribuyente dispone.
     */
    public function getFolios(): array
    {
        $folios = $this->getDatabaseConnection()->getTable('
            SELECT
                f.dte,
                t.tipo,
                f.siguiente,
                f.disponibles,
                f.alerta,
                c.xml
            FROM
                dte_folio AS f
                JOIN dte_tipo AS t ON
                    t.codigo = f.dte
                LEFT JOIN dte_caf AS c ON
                    c.emisor = f.emisor
                    AND c.dte = f.dte
                    AND c.certificacion = f.certificacion
                    AND f.siguiente BETWEEN c.desde AND c.hasta
            WHERE
                f.emisor = :rut
                AND f.certificacion = :certificacion
            ORDER BY
                f.dte
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ]);
        foreach ($folios as &$f) {
            $f['fecha_vencimiento'] = $f['meses_autorizacion'] = $f['vigente'] = null;
            if ($f['xml']) {
                try {
                    $caf = decrypt($f['xml']);
                } catch (\Exception $e) {
                    $caf = null;
                }
                if ($caf) {
                    $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
                    $f['fecha_vencimiento'] = $Caf->getFechaVencimiento(); // esto puede ser null si no vence
                    $f['meses_autorizacion'] = $Caf->getMesesAutorizacion(); // esto indica si se pudo obtener un CAF
                    $f['vigente'] = $Caf->vigente(); // esto puede ser true forzado si no vence pero podría no haber caf
                }
            }
            unset($f['xml']);
        }
        return $folios;
    }

    /**
     * Entrega los datos del folio del documento solicitado.
     * @param int $dte Tipo de documento para el cual se quiere su folio.
     */
    public function getFolio(int $dte, int $folio_manual = 0)
    {
        if (!$this->getDatabaseConnection()->beginTransaction(true)) {
            return false;
        }
        $DteFolio = new Model_DteFolio(
            $this->rut,
            $dte,
            $this->enCertificacion()
        );
        if (!$DteFolio->disponibles && $this->config_sii_timbraje_automatico) {
            try {
                // Solicitar timbraje al SII.
                $n_folios_solicitados = $DteFolio->alerta * $this->config_sii_timbraje_multiplicador;
                $DteFolio->timbrar($n_folios_solicitados);
                // Se vuelve a instanciar actualizar info del mantenedor de folios.
                $DteFolio = new Model_DteFolio(
                    $this->rut,
                    $dte,
                    $this->enCertificacion()
                );
            } catch (\Exception $e) {
                // fallar silenciosamente
            }
        }
        if (!$DteFolio->exists() || !$DteFolio->disponibles) {
            $this->getDatabaseConnection()->rollback();
            return false;
        }
        if ($folio_manual == $DteFolio->siguiente) {
            $folio_manual = 0;
        }
        if (!$folio_manual) {
            $folio = $DteFolio->siguiente;
            $DteFolio->siguiente++;
            $DteFolio->disponibles--;
            try {
                if (!$DteFolio->save(false)) {
                    $this->getDatabaseConnection()->rollback();
                    return false;
                }
            } catch (\Exception $e) {
                $this->getDatabaseConnection()->rollback();
                return false;
            }
        } else {
            $folio = $folio_manual;
        }
        $Caf = $DteFolio->getCaf($folio);
        if (!$Caf) {
            $this->getDatabaseConnection()->rollback();
            return false;
        }
        $this->getDatabaseConnection()->commit();
        return (object)[
            'folio' => $folio,
            'Caf' => $Caf,
            'DteFolio' => $DteFolio,
        ];
    }

    /**
     * Entrega una tabla con los datos de las firmas electrónicas de
     * los usuarios que están autorizados a trabajar con el contribuyente.
     */
    public function getFirmas(): array
    {
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    f.run,
                    f.nombre,
                    f.email,
                    f.desde,
                    f.hasta,
                    f.emisor,
                    u.usuario,
                    true AS administrador
                FROM
                    contribuyente AS c
                    JOIN usuario AS u ON u.id = c.usuario
                    JOIN firma_electronica AS f ON f.usuario = u.id
                WHERE
                    c.rut = :rut
            ) UNION (
                SELECT DISTINCT
                    f.run,
                    f.nombre,
                    f.email,
                    f.desde,
                    f.hasta,
                    f.emisor,
                    u.usuario,
                    false AS administrador
                FROM
                    contribuyente AS c
                    JOIN contribuyente_usuario AS cu ON cu.contribuyente = c.rut
                    JOIN usuario AS u ON u.id = cu.usuario
                    JOIN firma_electronica AS f ON f.usuario = u.id
                WHERE
                    c.rut = :rut
                    AND u.id NOT IN (
                        SELECT c.usuario
                        FROM contribuyente AS c
                        WHERE c.rut = :rut
                    )
            )
            ORDER BY
                administrador DESC,
                nombre ASC
        ', [':rut' => $this->rut]);
    }

    /**
     * Entrega el objeto de la firma electronica asociada al
     * usuario que la está solicitando o bien aquella firma del usuario
     * que es el administrador del contribuyente.
     * @param int $user ID del usuario que desea obtener la firma.
     * @return \sasco\LibreDTE\FirmaElectronica.
     */
    public function getFirma(?int $user_id = null)
    {
        if (!isset($this->firmas[(int)$user_id])) {
            // buscar firma del usuario administrador de la empresa
            $datos = $this->getDatabaseConnection()->getRow('
                SELECT
                    f.archivo,
                    f.contrasenia
                FROM
                    contribuyente AS c
                    JOIN firma_electronica AS f ON f.usuario = c.usuario
                WHERE
                    c.rut = :rut
            ', [':rut' => $this->rut]);
            // buscar firma del usuario que está haciendo la solicitud
            if (empty($datos) && $user_id && $user_id != $this->usuario) {
                $datos = $this->getDatabaseConnection()->getRow('
                    SELECT archivo, contrasenia
                    FROM firma_electronica
                    WHERE usuario = :usuario
                ', [':usuario' => $user_id]);
            }
            if (empty($datos)) {
                $this->firmas[(int)$user_id] = false;
                return false;
            }
            // obtener contraseña de la firma (que está encriptada)
            try {
                $pass = decrypt($datos['contrasenia']);
            } catch (\Exception $e) {
                $pass = null;
            }
            if (!$pass) {
                $this->firmas[(int)$user_id] = false;
                return false;
            }
            // cargar firma
            try {
                $this->firmas[(int)$user_id] = new \sasco\LibreDTE\FirmaElectronica([
                    'data' => base64_decode($datos['archivo']),
                    'pass' => $pass,
                ]);
                $this->firmas[(int)$user_id]->check();
            } catch (\Exception $e) {
                $this->firmas[(int)$user_id] = false;
            }
        }
        return $this->firmas[(int)$user_id];
    }

    /**
     * Crea los filtros para ser usados en las consultas de
     * documentos temporales.
     */
    private function crearFiltrosDocumentosTemporales(array $filtros): array
    {
        $where = ['d.emisor = :rut'];
        $vars = [':rut' => $this->rut];
        foreach (['codigo', 'fecha', 'total'] as $c) {
            if (!empty($filtros[$c])) {
                $where[] = 'd.' . $c . ' = :' . $c;
                $vars[':' . $c] = $filtros[$c];
            }
        }
        // usuario
        if (!empty($filtros['usuario'])) {
            if (is_numeric($filtros['usuario'])) {
                $where[] = 'u.id = :usuario';
            } else {
                $where[] = 'u.usuario = :usuario';
            }
            $vars[':usuario'] = $filtros['usuario'];
        }
        // se indica folio del documento temporal en formato DTE-CODIGO_7
        if (!empty($filtros['folio'])) {
            $aux = explode('-', str_replace("'", '-', $filtros['folio']));
            if (!isset($aux[1])) {
                throw new \Exception('Folio del DTE temporal debe ser en formato DTE-CODIGO_7.');
            }
            list($dte, $codigo) = $aux;
            $where[] = 'd.dte = :dte AND SUBSTR(d.codigo, 1, 7) = :codigo';
            $vars[':dte'] = (int)$dte;
            $vars[':codigo'] = strtolower($codigo);
        }
        // filtrar por DTE
        if (!empty($filtros['dte'])) {
            if (is_array($filtros['dte'])) {
                $i = 0;
                $where_dte = [];
                foreach ($filtros['dte'] as $filtro_dte) {
                    $where_dte[] = ':dte' . $i;
                    $vars[':dte' . $i] = $filtro_dte;
                    $i++;
                }
                $where[] = 'd.dte IN ('.implode(', ', $where_dte).')';
            } else {
                $where[] = 'd.dte = :dte';
                $vars[':dte'] = $filtros['dte'];
            }
        }
        // receptor
        if (!empty($filtros['receptor'])) {
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['receptor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['receptor'], '-')) {
                    $filtros['receptor'] = explode(
                        '-',
                        str_replace('.', '', $filtros['receptor'])
                    )[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['receptor'];
                    unset($filtros['receptor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al receptor
            if (!empty($filtros['receptor'])) {
                if ($filtros['receptor'][0] == '!') {
                    $where[] = 'd.receptor != :receptor';
                    $vars[':receptor'] = substr($filtros['receptor'], 1);
                }
                else {
                    $where[] = 'd.receptor = :receptor';
                    $vars[':receptor'] = $filtros['receptor'];
                }
            }
        }
        if (!empty($filtros['razon_social'])) {
            $where[] = '(
                (
                    d.receptor IN (55555555, 66666666)
                    AND d.datos::JSONB->\'Encabezado\'->\'Receptor\'->>\'RznSocRecep\' ILIKE :razon_social
                ) OR (
                    r.razon_social ILIKE :razon_social
                )
            )';
            $vars[':razon_social'] = '%'.$filtros['razon_social'].'%';
        }
        // otros filtros
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'd.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'd.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = 'd.total >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'd.total <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        if (isset($filtros['sucursal_sii']) && $filtros['sucursal_sii'] != -1) {
            if ($filtros['sucursal_sii']) {
                $where[] = 'd.sucursal_sii = :sucursal_sii';
                $vars[':sucursal_sii'] = $filtros['sucursal_sii'];
            } else {
                $where[] = 'd.sucursal_sii IS NULL';
            }
        }
        // entregar filtros
        return [$where, $vars];
    }

    /**
     * Entrega el total de documentos temporales por el contribuyente.
     */
    public function countDocumentosTemporales(array $filtros = []): int
    {
        list($where, $vars) = $this->crearFiltrosDocumentosTemporales($filtros);
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT
                COUNT(*)
            FROM
                dte_tmp AS d
                JOIN dte_tipo AS t ON d.dte = t.codigo
                JOIN contribuyente AS r ON d.receptor = r.rut
                LEFT JOIN usuario AS u ON d.usuario = u.id
            WHERE
                '.implode(' AND ', $where).'
        ', $vars);
    }

    /**
     * Entrega el listado de documentos temporales por el contribuyente.
     */
    public function getDocumentosTemporales(array $filtros = []): array
    {
        list($where, $vars) = $this->crearFiltrosDocumentosTemporales($filtros);
        // armar consulta interna
        $query = '
            SELECT
                d.dte,
                t.tipo,
                d.codigo,
                (d.dte || \'-\' || UPPER(SUBSTR(d.codigo,1,7))) AS folio,
                d.receptor,
                CASE WHEN d.receptor IN (55555555, 66666666) THEN
                    d.datos::JSONB->\'Encabezado\'->\'Receptor\'->>\'RznSocRecep\'
                ELSE
                    r.razon_social
                END AS razon_social,
                d.fecha,
                d.total,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_tmp AS d
                JOIN dte_tipo AS t ON d.dte = t.codigo
                JOIN contribuyente AS r ON d.receptor = r.rut
                LEFT JOIN usuario AS u ON d.usuario = u.id
            WHERE '.implode(' AND ', $where).'
            ORDER BY d.fecha DESC, t.tipo, d.codigo DESC
        ';
        // armar límite consulta
        if (isset($filtros['limit'])) {
            $query = $this->getDatabaseConnection()->setLimit(
                $query,
                $filtros['limit'],
                !empty($filtros['offset']) ? $filtros['offset'] : 0
            );
        }
        // entregar consulta
        return $this->getDatabaseConnection()->getTable($query, $vars);
    }

    /**
     * Crea los filtros para ser usados en las consultas de
     * documentos emitidos.
     */
    private function crearFiltrosDocumentosEmitidos(array $filtros): array
    {
        $where = [
            'd.emisor = :rut',
            'd.certificacion = :certificacion',
        ];
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ];
        foreach (['folio', 'fecha', 'total'] as $c) {
            if (!empty($filtros[$c])) {
                $where[] = 'd.' . $c . ' = :' . $c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        // usuario
        if (!empty($filtros['usuario'])) {
            if (is_numeric($filtros['usuario'])) {
                $where[] = 'u.id = :usuario';
            } else {
                $where[] = 'u.usuario = :usuario';
            }
            $vars[':usuario'] = $filtros['usuario'];
        }
        // filtrar por DTE
        if (!empty($filtros['dte'])) {
            if (is_array($filtros['dte'])) {
                $i = 0;
                $where_dte = [];
                foreach ($filtros['dte'] as $filtro_dte) {
                    $where_dte[] = ':dte' . $i;
                    $vars[':dte' . $i] = $filtro_dte;
                    $i++;
                }
                $where[] = 'd.dte IN ('.implode(', ', $where_dte).')';
            }
            else {
                $filtros['dte'] = (string)$filtros['dte'];
                if ($filtros['dte'][0] == '!') {
                    $where[] = 'd.dte != :dte';
                    $vars[':dte'] = substr($filtros['dte'], 1);
                }
                else {
                    $where[] = 'd.dte = :dte';
                    $vars[':dte'] = $filtros['dte'];
                }
            }
        }
        // receptor
        if (!empty($filtros['receptor'])) {
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['receptor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['receptor'], '-')) {
                    $filtros['receptor'] = explode(
                        '-',
                        str_replace('.', '', $filtros['receptor'])
                    )[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['receptor'];
                    unset($filtros['receptor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al receptor
            if (!empty($filtros['receptor'])) {
                $filtros['receptor'] = (string)$filtros['receptor'];
                if ($filtros['receptor'][0] == '!') {
                    $where[] = 'd.receptor != :receptor';
                    $vars[':receptor'] = substr($filtros['receptor'], 1);
                }
                else {
                    $where[] = 'd.receptor = :receptor';
                    $vars[':receptor'] = $filtros['receptor'];
                }
            }
        }
        if (!empty($filtros['razon_social'])) {
            $razon_social_xpath = $this->getDatabaseConnection()->xml(
                'd.xml',
                '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = '(
                (
                    d.receptor IN (55555555, 66666666)
                    AND '.$razon_social_xpath.' ILIKE :razon_social
                ) OR (
                    r.razon_social ILIKE :razon_social
                )
            )';
            $vars[':razon_social'] = '%' . $filtros['razon_social'] . '%';
        }
        // otros filtros
        if (!empty($filtros['periodo'])) {
            $filtros['fecha_desde'] = Utility_Date::normalize($filtros['periodo'] . '01');
            $filtros['fecha_hasta'] = Utility_Date::lastDayPeriod($filtros['periodo']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'd.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'd.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = 'd.total >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'd.total <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        if (isset($filtros['sucursal_sii']) && $filtros['sucursal_sii'] != -1) {
            if ($filtros['sucursal_sii']) {
                $where[] = 'd.sucursal_sii = :sucursal_sii';
                $vars[':sucursal_sii'] = $filtros['sucursal_sii'];
            } else {
                $where[] = 'd.sucursal_sii IS NULL';
            }
        }
        if (isset($filtros['receptor_evento'])) {
            if ($filtros['receptor_evento']) {
                $where[] = 'd.receptor_evento = :receptor_evento';
                $vars[':receptor_evento'] = $filtros['receptor_evento'];
            } else {
                $where[] = 'd.receptor_evento IS NULL';
            }
        }
        if (!empty($filtros['cedido'])) {
            $where[] = 'd.cesion_track_id IS NOT NULL';
        }
        // vendedor
        if (!empty($filtros['vendedor'])) {
            $vendedor_col = $this->getDatabaseConnection()->xml(
                'd.xml',
                '/EnvioDTE/SetDTE/DTE/*/Encabezado/Emisor/CdgVendedor',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = $vendedor_col . ' = :vendedor';
            $vars[':vendedor'] = $filtros['vendedor'];
        }
        // filtrar por estado del DTE
        if (!empty($filtros['estado_sii'])) {
            // solo documentos sin track id (falta enviar al sii)
            if ($filtros['estado_sii'] == 'sin_track_id') {
                $where[] = 'd.track_id IS NULL';
            }
            // solo documentos sin estado (falta actualizar)
            else if ($filtros['estado_sii'] == 'null') {
                $where[] = '(d.track_id IS NOT NULL AND d.revision_estado IS NULL)';
            }
            // solo documentos sin estado final (falta actualizar)
            else if ($filtros['estado_sii'] == 'no_final') {
                $estados_no_final = implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']);
                $where[] = '(
                    (
                        d.revision_estado IS NOT NULL
                        AND (
                            (
                                STRPOS(d.revision_estado, \' \') = 0
                                AND d.revision_estado IN (
                                    \'' . $estados_no_final . '\'
                                )
                            ) OR (
                                STRPOS(d.revision_estado, \' \') != 0
                                AND SUBSTR(d.revision_estado, 0, STRPOS(d.revision_estado, \' \')) IN (
                                    \'' . $estados_no_final . '\'
                                )
                            )
                        )
                    )
                )';
            }
            // solo documentos con estado rechazado (eliminar y, quizás, volver a emitir)
            else if ($filtros['estado_sii'] == 'rechazados') {
                $estados_rechazados = implode('\', \'', Model_DteEmitidos::$revision_estados['rechazados']);
                $where[] = '(
                    d.revision_estado IS NOT NULL
                    AND (
                        (
                            STRPOS(d.revision_estado, \' \') = 0
                            AND d.revision_estado IN (
                                \'' . $estados_rechazados . '\'
                            )
                        ) OR (
                            STRPOS(d.revision_estado, \' \') != 0
                            AND SUBSTR(d.revision_estado, 0, STRPOS(d.revision_estado, \' \')) IN (
                                \'' . $estados_rechazados . '\'
                            )
                        )
                    )
                )';
            }
        }
        // si se debe hacer búsqueda dentro de los XML
        if (!empty($filtros['xml'])) {
            $i = 1;
            foreach ($filtros['xml'] as $nodo => $valor) {
                $nodo = preg_replace('/[^A-Za-z\/]/', '', $nodo);
                $nodo_col = $this->getDatabaseConnection()->xml('d.xml', '/EnvioDTE/SetDTE/DTE/*/'.$nodo, 'http://www.sii.cl/SiiDte');
                $where[] = 'LOWER(' . $nodo_col . ') LIKE :xml' . $i;
                $vars[':xml' . $i] = '%' . strtolower($valor) . '%';
                $i++;
            }
        }
        // entregar filstros
        return [$where, $vars];
    }

    /**
     * Entrega el total de documentos emitidos por el contribuyente.
     */
    public function countDocumentosEmitidos(array $filtros = []): int
    {
        list($where, $vars) = $this->crearFiltrosDocumentosEmitidos($filtros);
        // contar documentos emitidos
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT
                COUNT(*)
            FROM
                dte_emitido AS d
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN usuario AS u ON
                    d.usuario = u.id
                LEFT JOIN dte_intercambio_resultado_dte AS i ON
                    i.emisor = d.emisor
                    AND i.dte = d.dte
                    AND i.folio = d.folio
                    AND i.certificacion = d.certificacion
            WHERE
                '.implode(' AND ', $where).'
        ', $vars);
    }

    /**
     * Entrega el listado de documentos emitidos por el contribuyente.
     */
    public function getDocumentosEmitidos(array $filtros = []): array
    {
        list($where, $vars) = $this->crearFiltrosDocumentosEmitidos($filtros);
        // armar consulta interna (no obtiene razón social verdadera en DTE
        // exportación por que requiere acceder al XML)
        $query = '
            SELECT
                d.emisor,
                d.dte,
                d.folio,
                d.certificacion
            FROM
                dte_emitido AS d
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN usuario AS u ON
                    d.usuario = u.id
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY
                d.fecha DESC,
                d.fecha_hora_creacion DESC
        ';
        // armar límite consulta
        if (isset($filtros['limit'])) {
            $query = $this->getDatabaseConnection()->setLimit(
                $query,
                $filtros['limit'],
                !empty($filtros['offset']) ? $filtros['offset'] : 0
            );
        }
        // entregar consulta verdadera (esta si obtiene razón social verdadera
        // en DTE exportación, pero solo para las filas del límite consultado)
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'd.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        return $this->getDatabaseConnection()->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                d.receptor,
                CASE WHEN d.receptor NOT IN (55555555, 66666666) THEN
                    r.razon_social
                ELSE
                    '.$razon_social_xpath.'
                END AS razon_social,
                d.fecha,
                d.total,
                d.revision_estado AS estado,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario,
                CASE WHEN d.xml IS NOT NULL OR d.mipyme IS NOT NULL THEN
                    true
                ELSE
                    false
                END AS has_xml,
                d.track_id
            FROM
                dte_emitido AS d
                JOIN ('.$query.') AS e ON
                    d.emisor = e.emisor
                    AND e.dte = d.dte
                    AND e.folio = d.folio
                    AND e.certificacion = d.certificacion
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN usuario AS u ON
                    d.usuario = u.id
                LEFT JOIN dte_intercambio_resultado_dte AS i ON
                    i.emisor = d.emisor
                    AND i.dte = d.dte
                    AND i.folio = d.folio
                    AND i.certificacion = d.certificacion
            ORDER BY
                d.fecha DESC,
                d.fecha_hora_creacion DESC
        ', $vars);
    }

    /**
     * Crea el objeto para enviar correo.
     *
     * @param string $email Email que se quiere obtener: intercambio o sii.
     * @return Network_Email
     */
    public function getEmailSender(
        string $email = 'intercambio',
        bool $debug = false
    ): Network_Email
    {
        $Sender = event(
            'dte_contribuyente_email_sender',
            [$this, $email, ['debug' => $debug]],
            true
        );
        if ($Sender) {
            return $Sender;
        }
        return $this->getEmailSenderSmtp($email, $debug);
    }

    /**
     * Crea el objeto para recibir correo.
     *
     * @param string $email Email que se quiere obtener: intercambio o sii.
     * @return Network_Email_Imap
     */
    public function getEmailReceiver(
        string $email = 'intercambio'
    ): Network_Email_Imap
    {
        $Receiver = event(
            'dte_contribuyente_email_receiver',
            [$this, $email],
            true
        );
        if ($Receiver) {
            return $Receiver;
        }
        return $this->getEmailReceiverImap($email);
    }

    /**
     * Crea el objeto email para enviar por SMTP y lo entrega.
     * @param email Email que se quiere obteber: intercambio o sii.
     * @return Network_Email
     */
    private function getEmailSenderSmtp(string $email = 'intercambio', bool $debug = false): Network_Email
    {
        $user = $this->{'config_email_'.$email.'_user'};
        $pass = $this->{'config_email_'.$email.'_pass'};
        $host = $this->{'config_email_'.$email.'_smtp'};
        // validar campos mínimos
        if (empty($user)) {
            throw new \Exception('El usuario del correo "'.$email.'" no está definido. Por favor, verificar configuración de la empresa.');
        }
        if (empty($pass)) {
            throw new \Exception('La contraseña del correo "'.$email.'" no está definida. Por favor, verificar configuración de la empresa.');
        }
        if (empty($host)) {
            throw new \Exception('El servidor SMTP del correo "'.$email.'" no está definido. Por favor, verificar configuración de la empresa.');
        }
        // crear objeto con configuración del correo
        return new Network_Email([
            'type' => 'smtp-phpmailer',
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'from' => [
                'email' => $user,
                'name' => str_replace(',', '', $this->getNombre())
            ],
            'debug' => $debug,
        ]);
    }

    /**
     * Crea el objeto Imap para recibir correo por IMAP.
     * @param email Email que se quiere obteber: intercambio o sii.
     * @return Network_Email_Imap
     */
    private function getEmailReceiverImap(string $email = 'intercambio'): Network_Email_Imap
    {
        $user = $this->{'config_email_'.$email.'_user'};
        $pass = $this->{'config_email_'.$email.'_pass'};
        $host = $this->{'config_email_'.$email.'_imap'};
        // validar campos mínimos
        if (empty($user)) {
            throw new \Exception('El usuario del correo "'.$email.'" no está definido. Por favor, verificar configuración de la empresa.');
        }
        if (empty($pass)) {
            throw new \Exception('La contraseña del correo "'.$email.'" no está definida. Por favor, verificar configuración de la empresa.');
        }
        if (empty($host)) {
            throw new \Exception('El servidor IMAP del correo "'.$email.'" no está definido. Por favor, verificar configuración de la empresa.');
        }
        // crear objeto con configuración del correo
        $Imap = new Network_Email_Imap([
            'mailbox' => $host,
            'user' => $user,
            'pass' => $pass,
        ]);
        return $Imap->isConnected() ? $Imap : false;
    }

    /**
     * Indica si el correo de recepción configurado en el
     * contribuyente es el correo genérico de LibreDTE.
     */
    public function isEmailReceiverLibredte(string $email = 'intercambio'): bool
    {
        if (!in_array($email, ['intercambio', 'sii'])) {
            return false;
        }
        // está configurado el correo de LibreDTE como "usable" en el contribuyente
        if (!empty($this->config_emails_libredte->disponible)) {
            $config = 'config_email_'.$email.'_receiver';
            if (
                !empty($this->$config->type)
                && $this->$config->type == 'libredte'
            ) {
                return true;
            }
        }
        // no está configurado LibreDTE para recibir correos
        return false;
    }

    /**
     * Entrega el resumen de las boletas por períodos.
     */
    public function getResumenBoletasPeriodos(): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                '.$periodo_col.' AS periodo,
                COUNT(folio) AS emitidas,
                MIN(fecha) AS desde,
                MAX(fecha) AS hasta,
                SUM(exento) AS exento,
                SUM(neto) AS neto,
                SUM(iva) AS iva,
                SUM(total) AS total,
                COUNT(xml) AS xml
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND dte IN (39, 41)
            GROUP BY
                '.$periodo_col.'
            ORDER BY
                '.$periodo_col.' DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ]);
    }

    /**
     * Entrega las boletas de un período.
     */
    public function getBoletas($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                e.dte,
                e.folio,
                e.tasa,
                e.fecha,
                r.rut || \'-\' || r.dv AS rut,
                e.exento,
                e.neto,
                e.iva,
                e.total,
                a.codigo AS anulada
            FROM
                dte_emitido AS e
                LEFT JOIN dte_referencia AS a ON
                    a.emisor = e.emisor
                    AND a.referencia_dte = e.dte
                    AND a.referencia_folio = e.folio
                    AND a.certificacion = e.certificacion
                    AND a.codigo = 1,
                contribuyente AS r
            WHERE
                e.receptor = r.rut
                AND e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.dte IN (39, 41)
                AND '.$periodo_col.' = :periodo
            ORDER BY
                e.fecha,
                e.dte,
                e.folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega los documentos para el reporte de consumo de
     * folios de las boletas electrónicas.
     */
    public function getDocumentosConsumoFolios(string $desde, ?string $hasta = null): array
    {
        if (!$hasta) {
            $hasta = $desde;
        }
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    dte,
                    folio,
                    tasa,
                    fecha,
                    exento,
                    neto,
                    iva,
                    total
                FROM
                    dte_emitido AS e
                WHERE
                    fecha BETWEEN :desde AND :hasta
                    AND emisor = :rut
                    AND certificacion = :certificacion
                    AND dte IN (39, 41)
            ) UNION (
                SELECT
                    e.dte,
                    e.folio,
                    e.tasa,
                    e.fecha,
                    e.exento,
                    e.neto,
                    e.iva,
                    e.total
                FROM
                    dte_referencia AS r
                    JOIN dte_emitido AS e ON
                        r.emisor = e.emisor
                        AND r.dte = e.dte
                        AND r.folio = e.folio
                        AND r.certificacion = e.certificacion
                WHERE
                    r.emisor = :rut
                    AND r.dte = 61
                    AND r.certificacion = :certificacion
                    AND r.referencia_dte IN (39, 41)
                    AND e.fecha BETWEEN :desde AND :hasta
            )
            ORDER BY fecha, dte, folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega el resumen de las ventas por períodos.
     */
    public function getResumenVentasPeriodos(): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha', 'INTEGER');
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    '.$periodo_col.' AS periodo,
                    COUNT(*) AS emitidos,
                    v.documentos AS enviados,
                    v.track_id,
                    v.revision_estado
                FROM
                    dte_emitido AS e
                    JOIN dte_tipo AS t ON
                        t.codigo = e.dte
                    LEFT JOIN dte_venta AS v ON
                        e.emisor = v.emisor
                        AND e.certificacion = v.certificacion
                        AND '.$periodo_col.' = v.periodo
                WHERE
                    e.emisor = :rut
                    AND e.certificacion = :certificacion
                    AND e.dte != 46
                    AND t.venta = true
                GROUP BY
                    '.$periodo_col.',
                    enviados,
                    v.track_id,
                    v.revision_estado
            ) UNION (
                SELECT
                    periodo,
                    documentos AS emitidos,
                    documentos AS enviados,
                    track_id,
                    revision_estado
                FROM
                    dte_venta
                WHERE
                    emisor = :rut
                    AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ]);
    }

    /**
     * Entrega el total de ventas de un período.
     */
    public function countVentas($periodo): int
    {
        $fecha_desde = Utility_Date::normalize($periodo . '01');
        $fecha_hasta = Utility_Date::lastDayPeriod($periodo);
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM
                dte_emitido AS e
                JOIN dte_tipo AS t ON
                    e.dte = t.codigo
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :fecha_desde AND :fecha_hasta
                AND e.dte != 46
                AND t.venta = true
                AND (e.emisor, e.dte, e.folio, e.certificacion) NOT IN (
                    SELECT e.emisor, e.dte, e.folio, e.certificacion
                    FROM
                        dte_emitido AS e
                        JOIN dte_referencia AS r ON
                            r.emisor = e.emisor
                            AND r.dte = e.dte
                            AND r.folio = e.folio
                            AND r.certificacion = e.certificacion
                    WHERE
                        e.emisor = :rut
                        AND e.fecha BETWEEN :fecha_desde AND :fecha_hasta
                        AND r.referencia_dte = 46
                )
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':fecha_desde' => $fecha_desde,
            ':fecha_hasta' => $fecha_hasta,
        ]);
    }

    /**
     * Entrega las ventas de un período.
     * @todo Corregir ID en Extranjero y asignar los NULL por los valores que
     * corresponden (quizás haya que modificar tabla dte_emitido).
     */
    public function getVentas($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'e.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN e.receptor NOT IN (55555555, 66666666) THEN r.razon_social ELSE '
            . $razon_social_xpath . ' END AS razon_social'
        ;
        // si el contribuyente tiene impuestos adicionales se crean las query para esos campos
        if ($this->config_extra_impuestos_adicionales) {
            list($impuesto_codigo, $impuesto_tasa, $impuesto_monto) = $this->getDatabaseConnection()->xml('e.xml', [
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TipoImp',
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TasaImp',
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/MontoImp',
            ], 'http://www.sii.cl/SiiDte');
        } else {
            $impuesto_codigo = $impuesto_tasa = $impuesto_monto = 'NULL';
        }
        if ($this->config_extra_constructora) {
            $credito_constructoras = $this->getDatabaseConnection()->xml(
                'e.xml',
                '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/CredEC',
                'http://www.sii.cl/SiiDte'
            );
        } else {
            $credito_constructoras = 'NULL';
        }
        // campos para datos extranjeros
        list($extranjero_id, $extranjero_nacionalidad) = $this->getDatabaseConnection()->xml('e.xml', [
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Referencia/FolioRef',
            '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad',
        ], 'http://www.sii.cl/SiiDte');
        // TODO: fix xpath para seleccionar la referencia que tiene codigo 813 (u otro doc identidad que se defina).
        $extranjero_id = 'NULL';
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                e.dte,
                e.folio,
                r.rut || \'-\' || r.dv AS rut,
                e.tasa,
                '.$razon_social.',
                e.fecha,
                CASE WHEN e.anulado THEN \'A\' ELSE NULL END AS anulado,
                e.exento,
                e.neto,
                e.iva,
                CASE WHEN e.iva_fuera_plazo THEN
                    e.iva
                ELSE
                    NULL
                END AS iva_fuera_plazo,
                '.$impuesto_codigo.' AS impuesto_codigo,
                '.$impuesto_tasa.' AS impuesto_tasa,
                '.$impuesto_monto.' AS impuesto_monto,
                NULL AS iva_propio,
                NULL AS iva_terceros,
                NULL AS iva_retencion_total,
                NULL AS iva_retencion_parcial,
                NULL AS iva_no_retenido,
                NULL AS ley_18211,
                '.$credito_constructoras.' AS credito_constructoras,
                ref.referencia_dte AS referencia_tipo,
                ref.referencia_folio AS referencia_folio,
                NULL AS deposito_envases,
                NULL AS monto_no_facturable,
                NULL AS monto_periodo,
                NULL AS pasaje_nacional,
                NULL AS pasaje_internacional,
                CASE WHEN e.receptor = 55555555 THEN
                    '.$extranjero_id.'
                ELSE
                    NULL
                END AS extranjero_id,
                CASE WHEN e.receptor = 55555555 THEN
                    '.$extranjero_nacionalidad.'
                ELSE
                    NULL
                END AS extranjero_nacionalidad,
                NULL AS indicador_servicio,
                NULL AS indicador_sin_costo,
                NULL AS liquidacion_rut,
                NULL AS liquidacion_comision_neto,
                NULL AS liquidacion_comision_exento,
                NULL AS liquidacion_comision_iva,
                e.sucursal_sii,
                NULL AS numero_interno,
                NULL AS emisor_nc_nd_fc,
                e.total
            FROM
                dte_emitido AS e
                JOIN contribuyente AS r ON
                    e.receptor = r.rut
                JOIN dte_tipo AS t ON
                    t.codigo = e.dte
                LEFT JOIN dte_referencia AS ref ON
                    ref.emisor = e.emisor
                    AND ref.dte = e.dte
                    AND ref.folio = e.folio
                    AND ref.certificacion = e.certificacion
                    AND ref.dte IN (56, 61, 111, 112)
                    AND ref.codigo = 1
            WHERE
                t.venta = true
                AND e.emisor = :rut
                AND e.certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND e.dte != 46
                AND (e.emisor, e.dte, e.folio, e.certificacion) NOT IN (
                    SELECT
                        e.emisor,
                        e.dte,
                        e.folio,
                        e.certificacion
                    FROM
                        dte_emitido AS e
                        JOIN dte_referencia AS r ON
                            r.emisor = e.emisor
                            AND r.dte = e.dte
                            AND r.folio = e.folio
                            AND r.certificacion = e.certificacion
                        WHERE
                            e.emisor = :rut
                            AND '.$periodo_col.' = :periodo
                            AND r.referencia_dte = 46
                )
            ORDER BY
                e.fecha,
                e.dte,
                e.folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el historial de ventas con el monto total por período
     * para un determinado receptor.
     * @param periodo Período para el cual se está construyendo el libro.
     */
    public function getHistorialVentas($receptor, ?string $fecha = null, int $periodos = 12): array
    {
        if (strpos($receptor, '-')) {
            $receptor = substr($receptor, 0, -2);
        }
        if (in_array($receptor, [55555555, 66666666])) {
            return [];
        }
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        $desde = substr(str_replace('-', '', Utility_Date::getPrevious($fecha, 'M', $periodos)), 0, 6);
        $hasta = Utility_Date::previousPeriod(substr(str_replace('-', '', $fecha), 0, 6));
        // realizar consulta
        $montos = $this->getDatabaseConnection()->getTable('
            SELECT
                '.$periodo_col.' AS periodo,
                t.operacion,
                SUM(e.total) AS total
            FROM
                dte_emitido AS e
                JOIN dte_tipo AS t ON
                    t.codigo = e.dte
            WHERE
                t.venta = true
                AND e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.dte != 46
                AND '.$periodo_col.' BETWEEN :desde AND :hasta
                AND e.receptor = :receptor
            GROUP BY
                periodo,
                t.operacion
            ORDER BY
                periodo
        ', [
            ':emisor' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':receptor' => $receptor,
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        if (!$montos) {
            return [];
        }
        $historial = [];
        $periodo = $montos[0]['periodo'];
        while ($periodo <= $hasta) {
            $historial[(int)$periodo] = 0;
            $periodo = Utility_Date::nextPeriod($periodo);
        }
        foreach ($montos as $monto) {
            if ($monto['operacion'] == 'S') {
                $historial[$monto['periodo']] += $monto['total'];
            } else {
                $historial[$monto['periodo']] -= $monto['total'];
            }
        }
        return $historial;
    }

    /**
     * Entrega el objeto del libro de ventas a partir de las ventas
     * registradas en la aplicación.
     * @param int $periodo Período para el cual se está construyendo el libro.
     */
    public function getLibroVentas($periodo)
    {
        $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $ventas = $this->getVentas($periodo);
        foreach ($ventas as $venta) {
            // armar detalle para agregar al libro
            $d = [];
            foreach ($venta as $k => $v) {
                if (strpos($k, 'impuesto_') !== 0 && strpos($k, 'extranjero_') !== 0) {
                    if ($v !== null) {
                        $d[Model_DteVenta::$libro_cols[$k]] = $v;
                    }
                }
            }
            // agregar datos si es extranjero
            if (!empty($venta['extranjero_id']) || !empty($venta['extranjero_nacionalidad'])) {
                $d['Extranjero'] = [
                    'NumId' => !empty($venta['extranjero_id'])
                        ? $venta['extranjero_id']
                        : false
                    ,
                    'Nacionalidad' => !empty($venta['extranjero_nacionalidad'])
                        ? $venta['extranjero_nacionalidad']
                        : false
                    ,
                ];
            }
            // agregar otros impuestos
            if (!empty($venta['impuesto_codigo'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $venta['impuesto_codigo'],
                    'TasaImp' => $venta['impuesto_tasa'],
                    'MntImp' => $venta['impuesto_monto'],
                ];
            }
            // agregar al libro
            $Libro->agregar($d);
        }
        return $Libro;
    }

    /**
     * Entrega el resumen de las ventas diarias de un período.
     */
    public function getVentasDiarias($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        $dia_col = $this->getDatabaseConnection()->date('d', 'e.fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                '.$dia_col.' AS dia,
                COUNT(*) AS documentos
            FROM
                dte_emitido AS e
                JOIN dte_tipo AS t ON
                    t.codigo = e.dte
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND t.venta = true
                AND e.dte != 46
            GROUP BY
                e.fecha
            ORDER BY
                e.fecha
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el resumen de ventas por tipo de un período.
     * @return Arreglo asociativo con las ventas.
     */
    public function getVentasPorTipo($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                t.tipo,
                COUNT(*) AS documentos
            FROM
                dte_emitido AS e
                JOIN dte_tipo AS t ON
                    t.codigo = e.dte
            WHERE
                t.venta = true
                AND e.emisor = :rut
                AND e.certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND e.dte != 46
            GROUP BY
                t.tipo
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el resumen de las guías por períodos.
     */
    public function getResumenGuiasPeriodos(): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha', 'INTEGER');
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    '.$periodo_col.' AS periodo,
                    COUNT(*) AS emitidos,
                    g.documentos AS enviados,
                    g.track_id,
                    g.revision_estado
                FROM
                    dte_emitido AS e
                    LEFT JOIN dte_guia AS g ON
                        e.emisor = g.emisor
                        AND e.certificacion = g.certificacion
                        AND '.$periodo_col.' = g.periodo
                WHERE
                    e.emisor = :rut
                    AND e.certificacion = :certificacion
                    AND e.dte = 52
                GROUP BY
                    '.$periodo_col.',
                    enviados,
                    g.track_id,
                    g.revision_estado
            ) UNION (
                SELECT
                    periodo,
                    documentos AS emitidos,
                    documentos AS enviados,
                    track_id,
                    revision_estado
                FROM
                    dte_guia
                WHERE
                    emisor = :rut
                    AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ]);
    }

    /**
     * Entrega el resumen de las guías de un período.
     */
    public function countGuias($periodo): int
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT
                COUNT(*)
            FROM
                dte_emitido AS e
                LEFT JOIN dte_referencia AS ref ON
                    e.emisor = ref.emisor
                    AND e.dte = ref.referencia_dte
                    AND e.folio = ref.referencia_folio
                    AND e.certificacion = ref.certificacion
                LEFT JOIN dte_emitido AS re ON
                    re.emisor = ref.emisor
                    AND re.dte = ref.dte
                    AND re.folio = ref.folio
                    AND re.certificacion = ref.certificacion
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND e.dte = 52
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el resumen de las guías de un período.
     */
    public function getGuias($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'e.fecha');
        $tipo_col= $this->getDatabaseConnection()->xml(
            'e.xml',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/IdDoc/IndTraslado',
            'http://www.sii.cl/SiiDte'
        );
        return $this->getDatabaseConnection()->getTable('
            SELECT
                e.folio,
                CASE WHEN e.anulado THEN
                    2
                ELSE
                    NULL
                END AS anulado,
                1 AS operacion,
                '.$tipo_col.' AS tipo,
                e.fecha,
                r.rut || \'-\' || r.dv AS rut,
                r.razon_social,
                e.neto,
                e.tasa,
                e.iva,
                e.total,
                NULL AS modificado,
                ref.dte AS ref_dte,
                ref.folio AS ref_folio,
                re.fecha AS ref_fecha
            FROM
                dte_emitido AS e
                JOIN contribuyente AS r ON
                    e.receptor = r.rut
                LEFT JOIN dte_referencia AS ref ON
                    e.emisor = ref.emisor
                    AND e.dte = ref.referencia_dte
                    AND e.folio = ref.referencia_folio
                    AND e.certificacion = ref.certificacion
                LEFT JOIN dte_emitido AS re ON
                    re.emisor = ref.emisor
                    AND re.dte = ref.dte
                    AND re.folio = ref.folio
                    AND re.certificacion = ref.certificacion
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND e.dte = 52
            ORDER BY
                e.fecha,
                e.folio
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el resumen de las guías diarias de un período.
     */
    public function getGuiasDiarias($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha');
        $dia_col = $this->getDatabaseConnection()->date('d', 'fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                '.$dia_col.' AS dia,
                COUNT(*) AS documentos
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND dte = 52
            GROUP BY
                fecha
            ORDER BY
                fecha
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Cuenta los casos de intercambio del contribuyente.
     */
    public function countDocumentosIntercambios(array $filter = []): int
    {
        return (new Model_DteIntercambios())
            ->setContribuyente($this)
            ->countDocumentos($filter)
        ;
    }

    /**
     * Entrega la tabla con los casos de intercambio del contribuyente.
     */
    public function getDocumentosIntercambios(array $filter = []): array
    {
        return (new Model_DteIntercambios())
            ->setContribuyente($this)
            ->getDocumentos($filter)
        ;
    }

    /**
     * Método para actualizar la bandeja de intercambio.
     */
    public function actualizarBandejaIntercambio(int $dias = 7)
    {
        return (new Model_DteIntercambios())
            ->setContribuyente($this)
            ->actualizar($dias)
        ;
    }

    /**
     * Crea los filtros para ser usados en las consultas de documentos recibidos.
     */
    private function crearFiltrosDocumentosRecibidos(array $filtros): array
    {
        $where = [
            'd.receptor = :rut',
            'd.certificacion = :certificacion',
        ];
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ];
        foreach (['folio', 'fecha', 'total', 'intercambio', 'usuario'] as $c) {
            if (isset($filtros[$c])) {
                $where[] = 'd.' . $c . ' = :' . $c;
                $vars[':'.$c] = $filtros[$c];
            }
        }
        // filtrar por DTE
        if (!empty($filtros['dte'])) {
            if (is_array($filtros['dte'])) {
                $i = 0;
                $where_dte = [];
                foreach ($filtros['dte'] as $filtro_dte) {
                    $where_dte[] = ':dte'.$i;
                    $vars[':dte'.$i] = $filtro_dte;
                    $i++;
                }
                $where[] = 'd.dte IN (' . implode(', ', $where_dte) . ')';
            } else {
                $where[] = 'd.dte = :dte';
                $vars[':dte'] = $filtros['dte'];
            }
        }
        // filtrar por emisor
        if (!empty($filtros['emisor'])) {
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['emisor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['emisor'], '-')) {
                    $filtros['emisor'] = explode(
                        '-',
                        str_replace('.', '', $filtros['emisor'])
                    )[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['emisor'];
                    unset($filtros['emisor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al emisor
            if (!empty($filtros['emisor'])) {
                $where[] = 'd.emisor = :emisor';
                $vars[':emisor'] = $filtros['emisor'];
            }
        }
        if (!empty($filtros['razon_social'])) {
            $where[] = 'e.razon_social ILIKE :razon_social';
            $vars[':razon_social'] = '%' . $filtros['razon_social'] . '%';
        }
        // filtrar por fechas
        if (!empty($filtros['periodo'])) {
            $fecha_desde = Utility_Date::normalize($filtros['periodo'] . '01');
            $fecha_hasta = Utility_Date::lastDayPeriod($filtros['periodo']);
            $where[] = '((d.periodo IS NULL AND d.fecha BETWEEN :fecha_desde AND :fecha_hasta) OR (d.periodo IS NOT NULL AND d.periodo = :periodo))';
            $vars[':periodo'] = $filtros['periodo'];
            $vars[':fecha_desde'] = $fecha_desde;
            $vars[':fecha_hasta'] = $fecha_hasta;
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'd.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'd.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        // filtrar por montos
        if (!empty($filtros['total_desde'])) {
            $where[] = 'd.total >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'd.total <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        // entregar filtros
        return [$where, $vars];
    }

    /**
     * Entrega el total de documentos recibidos por el contribuyente.
     */
    public function countDocumentosRecibidos(array $filtros = []): int
    {
        list($where, $vars) = $this->crearFiltrosDocumentosRecibidos($filtros);
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT
                COUNT(*)
            FROM
                dte_recibido AS d
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN contribuyente AS e ON
                    d.emisor = e.rut
                JOIN usuario AS u ON
                    d.usuario = u.id
            WHERE
                '.implode(' AND ', $where).'
        ', $vars);
    }

    /**
     * Entrega el listado de documentos recibidos por el contribuyente.
     */
    public function getDocumentosRecibidos(array $filtros = []): array
    {
        list($where, $vars) = $this->crearFiltrosDocumentosRecibidos($filtros);
        // armar consulta
        $query = '
            SELECT
                d.emisor,
                e.razon_social,
                d.dte,
                t.tipo,
                d.folio,
                d.fecha,
                d.total,
                d.intercambio,
                u.usuario,
                d.emisor,
                d.mipyme
            FROM
                dte_recibido AS d
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN contribuyente AS e ON
                    d.emisor = e.rut
                JOIN usuario AS u ON
                    d.usuario = u.id
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY
                d.fecha DESC,
                t.tipo,
                e.razon_social
        ';
        // armar límite consulta
        if (isset($filtros['limit'])) {
            $query = $this->getDatabaseConnection()->setLimit(
                $query,
                $filtros['limit'],
                $filtros['offset']
            );
        }
        // entregar consulta
        return $this->getDatabaseConnection()->getTable($query, $vars);
    }

    /**
     * Entrega el resumen de las compras por períodos.
     */
    public function getResumenComprasPeriodos(): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'r.fecha', 'INTEGER');
        $periodo_col_46 = $this->getDatabaseConnection()->date('Ym', 'r.fecha_hora_creacion', 'INTEGER'); ///< se asume como período el de la creación de la FC
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    CASE WHEN r.periodo IS NOT NULL THEN
                        r.periodo
                    ELSE
                        CASE WHEN f.periodo IS NOT NULL THEN
                            f.periodo
                        ELSE
                            NULL
                        END
                    END AS periodo,
                    CASE WHEN r.recibidos IS NOT NULL AND f.facturas_compra IS NOT NULL THEN
                        r.recibidos + f.facturas_compra
                    ELSE
                        CASE WHEN r.recibidos IS NOT NULL THEN
                            r.recibidos
                        ELSE
                            f.facturas_compra
                        END
                    END AS recibidos,
                    c.documentos AS enviados,
                    c.track_id,
                    c.revision_estado
                FROM
                    (
                        SELECT
                            periodo,
                            COUNT(*) AS recibidos
                        FROM (
                            SELECT
                                CASE WHEN r.periodo IS NOT NULL THEN
                                    r.periodo
                                ELSE
                                    '.$periodo_col.'
                                END AS periodo
                            FROM
                                dte_recibido AS r
                                JOIN dte_tipo AS t ON
                                    t.codigo = r.dte
                            WHERE
                                t.compra = true
                                AND r.receptor = :rut
                                AND r.dte != 46 -- se quitan FC para evitar duplicidad con las que están en dte_emitidos
                                AND r.certificacion = :certificacion
                        ) AS t
                        GROUP BY periodo
                    ) AS r
                    FULL JOIN (
                        SELECT
                            '.$periodo_col_46.' AS periodo,
                            COUNT(*) AS facturas_compra
                        FROM
                            dte_emitido AS r
                        WHERE
                            r.emisor = :rut
                            AND r.certificacion = :certificacion
                            AND r.dte = 46
                        GROUP BY
                            '.$periodo_col_46.'
                    ) AS f ON
                        r.periodo = f.periodo
                    LEFT JOIN dte_compra AS c ON
                        c.receptor = :rut
                        AND c.certificacion = :certificacion
                        AND c.periodo IN (r.periodo, f.periodo)
            ) UNION (
                SELECT
                    periodo,
                    documentos AS recibidos,
                    documentos AS enviados,
                    track_id,
                    revision_estado
                FROM
                    dte_compra
                WHERE
                    receptor = :rut
                    AND certificacion = :certificacion
            )
            ORDER BY periodo DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ]);
    }

    /**
     * Entrega el total de las compras de un período.
     */
    public function countCompras($periodo): int
    {
        $fecha_desde = Utility_Date::normalize($periodo . '01');
        $fecha_hasta = Utility_Date::lastDayPeriod($periodo);
        $fechahora_hasta = $fecha_hasta . ' 23:59:59';
        $compras = $this->getDatabaseConnection()->getCol('
            (
                SELECT
                    COUNT(*)
                FROM
                    dte_tipo AS t
                    JOIN dte_recibido AS r ON
                        t.codigo = r.dte
                WHERE
                    t.compra = true
                    AND r.receptor = :rut
                    AND r.certificacion = :certificacion
                    AND r.dte != 46 -- se quitan FC para evitar duplicidad con las que están en dte_emitidos
                    AND (r.receptor, r.dte, r.folio, r.certificacion) NOT IN (
                        SELECT
                            r.emisor,
                            r.dte,
                            r.folio,
                            r.certificacion
                        FROM
                            dte_emitido AS r
                            JOIN dte_referencia AS re ON
                                re.emisor = r.emisor
                                AND re.dte = r.dte
                                AND re.folio = r.folio
                                AND re.certificacion = r.certificacion
                        WHERE
                            re.referencia_dte = 46
                    )
                    AND (
                        (
                            r.periodo IS NULL
                            AND r.fecha BETWEEN :fecha_desde AND :fecha_hasta
                        ) OR (
                            r.periodo IS NOT NULL
                            AND r.periodo = :periodo
                        )
                    )
            ) UNION (
                SELECT
                    COUNT(*)
                FROM
                    dte_tipo AS t
                    JOIN dte_emitido AS r ON
                        t.codigo = r.dte
                WHERE
                    r.emisor = :rut
                    AND r.certificacion = :certificacion
                    AND r.fecha_hora_creacion BETWEEN :fecha_desde AND :fechahora_hasta
                    AND (
                        r.dte = 46
                        OR (r.emisor, r.dte, r.folio, r.certificacion) IN (
                            SELECT
                                r.emisor,
                                r.dte,
                                r.folio,
                                r.certificacion
                            FROM
                                dte_emitido AS r
                                JOIN dte_referencia AS re ON
                                    re.emisor = r.emisor
                                    AND re.dte = r.dte
                                    AND re.folio = r.folio
                                    AND re.certificacion = r.certificacion
                            WHERE
                                re.referencia_dte = 46
                        )
                    )
            )
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
            ':fecha_desde' => $fecha_desde,
            ':fecha_hasta' => $fecha_hasta,
            ':fechahora_hasta' => $fechahora_hasta,
        ]);
        return (int)array_sum($compras);
    }

    /**
     * Entrega el resumen de las compras de un período.
     */
    public function getCompras($periodo, $tipo_dte = null): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'r.fecha', 'INTEGER');
        $periodo_col_46 = $this->getDatabaseConnection()->date('Ym', 'r.fecha_hora_creacion', 'INTEGER');
        list($impuesto_codigo, $impuesto_tasa, $impuesto_monto) = $this->getDatabaseConnection()->xml('r.xml', [
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TipoImp',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/TasaImp',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Totales/ImptoReten/MontoImp',
        ], 'http://www.sii.cl/SiiDte');
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ];
        if ($tipo_dte !== null) {
            if (is_array($tipo_dte)) {
                $where_tipo_dte = 'AND t.codigo IN (' . implode(', ', array_map('intval', $tipo_dte)) . ')';
            } else {
                $where_tipo_dte = 'AND t.electronico = :electronico';
                $vars[':electronico'] = (int)$tipo_dte;
            }
        } else {
            $where_tipo_dte = '';
        }
        $compras = $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    r.dte,
                    r.folio,
                    e.rut || \'-\' || e.dv AS rut,
                    r.tasa,
                    e.razon_social,
                    r.impuesto_tipo,
                    r.fecha,
                    r.anulado,
                    r.exento,
                    r.neto,
                    r.iva,
                    r.iva_no_recuperable,
                    NULL AS iva_no_recuperable_codigo,
                    NULL AS iva_no_recuperable_monto,
                    r.iva_uso_comun,
                    r.impuesto_adicional,
                    NULL AS impuesto_adicional_codigo,
                    NULL AS impuesto_adicional_tasa,
                    NULL AS impuesto_adicional_monto,
                    r.impuesto_sin_credito,
                    r.monto_activo_fijo,
                    r.monto_iva_activo_fijo,
                    r.iva_no_retenido,
                    r.impuesto_puros,
                    r.impuesto_cigarrillos,
                    r.impuesto_tabaco_elaborado,
                    r.impuesto_vehiculos,
                    r.sucursal_sii,
                    r.numero_interno,
                    r.emisor_nc_nd_fc,
                    r.total,
                    r.tipo_transaccion
                FROM
                    dte_recibido AS r
                    JOIN dte_tipo AS t ON
                        t.codigo = r.dte
                    JOIN contribuyente AS e ON
                        e.rut = r.emisor
                WHERE
                    r.receptor = :rut
                    '.$where_tipo_dte.'
                    AND r.certificacion = :certificacion
                    AND t.compra = true
                    AND r.dte != 46 -- se quitan FC para evitar duplicidad con las que están en dte_emitidos
                    AND (r.receptor, r.dte, r.folio, r.certificacion) NOT IN (
                        SELECT
                            r.emisor,
                            r.dte,
                            r.folio,
                            r.certificacion
                        FROM
                            dte_emitido AS r
                            JOIN dte_referencia AS re ON
                                re.emisor = r.emisor
                                AND re.dte = r.dte
                                AND re.folio = r.folio
                                AND re.certificacion = r.certificacion
                        WHERE
                            '.$periodo_col_46.' = :periodo
                            AND re.referencia_dte = 46
                    )
                    AND (
                        (
                            r.periodo IS NULL
                            AND '.$periodo_col.' = :periodo
                        ) OR (
                            r.periodo IS NOT NULL
                            AND r.periodo = :periodo
                        )
                    )
            ) UNION (
                SELECT
                    r.dte,
                    r.folio,
                    e.rut || \'-\' || e.dv AS rut,
                    r.tasa,
                    e.razon_social,
                    NULL AS impuesto_tipo,
                    r.fecha,
                    NULL AS anulado,
                    r.exento,
                    r.neto,
                    r.iva,
                    NULL AS iva_no_recuperable,
                    NULL AS iva_no_recuperable_codigo,
                    NULL AS iva_no_recuperable_monto,
                    NULL AS iva_uso_comun,
                    NULL AS impuesto_adicional,
                    '.$impuesto_codigo.' AS impuesto_adicional_codigo,
                    '.$impuesto_tasa.' AS impuesto_adicional_tasa,
                    '.$impuesto_monto.' AS impuesto_adicional_monto,
                    NULL AS impuesto_sin_credito,
                    NULL AS monto_activo_fijo,
                    NULL AS monto_iva_activo_fijo,
                    NULL AS iva_no_retenido,
                    NULL AS impuesto_puros,
                    NULL AS impuesto_cigarrillos,
                    NULL AS impuesto_tabaco_elaborado,
                    NULL AS impuesto_vehiculos,
                    NULL AS sucursal_sii,
                    NULL AS numero_interno,
                    CASE WHEN r.dte IN (56, 61) THEN
                        1
                    ELSE
                        NULL
                    END AS emisor_nc_nd_fc,
                    r.total,
                    NULL AS tipo_transaccion
                FROM
                    dte_emitido AS r
                    JOIN dte_tipo AS t ON
                        t.codigo = r.dte
                    JOIN contribuyente AS e ON
                        e.rut = r.receptor
                WHERE
                    r.emisor = :rut
                    '.$where_tipo_dte.'
                    AND r.certificacion = :certificacion
                    AND '.$periodo_col_46.' = :periodo
                    AND (
                        r.dte = 46
                        OR (r.emisor, r.dte, r.folio, r.certificacion) IN (
                            SELECT
                                r.emisor,
                                r.dte,
                                r.folio,
                                r.certificacion
                            FROM
                                dte_emitido AS r
                                JOIN dte_referencia AS re ON
                                    re.emisor = r.emisor
                                    AND re.dte = r.dte
                                    AND re.folio = r.folio
                                    AND re.certificacion = r.certificacion
                            WHERE
                                '.$periodo_col_46.' = :periodo
                                AND re.referencia_dte = 46
                        )
                    )
            )
            ORDER BY
                fecha,
                dte,
                folio
        ', $vars);
        // procesar cada compra
        foreach ($compras as &$c) {
            // asignar IVA no recuperable
            if ($c['iva_no_recuperable']) {
                $iva_no_recuperable = json_decode($c['iva_no_recuperable'], true);
                $iva_no_recuperable_codigo = [];
                $iva_no_recuperable_monto = [];
                foreach ($iva_no_recuperable as $inr) {
                    $iva_no_recuperable_codigo[] = $inr['codigo'];
                    $iva_no_recuperable_monto[] = $inr['monto'];
                    $c['iva'] -= $inr['monto'];
                }
                $c['iva_no_recuperable_codigo'] = implode(',', $iva_no_recuperable_codigo);
                $c['iva_no_recuperable_monto'] = implode(',', $iva_no_recuperable_monto);
            }
            unset($c['iva_no_recuperable']);
            // asignar monto de impuesto adicional
            if ($c['impuesto_adicional']) {
                $impuesto_adicional = json_decode($c['impuesto_adicional'], true);
                $impuesto_adicional_codigo = [];
                $impuesto_adicional_tasa = [];
                $impuesto_adicional_monto = [];
                foreach ($impuesto_adicional as $ia) {
                    $impuesto_adicional_codigo[] = $ia['codigo'];
                    $impuesto_adicional_tasa[] = $ia['tasa'];
                    $impuesto_adicional_monto[] = $ia['monto'];
                }
                $c['impuesto_adicional_codigo'] = implode(',', $impuesto_adicional_codigo);
                $c['impuesto_adicional_tasa'] = implode(',', $impuesto_adicional_tasa);
                $c['impuesto_adicional_monto'] = implode(',', $impuesto_adicional_monto);
            }
            unset($c['impuesto_adicional']);
            // asignar factor de proporcionalidad
            $c['iva_uso_comun_factor'] = $c['iva_uso_comun']
                ? round(($c['iva_uso_comun'] / $c['iva']) * 100)
                : null
            ;
        }
        return $compras;
    }

    /**
     * Entrega el objeto del libro de compras a partir de las
     * compras registradas en la aplicación.
     * @param int $periodo Período para el cual se está construyendo el libro.
     */
    public function getLibroCompras($periodo)
    {
        $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $compras = $this->getCompras($periodo);
        foreach ($compras as $compra) {
            // armar detalle para agregar al libro
            $d = [];
            foreach ($compra as $k => $v) {
                if (
                    strpos($k, 'impuesto_adicional') !== 0
                    && strpos($k, 'iva_no_recuperable') !== 0
                    && $v !== null
                    && isset(Model_DteCompra::$libro_cols[$k])
                ) {
                    $d[Model_DteCompra::$libro_cols[$k]] = $v;
                }
            }
            // agregar iva no recuperable
            if (!empty($compra['iva_no_recuperable_codigo'])) {
                $d['IVANoRec'] = [
                    'CodIVANoRec' => $compra['iva_no_recuperable_codigo'],
                    'MntIVANoRec' => $compra['iva_no_recuperable_monto'],
                ];
            }
            // agregar otros impuestos
            if (!empty($compra['impuesto_adicional_codigo'])) {
                $d['OtrosImp'] = [
                    'CodImp' => $compra['impuesto_adicional_codigo'],
                    'TasaImp' => $compra['impuesto_adicional_tasa']
                        ? $compra['impuesto_adicional_tasa']
                        : 0
                    ,
                    'MntImp' => $compra['impuesto_adicional_monto'],
                ];
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        return $Libro;
    }

    /**
     * Entrega el resumen de las compras diarias de un período.
     */
    public function getComprasDiarias($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'r.fecha');
        $dia_col = $this->getDatabaseConnection()->date('d', 'r.fecha');
        $periodo_col_46 = $this->getDatabaseConnection()->date('Ym', 'r.fecha_hora_creacion');
        $dia_col_46 = $this->getDatabaseConnection()->date('d', 'r.fecha_hora_creacion');
        return $this->getDatabaseConnection()->getTable('
            SELECT
                CASE WHEN r.dia IS NOT NULL THEN
                    r.dia
                ELSE
                    CASE WHEN f.dia IS NOT NULL THEN
                        f.dia
                    ELSE
                        NULL
                    END
                END AS dia,
                CASE WHEN r.documentos IS NOT NULL AND f.documentos IS NOT NULL THEN
                    r.documentos + f.documentos
                ELSE
                    CASE WHEN r.documentos IS NOT NULL THEN
                        r.documentos
                    ELSE
                        f.documentos
                    END
                END AS documentos
            FROM
                (
                    SELECT
                        '.$dia_col.' AS dia,
                        COUNT(*) AS documentos
                    FROM
                        dte_recibido AS r
                        JOIN dte_tipo AS t ON
                            t.codigo = r.dte
                    WHERE
                        t.compra = true
                        AND r.receptor = :rut
                        AND r.dte != 46 -- se quitan FC para evitar duplicidad con las que están en dte_emitidos
                        AND r.certificacion = :certificacion
                        AND '.$periodo_col.' = :periodo
                    GROUP BY
                        r.fecha
                ) AS r
                FULL JOIN
                (
                    SELECT
                        '.$dia_col_46.' AS dia,
                        COUNT(*) AS documentos
                    FROM
                        dte_emitido AS r
                    WHERE
                        r.emisor = :rut
                        AND r.certificacion = :certificacion
                        AND '.$periodo_col_46.' = :periodo
                        AND r.dte = 46
                    GROUP BY
                        r.fecha_hora_creacion
                ) AS f ON
                    r.dia = f.dia
            ORDER BY
                dia
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el resumen de compras por tipo de un período.
     * @return array Arreglo asociativo con las compras.
     */
    public function getComprasPorTipo($periodo): array
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'r.fecha');
        $periodo_col_46 = $this->getDatabaseConnection()->date('Ym', 'r.fecha_hora_creacion');
        return $this->getDatabaseConnection()->getTable('
            (
                SELECT
                    t.tipo,
                    COUNT(*) AS documentos
                FROM
                    dte_recibido AS r
                    JOIN dte_tipo AS t ON
                        t.codigo = r.dte
                WHERE
                    t.compra = true
                    AND r.receptor = :rut
                    AND r.dte != 46 -- se quitan FC para evitar duplicidad con las que están en dte_emitidos
                    AND r.certificacion = :certificacion
                    AND '.$periodo_col.' = :periodo
                GROUP BY
                    t.tipo
            ) UNION (
                SELECT
                    t.tipo,
                    COUNT(*) AS facturas_compra
                FROM
                    dte_emitido AS r
                    JOIN dte_tipo AS t ON
                        t.codigo = r.dte
                WHERE
                    r.emisor = :rut
                    AND r.certificacion = :certificacion
                    AND r.dte = 46
                    AND '.$periodo_col_46.' = :periodo
                GROUP BY
                    t.tipo
            )
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Entrega el listado de documentos electrónicos que han
     * sido generados pero no se han enviado al SII.
     */
    public function getDteEmitidosSinEnviar(?int $certificacion = null, int $creados_hace_horas = 0): array
    {
        $certificacion = (int)($certificacion !== null
            ? $certificacion
            : $this->enCertificacion()
        );
        return $this->getDatabaseConnection()->getTable('
            SELECT
                dte,
                folio
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND (
                    dte NOT IN (39, 41)
                    OR (
                        dte IN (39, 41)
                        AND fecha >= :envio_boleta
                    )
                )
                AND (
                    track_id IS NULL
                    OR track_id = 0
                )
                AND NOW() AT TIME ZONE \'America/Santiago\' >= (
                    fecha_hora_creacion + interval \'1h\' * :creados_hace_horas
                )
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $certificacion,
            ':envio_boleta' => Model_DteEmitidos::ENVIO_BOLETA,
            ':creados_hace_horas' => (int)$creados_hace_horas,
        ]);
    }

    /**
     * Entrega el listado de documentos electrónicos que han sido
     * generados y enviados al SII pero aun no se ha actualizado su estado.
     */
    public function getDteEmitidosSinEstado(?int $certificacion = null): array
    {
        $certificacion = (int)($certificacion !== null
            ? $certificacion
            : $this->enCertificacion()
        );
        $estados_no_final = implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']);
        return $this->getDatabaseConnection()->getTable('
            SELECT
                dte,
                folio
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND (
                    dte NOT IN (39, 41)
                    OR (
                        dte IN (39, 41)
                        AND fecha >= :envio_boleta
                    )
                )
                AND track_id > 0
                AND (
                    revision_estado IS NULL
                    OR revision_estado LIKE \'-%\'
                    OR SUBSTRING(revision_estado FROM 1 FOR 3) IN (
                        \'' . $estados_no_final . '\'
                    )
                )
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $certificacion,
            ':envio_boleta' => Model_DteEmitidos::ENVIO_BOLETA,
        ]);
    }

    /**
     * Entrega el listado de sucursales del contribuyente con los
     * codigos de actividad económica asociados a cada una (uno por sucursal).
     */
    public function getSucursalesActividades()
    {
        $actividades = [0 => $this->actividad_economica];
        if ($this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $sucursal) {
                $actividades[$sucursal->codigo] = $sucursal->actividad_economica
                    ? $sucursal->actividad_economica
                    : $this->actividad_economica
                ;
            }
        }
        return $actividades;
    }

    /**
     * Entrega el listado de sucursales del contribuyente,
     * se incluye la casa matriz.
     */
    public function getSucursales(): array
    {
        $sucursales = [
            0 => 'Casa matriz (' . $this->direccion . ', ' . $this->getComuna()->comuna . ')',
        ];
        if ($this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $sucursal) {
                $comuna = (new Model_Comunas())
                    ->get($sucursal->comuna)
                    ->comuna
                ;
                $sucursales[$sucursal->codigo] = $sucursal->sucursal
                    . ' (' . $sucursal->direccion . ', ' . $comuna . ')'
                ;
            }
        }
        return $sucursales;
    }

    /**
     * Entrega el objeto de la sucursal del contribuyente a partir
     * del código de la sucursal (por defecto casa matriz).
     */
    public function getSucursal($codigo = null)
    {
        $encontrada = false;
        // si se pasó código se busca sucursal
        if ($codigo && $this->config_extra_sucursales) {
            foreach ($this->config_extra_sucursales as $Sucursal) {
                if ($Sucursal->codigo == $codigo) {
                    $encontrada = true;
                    break;
                }
            }
        }
        // si no se pasó código o no se encontró se entrega sucursal matriz
        if (!$encontrada) {
            $Sucursal = (object)[
                'codigo' => 0,
                'sucursal' => 'Casa matriz',
                'direccion' => $this->direccion,
                'comuna' => $this->comuna,
                'actividad_economica' => $this->actividad_economica,
            ];
        }
        // agregar datos de provincia y región a la sucursal
        $Sucursal->provincia = substr($Sucursal->comuna, 0, 3);
        $Sucursal->region = substr($Sucursal->comuna, 0, 2);
        // entregar objeto de la sucursal
        return $Sucursal;
    }

    /**
     * Entrega la sucursal del usuario indicado.
     */
    public function getSucursalUsuario($Usuario)
    {
        // obtener desde la tabla de usuarios y sucursales del contribuyente
        $sucursal = $this->getDatabaseConnection()->getValue('
            SELECT sucursal_sii
            FROM contribuyente_usuario_sucursal
            WHERE contribuyente = :rut AND usuario = :usuario
        ', [
            ':rut' => $this->rut,
            ':usuario' => $Usuario->id,
        ]);
        if ($sucursal) {
            return $sucursal;
        }
        // Obtener desde el usuario, permite obtener mediante un trigger
        // desde otro módulo.
        return method_exists($Usuario, 'getSucursal')
            ? $Usuario->getSucursal($this)
            : null
        ;
    }

    /**
     * Asigna las sucursales por defecto de los usuarios.
     */
    public function setSucursalesPorUsuario(array $usuarios = [])
    {
        $this->getDatabaseConnection()->beginTransaction();
        // Se eliminan todas las sucursales, para dejar solo lo que
        // viene en el arreglo.
        $this->getDatabaseConnection()->executeRawQuery('
            DELETE
            FROM contribuyente_usuario_sucursal
            WHERE contribuyente = :rut
        ', [':rut' => $this->rut]);
        // se agregan las sucursales por defecto
        foreach ($usuarios as $usuario => $sucursal) {
            $Usuario = new Model_Usuario($usuario);
            if (!$Usuario->exists()) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('Usuario '.$usuario.' no existe.');
                return false;
            }
            $Sucursal = $this->getSucursal($sucursal);
            if (!$Sucursal->codigo) {
                continue;
            }
            $this->getDatabaseConnection()->executeRawQuery('
                INSERT INTO contribuyente_usuario_sucursal
                VALUES (:rut, :usuario, :sucursal)
            ', [
                ':rut' => $this->rut,
                ':usuario' => $Usuario->id,
                ':sucursal' => $Sucursal->codigo,
            ]);
        }
        return $this->getDatabaseConnection()->commit();
    }

    /**
     * Obtiene las sucursales por defecto de los usuarios.
     */
    public function getSucursalesPorUsuario(): array
    {
        return $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            SELECT
                u.usuario,
                s.sucursal_sii
            FROM
                contribuyente_usuario_sucursal AS s
                JOIN usuario AS u ON
                    u.id = s.usuario
            WHERE
                contribuyente = :rut
        ', [':rut' => $this->rut]);
    }

    /**
     * Entrega las coordenadas geográficas del emisor según su dirección.
     */
    public function getCoordenadas($sucursal = null)
    {
        $Sucursal = $this->getSucursal($sucursal);
        $direccion = $Sucursal->direccion
            . ', ' . (new Model_Comuna($Sucursal->comuna))->comuna
        ;
        return (new Utility_Mapas_Google())->getCoordenadas($direccion);
    }

    /**
     * Entrega el listado de clientes del contribuyente.
     */
    public function getClientes(array $filtros = []): array
    {
        // Si es edición enterprise se saca del CRM.
        if (libredte()->isEnterpriseEdition()) {
            return (new \libredte\enterprise\Crm\Model_Clientes())
                ->setContribuyente($this)
                ->getListado($filtros)
            ;
        }
        // Si es edición comunidad se sacan los clientes de los DTE emitidos.
        else {
            return $this->getDatabaseConnection()->getTable('
                SELECT DISTINCT
                    c.rut,
                    c.dv,
                    c.razon_social,
                    c.telefono,
                    c.email,
                    c.direccion,
                    co.comuna,
                    NULL AS codigo_interno,
                    c.giro
                FROM
                    contribuyente AS c
                    JOIN dte_emitido AS d ON
                        d.receptor = c.rut
                    LEFT JOIN comuna AS co ON
                        co.codigo = c.comuna
                WHERE
                    d.emisor = :emisor
                    AND d.receptor NOT IN (55555555, 66666666)
                    AND d.certificacion = :certificacion
                ORDER BY
                    c.razon_social
            ', [
                ':emisor' => $this->rut,
                ':certificacion' => $this->enCertificacion(),
            ]);
        }
    }

    /**
     * Entrega la cuota de documentos asignada al contribuyente.
     */
    public function getCuota(): int
    {
        return (int)$this->config_libredte_cuota;
    }

    /**
     * Entrega los documentos usados por el contribuyente.
     * Ya sea en todos los períodos o en uno en específico.
     */
    public function getDocumentosUsados($periodo = null): array
    {
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
        ];
        // columnas de periodos
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha_hora_creacion');
        $intercambio_periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha_hora_email');
        // listado de periodos
        if ($periodo) {
            $periodos = [$periodo];
        } else {
            // WARNING al ser muchos períodos (casos raros donde un cliente emitió con período
            // del año 2130) sale: "ERROR:  stack depth limit exceeded" (error de SQL)
            // por eso se usa un período mínimo base que es enero del 2016 y un periodo máximo
            // que es el período siguiente al actual.
            // el error ocurre por la gran cantidad de UNION que aparecen
            $periodo_actual = date('Ym');
            $periodos_min = array_filter($this->getDatabaseConnection()->getCol('
                (
                    SELECT MIN('.$periodo_col.')
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte NOT IN (39,41)
                ) UNION (
                    SELECT MIN('.$periodo_col.')
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte IN (39,41)
                ) UNION (
                    SELECT MIN('.$periodo_col.')
                    FROM dte_recibido
                    WHERE
                        receptor = :rut
                        AND certificacion = :certificacion
                        AND emisor = 1
                )
            ', $vars));
            $periodo_min = max(
                $periodos_min ? min($periodos_min) : $periodo_actual,
                201601
            );
            $periodos_max = array_filter($this->getDatabaseConnection()->getCol('
                (
                    SELECT MAX('.$periodo_col.')
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte NOT IN (39,41)
                ) UNION (
                    SELECT MAX('.$periodo_col.')
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte IN (39,41)
                ) UNION (
                    SELECT MAX('.$periodo_col.')
                    FROM dte_recibido
                    WHERE
                        receptor = :rut
                        AND certificacion = :certificacion
                        AND emisor = 1
                )
            ', $vars));
            $periodo_max = min(
                $periodos_max ? max($periodos_max) : $periodo_actual,
                Utility_Date::nextPeriod($periodo_actual)
            );
            $periodos = [];
            $p_aux = $periodo_min;
            do {
                $periodos[] = $p_aux;
                $p_aux = Utility_Date::nextPeriod($p_aux);
            } while($p_aux <= $periodo_max);
        }
        // consulta SQL
        if ($periodo) {
            $periodo_where = ' AND '.$periodo_col.' = :periodo';
            $intercambio_periodo_where = ' AND '.$intercambio_periodo_col.' = :periodo';
            $vars[':periodo'] = $periodo;
        } else {
            $periodo_where = $intercambio_periodo_where = '';
        }
        $periodos = array_map(
            function($p) { return '(SELECT '.$p.' AS periodo)'; },
            $periodos
        );
        $datos = $this->getDatabaseConnection()->getTable('
            SELECT
                p.periodo,
                e.total AS emitidos,
                b.total AS boletas,
                r.total AS recibidos,
                i.total AS intercambios
            FROM
                (
                    SELECT periodo::TEXT
                    FROM ('.implode(' UNION ', $periodos).') AS t
                ) AS p
                LEFT JOIN (
                    SELECT '.$periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte NOT IN (39,41) '.$periodo_where.'
                    GROUP BY '.$periodo_col.'
                ) AS e ON e.periodo = p.periodo
                LEFT JOIN (
                    SELECT '.$periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_emitido
                    WHERE
                        emisor = :rut
                        AND certificacion = :certificacion
                        AND dte IN (39,41) '.$periodo_where.'
                    GROUP BY '.$periodo_col.'
                ) AS b ON b.periodo = p.periodo
                LEFT JOIN (
                    SELECT '.$periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_recibido
                    WHERE
                        receptor = :rut
                        AND certificacion = :certificacion '.$periodo_where.'
                    GROUP BY '.$periodo_col.'
                ) AS r ON r.periodo = p.periodo
                LEFT JOIN (
                    SELECT '.$intercambio_periodo_col.' AS periodo, COUNT(*) AS total
                    FROM dte_intercambio
                    WHERE
                        receptor = :rut
                        AND certificacion = :certificacion '.$intercambio_periodo_where.'
                    GROUP BY '.$intercambio_periodo_col.'
                ) AS i ON i.periodo = p.periodo
            ORDER BY periodo DESC
        ', $vars);
        foreach ($datos as &$d) {
            $d['total'] = $d['emitidos'] + $d['boletas'] + $d['recibidos'];
        }
        if ($periodo) {
            return !empty($datos) ? $datos[0] : [
                'periodo' => 0,
                'emitidos' => 0,
                'boletas' => 0,
                'recibidos' => 0,
                'intercambios' => 0,
                'total' => 0,
            ];
        }
        return $datos;
    }

    /**
     * Entrega el total de documentos usados por el
     * contribuyente en un periodo en particular.
     */
    public function getTotalDocumentosUsadosPeriodo($periodo = null): int
    {
        if (!$periodo) {
            $periodo = date('Ym');
        }
        return $this->getDocumentosUsados($periodo)['total'];
    }

    /**
     * Entrega el resumen de los estados de los DTE para un
     * periodo de tiempo.
     */
    public function getDocumentosEmitidosResumenEstados(string $desde, string $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                revision_estado AS estado,
                COUNT(*) AS total
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND track_id > 0
            GROUP BY
                revision_estado
            ORDER BY
                total DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega el resumen diario de los documentos emitidos.
     */
    public function getDocumentosEmitidosResumenDiario(array $filtros): array
    {
        if (empty($filtros['periodo'])) {
            $filtros['periodo'] = date('Ym');
        }
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha');
        $where = [
            'emisor = :rut',
            'certificacion = :certificacion',
            $periodo_col . ' = :periodo',
        ];
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':periodo' => $filtros['periodo'],
        ];
        if (!empty($filtros['dtes'])) {
            if (!is_array($filtros['dtes'])) {
                [$filtros['dtes']] = [$filtros['dtes']];
            }
            $where[] = 'dte IN (' . implode(', ', array_map('intval', $filtros['dtes'])) . ')';
        }
        return $this->getDatabaseConnection()->getTable('
            SELECT
                fecha,
                COUNT(folio) AS emitidos,
                MIN(folio) AS desde,
                MAX(folio) AS hasta,
                SUM(exento) AS exento,
                SUM(neto) AS neto,
                SUM(iva) AS iva,
                SUM(total) AS total
            FROM
                dte_emitido
            WHERE
                '.implode(' AND ', $where).'
                AND dte != 46
            GROUP BY
                fecha
            ORDER BY
                fecha
        ', $vars);
    }

    /**
     * Entrega el detalle de los documentos emitidos con
     * cierto estado en un rango de tiempo.
     */
    public function getDocumentosEmitidosEstado(string $desde, string $hasta, ?string $estado = null): array
    {
        // filtros
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ];
        if ($estado) {
            $vars[':estado'] = $estado;
            $estado = 'd.revision_estado = :estado';
        } else {
            $estado = 'd.revision_estado IS NULL';
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'd.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN d.receptor NOT IN (55555555, 66666666) THEN r.razon_social ELSE '
            . $razon_social_xpath . ' END AS razon_social'
        ;
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                '.$razon_social.',
                d.fecha,
                d.total,
                d.track_id,
                d.revision_detalle AS estado_detalle,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN usuario AS u ON
                    d.usuario = u.id
                LEFT JOIN dte_intercambio_resultado_dte AS i ON
                    i.emisor = d.emisor
                    AND i.dte = d.dte
                    AND i.folio = d.folio
                    AND i.certificacion = d.certificacion
            WHERE
                d.emisor = :rut
                AND d.certificacion = :certificacion
                AND d.fecha BETWEEN :desde AND :hasta
                AND d.track_id > 0
                AND '.$estado.'
            ORDER BY
                d.fecha DESC,
                t.tipo,
                d.folio DESC
        ', $vars);
    }

    /**
     * Entrega el resumen de los eventos asignados por los
     * receptores para un periodo de tiempo.
     */
    public function getDocumentosEmitidosResumenEventos(string $desde, string $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                receptor_evento AS evento,
                COUNT(*) AS total
            FROM
                dte_emitido
            WHERE
                emisor = :rut
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte IN (33, 34, 43)
            GROUP BY
                receptor_evento
            ORDER BY
                total DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega el detalle de los documentos emitidos con
     * cierto evento en un rango de tiempo.
     */
    public function getDocumentosEmitidosEvento(string $desde, string $hasta, ?string $evento = null): array
    {
        // filtros
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ];
        if ($evento) {
            $vars[':evento'] = $evento;
            $evento = 'd.receptor_evento = :evento';
        } else {
            $evento = 'd.receptor_evento IS NULL';
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'd.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN d.receptor NOT IN (55555555, 66666666) THEN r.razon_social ELSE '
            . $razon_social_xpath . ' END AS razon_social'
        ;
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                '.$razon_social.',
                d.fecha,
                d.total,
                d.revision_detalle AS estado_detalle,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN usuario AS u ON
                    d.usuario = u.id
                LEFT JOIN dte_intercambio_resultado_dte AS i ON
                    i.emisor = d.emisor
                    AND i.dte = d.dte
                    AND i.folio = d.folio
                    AND i.certificacion = d.certificacion
            WHERE
                d.emisor = :rut
                AND d.certificacion = :certificacion
                AND d.fecha BETWEEN :desde AND :hasta
                AND d.dte IN (33, 34, 43)
                AND '.$evento.'
            ORDER BY
                d.fecha DESC,
                t.tipo,
                d.folio DESC
        ', $vars);
    }

    /**
     * Entrega el detalle de los documentos emitidos que aun
     * no han sido enviado al SII.
     */
    public function getDocumentosEmitidosSinEnviar(): array
    {
        // forma de obtener razón social
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'd.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN d.receptor NOT IN (55555555, 66666666) THEN r.razon_social ELSE '
            . $razon_social_xpath . ' END AS razon_social'
        ;
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                '.$razon_social.',
                d.fecha,
                d.total,
                i.glosa AS intercambio,
                d.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS d
                JOIN contribuyente AS r ON
                    d.receptor = r.rut
                JOIN dte_tipo AS t ON
                    d.dte = t.codigo
                JOIN usuario AS u ON
                    d.usuario = u.id
                LEFT JOIN dte_intercambio_resultado_dte AS i ON
                    i.emisor = d.emisor
                    AND i.dte = d.dte
                    AND i.folio = d.folio
                    AND i.certificacion = d.certificacion
            WHERE
                d.emisor = :rut
                AND d.certificacion = :certificacion
                AND (d.dte NOT IN (39, 41) OR (d.dte IN (39, 41) AND d.fecha >= :envio_boleta))
                AND (d.track_id IS NULL OR d.track_id = 0)
                AND d.xml IS NOT NULL
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':envio_boleta' => Model_DteEmitidos::ENVIO_BOLETA,
        ]);
    }

    /**
     * Entrega el resumen de los estados de los DTE para un
     * periodo de tiempo.
     */
    public function getDocumentosEmitidosResumenEstadoIntercambio(string $desde, string $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                CASE WHEN recibo.responde IS NOT NULL THEN
                    true
                ELSE
                    false
                END AS recibo,
                recepcion.estado AS recepcion,
                resultado.estado  AS resultado,
                COUNT(*) AS total
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_recibo_dte AS recibo ON
                    recibo.emisor = e.emisor
                    AND recibo.dte = e.dte
                    AND recibo.folio = e.folio
                    AND recibo.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_recepcion_dte AS recepcion ON
                    recepcion.emisor = e.emisor
                    AND recepcion.dte = e.dte
                    AND recepcion.folio = e.folio
                    AND recepcion.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_resultado_dte AS resultado ON
                    resultado.emisor = e.emisor
                    AND resultado.dte = e.dte
                    AND resultado.folio = e.folio
                    AND resultado.certificacion = e.certificacion
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.track_id > 0
                AND  e.revision_estado IS NOT NULL
            GROUP BY
                recibo,
                recepcion,
                resultado
            ORDER BY
                 total DESC
        ', [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega los estados de los DTE para un periodo de tiempo.
     */
    public function getDocumentosEmitidosEstadoIntercambio(string $desde, string $hasta, $recibo, $recepcion, $resultado): array
    {
        // filtros
        $vars = [
            ':rut' => $this->rut,
            ':certificacion' => $this->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ];
        $where = [
            $recibo
                ? 'recibo.responde IS NOT NULL'
                : 'recibo.responde IS NULL'
            ,
        ];
        if ($recepcion !== null && $recepcion != -1) {
            $where[] = 'recepcion.estado = :recepcion';
            $vars[':recepcion'] = $recepcion;
        } else {
            $where[] = 'recepcion.estado IS NULL';
        }
        if ($resultado !== null && $resultado != -1) {
            $where[] = 'resultado.estado = :resultado';
            $vars[':resultado'] = $resultado;
        } else {
            $where[] = 'resultado.estado IS NULL';
        }
        // forma de obtener razón social
        $razon_social_xpath = $this->getDatabaseConnection()->xml(
            'e.xml',
            '/*/SetDTE/DTE/*/Encabezado/Receptor/RznSocRecep',
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN e.receptor NOT IN (55555555, 66666666) THEN r.razon_social ELSE '
            . $razon_social_xpath . ' END AS razon_social'
        ;
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                e.dte,
                t.tipo,
                e.folio,
                '.$razon_social.',
                e.fecha,
                e.total,
                e.revision_estado,
                e.sucursal_sii,
                u.usuario
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_recibo_dte AS recibo ON
                    recibo.emisor = e.emisor
                    AND recibo.dte = e.dte
                    AND recibo.folio = e.folio
                    AND recibo.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_recepcion_dte AS recepcion ON
                    recepcion.emisor = e.emisor
                    AND recepcion.dte = e.dte
                    AND recepcion.folio = e.folio
                    AND recepcion.certificacion = e.certificacion
                LEFT JOIN dte_intercambio_resultado_dte AS resultado ON
                    resultado.emisor = e.emisor
                    AND resultado.dte = e.dte
                    AND resultado.folio = e.folio
                    AND resultado.certificacion = e.certificacion
                JOIN dte_tipo AS t ON
                    t.codigo = e.dte
                JOIN contribuyente AS r ON
                    r.rut = e.receptor
                JOIN usuario AS u ON
                    u.id = e.usuario
            WHERE
                e.emisor = :rut
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.track_id > 0
                AND  e.revision_estado IS NOT NULL
                AND ' . implode(' AND ', $where) . '
            ORDER BY
                e.fecha DESC,
                t.tipo,
                e.folio DESC
        ', $vars);
    }

    /**
     * Entrega la información del registro de compra y venta
     * del SII del contribuyente.
     */
    public function getRCV(array $filtros = []): array
    {
        // definir autenticación
        try {
            $auth = $this->getSiiAuth();
        } catch (\Exception $e) {
            $auth = $this->getSiiAuthUser();
        }
        // filtros por defecto
        $filtros = array_merge([
            'detalle' => true,
            'operacion' => 'COMPRA',
            'estado' => 'REGISTRO',
            'periodo' => date('Ym'),
            'dte' => null,
            // tipo de archivo a usar cuando se pide el detalle de
            // documentos (rcv, iecv o rcv_csv)
            'tipo' => 'rcv',
            // formato en el que se debe entregar la respuesta, siempre
            // debería ser json, excepto si es rcv_csv que podría ser csv
            'formato' => 'json',
        ], $filtros);
        // si se pide el detalle pero no se indicó el tipo de documento
        // se buscan todos los posible
        if ($filtros['detalle'] === true) {
            // si no se indicó dte se colocan todos los posibles
            if (!$filtros['dte']) {
                // si se solicita el ripo rcv_csv no se indican los DTE;
                // se obtiene todo por defecto
                if ($filtros['tipo'] == 'rcv_csv') {
                    $filtros['dte'] = [0];
                }
                // si es tipo rcv o iecv se deben buscar los posibles
                // tipos de documentos
                else {
                    $dtes = [];
                    $resumen = $this->getRCV([
                        'operacion' => $filtros['operacion'],
                        'periodo' => $filtros['periodo'],
                        'estado' => $filtros['estado'],
                        'detalle' => false,
                    ]);
                    foreach ($resumen as $r) {
                        if ($r['rsmnTotDoc']) {
                            $dtes[] = $r['rsmnTipoDocInteger'];
                        }
                    }
                    $filtros['dte'] = $dtes;
                }
            }
            // si el dte es solo uno se coloca como arreglo
            else if (!is_array($filtros['dte'])) {
                $filtros['dte'] = [$filtros['dte']];
            }
        }
        // errores
        $errores = [
            1 => 'Error de negocio',
            2 => 'Error de aplicación',
            3 => 'Sin datos',
            99 => 'Consulta no válida',
        ];
        // consumir servicio web de resumen
        if (!$filtros['detalle']) {
            if ($filtros['operacion'] == 'COMPRA') {
                $url = sprintf(
                    '/sii/rcv/compras/resumen/%d-%s/%d/%s?formato=json&certificacion=%d',
                    $this->rut,
                    $this->dv,
                    $filtros['periodo'],
                    $filtros['estado'],
                    $this->enCertificacion()
                );
            } else {
                $url = sprintf(
                    '/sii/rcv/ventas/resumen/%d-%s/%d?formato=json&certificacion=%d',
                    $this->rut,
                    $this->dv,
                    $filtros['periodo'],
                    $this->enCertificacion()
                );
            }
            $r = apigateway($url, ['auth' => $auth]);
            if ($r['status']['code'] != 200) {
                throw new \Exception('Error al obtener el resumen del RCV: '.$r['body']);
            }
            if ($r['body']['respEstado']['codRespuesta']) {
                $error = isset($errores[$r['body']['respEstado']['codRespuesta']])
                    ? $errores[$r['body']['respEstado']['codRespuesta']]
                    : ('Código ' . $r['body']['respEstado']['codRespuesta'])
                ;
                if ($error == 'Sin datos') {
                    return [];
                }
                throw new \Exception(
                    'No fue posible obtener el resumen: '
                        . $r['body']['respEstado']['msgeRespuesta']
                        . ' (' . $error . ').'
                );
            }
            return $r['body']['data'];
        }
        // consumir servicio web de detalle
        else {
            $detalle = [];
            foreach ($filtros['dte'] as $dte) {
                if ($filtros['operacion'] == 'COMPRA') {
                    $url = sprintf(
                        '/sii/rcv/compras/detalle/%d-%s/%d/%d/%s?formato='
                            . $filtros['formato'] . '&certificacion=%d&tipo=%s'
                        ,
                        $this->rut,
                        $this->dv,
                        $filtros['periodo'],
                        $dte,
                        $filtros['estado'],
                        $this->enCertificacion(),
                        $filtros['tipo']
                    );
                } else {
                    $url = sprintf(
                        '/sii/rcv/ventas/detalle/%d-%s/%d/%d?formato='
                            . $filtros['formato'] . '&certificacion=%d&tipo=%s'
                        ,
                        $this->rut,
                        $this->dv,
                        $filtros['periodo'],
                        $dte,
                        $this->enCertificacion(),
                        $filtros['tipo']
                    );
                }
                $r = apigateway($url, ['auth' => $auth]);
                if ($r['status']['code'] != 200) {
                    throw new \Exception('Error al obtener el detalle del RCV: ' . $r['body']);
                }
                if ($filtros['tipo'] == 'rcv_csv') {
                    return $r['body'];
                } else {
                    if ($r['body']['respEstado']['codRespuesta']) {
                        $error = isset($errores[$r['body']['respEstado']['codRespuesta']])
                            ? $errores[$r['body']['respEstado']['codRespuesta']]
                            : ('Código ' . $r['body']['respEstado']['codRespuesta'])
                        ;
                        throw new \Exception(
                            'No fue posible obtener el detalle: '
                                . $r['body']['respEstado']['msgeRespuesta']
                                . ' (' . $error . ').'
                        );
                    }
                    $detalle = array_merge($detalle, $r['body']['data']);
                }
            }
            return $detalle;
        }
    }

    /**
     * Entrega la configuración de cierta API (servicio web)
     * del contribuyente.
     */
    public function getAPI($api)
    {
        return ($this->config_api_servicios && isset($this->config_api_servicios->$api))
            ? $this->config_api_servicios->$api
            : false
        ;
    }

    /**
     * Entrega el cliente para la API del contribuyente.
     */
    public function getApiClient($api)
    {
        $Api = $this->getAPI($api);
        if (!$Api) {
            return false;
        }
        $rest = new Network_Http_Rest();
        $rest->url = $Api->url;
        if (!empty($Api->credenciales)) {
            if ($Api->auth == 'http_auth_basic') {
                $aux = explode(':', $Api->credenciales);
                if (isset($aux[1])) {
                    $rest->setAuth($aux[0], $aux[1]);
                } else {
                    $rest->setAuth($aux[0]);
                }
            }
        }
        return $rest;
    }

    /**
     * Entrega los enlaces normalizados para ser usados en el
     * layout de la aplicación.
     */
    public function getLinks(): array
    {
        $links = $this->config_extra_links
            ? $this->config_extra_links
            : (array)config('nav.contribuyente')
        ;
        foreach ($links as &$l) {
            if (empty($l->icono)) {
                $l->icono = 'fa-solid fa-link';
            }
        }
        return $links;
    }

    /**
     * Entrega la plantilla de un correo ya armada con los datos.
     */
    public function getEmailFromTemplate($template, $params = null)
    {
        // buscar plantilla
        $file = DIR_STATIC . '/contribuyentes/'
            . (int)$this->rut . '/email/' . $template . '.html'
        ;
        if (!is_readable($file)) {
            return false;
        }
        // buscar parámetros pasados
        $params = array_slice(func_get_args(), 1);
        // si no se pasó ningún parámetro solo se quiere saber si la plantilla existe o no
        if (!$params) {
            return true;
        }
        // leer archivo
        $html = file_get_contents($file);
        // plantilla de envío de DTE
        if ($template == 'dte') {
            $Documento = $params[0];
            $msg_text = !empty($params[1]) ? $params[1] : null;
            $links = $Documento->getLinks();
            $class = get_class($Documento);
            $mostrar_pagado = false;
            $mostrar_pagar = false;
            if ($this->config_pagos_habilitado && $Documento->getTipo()->operacion == 'S') {
                $Cobro = $Documento->getCobro(false);
                if ($Cobro->total) {
                    if (!$Cobro->pagado) {
                        $mostrar_pagar = !empty($links['pagar']);
                    } else {
                        $mostrar_pagado = true;
                    }
                }
            }
            if ($class == 'website\Dte\Model_DteTmp') {
                if (in_array($Documento->dte, [33, 34, 39, 41])) {
                    $dte_cotizacion = 'cotización';
                    $dte_tipo = 'cotización';
                } else {
                    $dte_cotizacion = 'documento';
                    $dte_tipo = 'borrador de '.$Documento->getTipo()->tipo;
                }
            } else {
                $dte_cotizacion = 'documento tributario electrónico';
                $dte_tipo = $Documento->getTipo()->tipo;
            }
            $fecha_pago = $mostrar_pagado
                ? Utility_Date::format($Cobro->pagado)
                : '00/00/0000'
            ;
            $medio_pago = $mostrar_pagado
                ? $Cobro->getMedioPago()->getNombre()
                : '"sin pago"'
            ;
            $fecha_vencimiento = !empty($Cobro->vencimiento)
                ? $Cobro->vencimiento
                : $Documento->fecha
            ;
            return str_replace(
                [
                    '{dte_cotizacion}',
                    '{total_int}',
                    '{total}',
                    '{razon_social}',
                    '{documento}',
                    '{folio}',
                    '{fecha_emision_estandar}',
                    '{fecha_emision}',
                    '{fecha_vencimiento_estandar}',
                    '{fecha_vencimiento}',
                    '{link_pagar}',
                    '{link_pdf}',
                    '{msg_text}',
                    '{mostrar_pagado}',
                    '{msg_pagado}',
                    '{fecha_pago}',
                    '{medio_pago}',
                    '{mostrar_pagar}',
                    '{rut}',
                ],
                [
                    $dte_cotizacion,
                    $Documento->total,
                    num($Documento->total),
                    $Documento->getReceptor()->razon_social,
                    $dte_tipo,
                    $Documento->getFolio(),
                    $Documento->fecha,
                    Utility_Date::format($Documento->fecha),
                    $fecha_vencimiento,
                    Utility_Date::format($fecha_vencimiento),
                    $mostrar_pagar ? $links['pagar'] : '',
                    $links['pdf'],
                    $msg_text ? str_replace("\n", '</p><p>', $msg_text) : null,
                    !$mostrar_pagado ? 'none' : '',
                    $mostrar_pagado ? __('El documento se encuentra pagado con fecha %s usando el medio de pago %s.', $fecha_pago, $medio_pago) : '',
                    $fecha_pago,
                    $medio_pago,
                    !$mostrar_pagar ? 'none' : '',
                    $Documento->getReceptor()->rut,
                ],
                $html
            );
        }
        // no se encontró plantilla
        return false;
    }

    /**
     * Entrega la URL del sitio web.
     */
    public function getURL()
    {
        if ($this->config_extra_web) {
            if (
                strpos($this->config_extra_web, 'http://') === 0
                || strpos($this->config_extra_web, 'https://')
            ) {
                return $this->config_extra_web;
            } else {
                return 'http://' . $this->config_extra_web;
            }
        }
    }

    /**
     * Entrega la aplicación de tercero del contribuyente.
     */
    public function getApp($app)
    {
        // obtener namespace y código
        if (strpos($app, '.')) {
            list($namespace, $codigo) = explode('.', $app);
        } else {
            $namespace = 'apps';
            $codigo = $app;
        }
        // cargar app si existe
        $apps_config = (array)config('libredte.apps.' . $namespace);
        $App = (new Utility_Apps($apps_config))->getApp($codigo);
        if (!$App) {
            throw new \Exception('Aplicación solicitada "'.$app.'" no existe.', 404);
        }
        // cargar configuración de la app
        $App->setConfig($this->{'config_'.$namespace.'_'.$codigo});
        $App->setVars([
            'Contribuyente' => $this,
        ]);
        // entrgar App con su configuración (si existe) y enlazada al contribuyente
        return $App;
    }

    /**
     * Entrega todas los aplicaciones disponibles para el contribuyente.
     * @param array|string $filtros Los filtros de aplicaciones (como arreglo)
     * o el namespace de las aplicaciones (como string).
     */
    public function getApps($filtros = []): array
    {
        // ver si viene el namespace como filtro y extraer
        if (is_string($filtros)) {
            $filtros = ['namespace' => $filtros];
        }
        $default = [
            'namespace' => 'apps',
            'loadConfig' => true,
        ];
        $filtros = array_merge($default, $filtros);
        foreach ($default as $key => $value) {
            $$key = $filtros[$key];
            unset($filtros[$key]);
        }
        // obtener aplicaciones según namespace y filtros
        $apps_config = (array)config('libredte.apps.' . $namespace);
        $apps = (new Utility_Apps($apps_config))->getApps($filtros);
        // cargar variables por defecto (asociar contribuyente)
        foreach ($apps as $App) {
            $App->setVars([
                'Contribuyente' => $this,
            ]);
        }
        if ($loadConfig) {
            // cargar configuración de la app de un objeto que no es el Contribuyente
            if (is_array($loadConfig)) {
                list($config_obj, $config_prefix) = $loadConfig;
            }
            // cargar configuración de la app desde el objeto contribuyente y prefijo estándar
            else {
                $config_obj = $this;
                $config_prefix = 'config_' . $namespace . '_';
            }
            // cargar la configuración de cada aplicación
            foreach ($apps as $App) {
                $App->setConfig($config_obj->{$config_prefix . $App->getCodigo()});
                // si se solicitó solo disponibles o solo no disponibles verificar
                if (isset($filtros['disponible'])) {
                    if ($App->getConfig()->disponible != $filtros['disponible']) {
                        unset($apps[$App->getCodigo()]);
                    }
                }
            }
        }
        // entregar aplicaciones
        return $apps;
    }

    /**
     * Entrega el contador asociado al contribuyente.
     */
    public function getContador()
    {
        if (!$this->config_contabilidad_contador_run) {
            return false;
        }
        return (new Model_Contribuyentes())
            ->get($this->config_contabilidad_contador_run)
        ;
    }

    /**
     * Entrega las credenciales de empresa para autenticación
     * en el SII.
     */
    public function getSiiAuth(): array
    {
        if (!$this->config_sii_pass) {
            throw new \Exception(
                'La empresa no tiene configurada la contraseña del SII.'
            );
        }
        return [
            'pass' => [
                'rut' => $this->rut . '-' . $this->dv,
                'clave' => $this->config_sii_pass
            ]
        ];
    }

    /**
     * Entrega las credenciales de usuario para autenticación
     * en el SII. Se puede entregar las credenciales rut/clave del
     * usuario o en segunda instancia la firma electrónica del usuario.
     */
    public function getSiiAuthUser($user_id = null): array
    {
        // si no se indicó usuario se usa el por defecto de la empresa
        if (!$user_id) {
            $user_id = $this->usuario;
        }
        // mediante rut/clave del usuario
        $Usuario = $user_id == $this->usuario
            ? $this->getUsuario()
            : (new Model_Usuarios())->get($user_id)
        ;
        if ($Usuario->config_sii_rut && $Usuario->config_sii_pass) {
            return [
                'pass' => [
                    'rut' => str_replace('.', '', $Usuario->config_sii_rut),
                    'clave' => $Usuario->config_sii_pass,
                ],
            ];
        }
        // mediante firma electrónica del usuario
        return $this->getSiiAuthCert($user_id);
    }

    /**
     * Entrega las credenciales de usuario para autenticación
     * en el SII usando firma electrónica.
     */
    public function getSiiAuthCert($user_id = null): array
    {
        $Firma = $this->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception(
                'No existe una firma electrónica cargada que pueda ser usada.'
            );
        }
        return [
            'cert' => [
                'cert-data' => $Firma->getCertificate(),
                'pkey-data' => $Firma->getPrivateKey(),
            ],
        ];
    }

    /**
     * Indica si el contribuyente está o no en ambiente de certificación.
     * @return int =0 ambiente de producción, =1 ambiente de certificación.
     */
    public function enCertificacion(): int
    {
        $certificacion = config('modules.Dte.sii.certificacion');
        if ($certificacion !== null) {
            return (int)(bool)$certificacion;
        }
        if (isset($_GET['_contribuyente_certificacion'])) {
            return (int)(bool)$_GET['_contribuyente_certificacion'];
        }
        try {
            if (session()->has('dte.certificacion')) {
                return (int)(bool)session('dte.certificacion');
            }
        } catch (\Exception $e) {
        }
        return (int)(bool)$this->config_ambiente_en_certificacion;
    }

    /**
     * Entrega la configuración para el PDF de los DTE.
     * @param array|object $options
     * @param array $default_config
     */
    public function getConfigPDF($options, array $default_config = [])
    {
        if (is_object($options)) {
            $Documento = $options;
            $options = [
                'documento' => $Documento->dte,
                'actividad' => $Documento->getActividad('*'),
                'sucursal' => $Documento->sucursal_sii,
            ];
        }
        if (
            empty($default_config['formato'])
            || !isset($default_config['papelContinuo'])
        ) {
            foreach (['documento', 'actividad', 'sucursal'] as $col) {
                if (!isset($options[$col])) {
                    $options[$col] = '*';
                }
            }
            $config = $this->_getConfigPDF($options);
        } else {
            $config = [
                'formato' => $default_config['formato'],
                'papelContinuo' => $default_config['papelContinuo'],
            ];
        }
        // agregar configuración del formato encontrado como datos "extra"
        $formatoPDF = $this->getApp('dtepdfs.' . $config['formato']);
        if (!empty($formatoPDF)) {
            $config['extra'] = json_decode(json_encode(
                $formatoPDF->getConfig()
            ), true);
            unset($config['extra']['disponible']);
            if (!empty($default_config['extra'])) {
                $config['extra'] = Utility_Array::mergeRecursiveDistinct(
                    $config['extra'],
                    $default_config['extra']
                );
            }
        }
        // agregar siempre que se pueda la dirección de la casa matriz
        // como dato "extra"
        if (!empty($this->direccion) && !empty($this->comuna)) {
            $config['extra']['casa_matriz'] = $this->direccion
                . ', ' . $this->getComuna()->comuna
            ;
        }
        // agregar opciones del documento si se indicó
        if (!empty($Documento)) {
            $config['extra']['documento'] = [
                'emisor' => $Documento->emisor,
                'receptor' => $Documento->receptor,
                'dte' => $Documento->dte,
                'folio' => !empty($Documento->folio)
                    ? $Documento->folio
                    : $Documento->codigo
                ,
                'fecha' => $Documento->fecha,
                'total' => $Documento->total,
            ];
        }
        // entregar configuración
        return $config;
    }

    /**
     * Entrega la configuración de formato y papel para los PDF.
     */
    private function _getConfigPDF($options, $firstQuery = true)
    {
        // buscar si existe configuración creada según los filtros
        foreach ((array)$this->config_pdf_mapeo as $m) {
            if (
                $options['documento'] == $m->documento
                && $options['actividad'] == $m->actividad
                && $options['sucursal'] == $m->sucursal
            ) {
                return [
                    'formato' => $m->formato,
                    'papelContinuo' => $m->papel,
                ];
            }
        }
        // no se encontró la configuración buscada y la buscada no es la por defecto
        if (
            $options['documento'] == '*'
            && $options['actividad'] == '*'
            && $options['sucursal'] == '*'
        ) {
            return [
                'formato' => 'estandar',
                'papelContinuo' => 0,
            ];
        }
        // buscar con permutaciones
        if ($firstQuery) {
            // documento por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['documento' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // actividad por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['actividad' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // sucursal por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['sucursal' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // documento y actividad por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['documento' => '*', 'actividad' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // documento y sucursal por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['documento' => '*', 'sucursal' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // actividad y sucursal por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['actividad' => '*', 'sucursal' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
            // todo por defecto
            $config = $this->_getConfigPDF(
                array_merge($options, ['documento' => '*', 'actividad' => '*', 'sucursal' => '*']),
                false
            );
            if ($config) {
                return $config;
            }
        }
        // no se encontró, se debe buscar en otra permutación
        return false;
    }
}
