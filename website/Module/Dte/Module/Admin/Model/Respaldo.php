<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del modelo
namespace website\Dte\Admin;

/**
 * Modelo para generar respaldo de datos de un contribuyente
 * @version 2017-01-19
 */
class Model_Respaldo
{

    private $tablas = [
        'cobranza' => [
            'rut' => 'emisor',
        ],
        'contribuyente' => [
            'rut' => 'rut',
        ],
        'contribuyente_config' => [
            'rut' => 'contribuyente',
        ],
        'dte_boleta_consumo' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'dte_caf' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
            'encriptar' => ['xml'],
        ],
        'dte_compra' => [
            'rut' => 'receptor',
            'archivos' => ['xml'],
        ],
        'dte_emitido' => [
            'rut' => 'emisor',
            'archivos' => ['xml', 'cesion_xml', 'extra' => ['ext' => 'json','base64' => false]],
        ],
        'dte_folio' => [
            'rut' => 'emisor',
        ],
        'dte_guia' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'dte_intercambio' => [
            'rut' => 'receptor',
            'archivos' => ['mensaje' => 'txt', 'mensaje_html' => 'html', 'archivo_xml', 'recepcion_xml', 'recibos_xml', 'resultado_xml'],
        ],
        'dte_intercambio_recepcion' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_recepcion_dte' => [
            'rut' => 'emisor',
        ],
        'dte_intercambio_recibo' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_recibo_dte' => [
            'rut' => 'emisor',
        ],
        'dte_intercambio_resultado' => [
            'rut' => 'recibe',
            'archivos' => ['xml'],
        ],
        'dte_intercambio_resultado_dte' => [
            'rut' => 'emisor',
        ],
        'dte_tmp' => [
            'rut' => 'emisor',
            'archivos' => ['datos' => ['ext' => 'json','base64' => false], 'extra' => ['ext' => 'json','base64' => false]],
        ],
        'dte_recibido' => [
            'rut' => 'receptor',
        ],
        'dte_referencia' => [
            'rut' => 'emisor',
        ],
        'dte_venta' => [
            'rut' => 'emisor',
            'archivos' => ['xml'],
        ],
        'item' => [
            'rut' => 'contribuyente',
        ],
        'item_clasificacion' => [
            'rut' => 'contribuyente',
        ],
        'registro_compra' => [
            'rut' => 'receptor',
        ],
        'boleta_honorario' => [
            'rut' => 'receptor',
        ],
        'boleta_tercero' => [
            'rut' => 'emisor',
        ],
    ]; ///< Información de las tabla que se exportarán

    private $_pks = []; ///< Caché para las PKs

    /**
     * Constructor del modelo de respaldos
         * @version 2017-01-19
     */
    public function __construct()
    {
        $this->db = &\sowerphp\core\Model_Datasource_Database::get();
        $extra = \sowerphp\core\Configure::read('app.respaldo.tablas');
        if ($extra) {
            $this->tablas += $extra;
        }
    }

    /**
     * Método que entrega el listado de tablas que se podrán respaldar
         * @version 2016-01-29
     */
    public function getTablas()
    {
        $tablas = [];
        foreach ($this->tablas as $tabla => $info) {
            $tablas[] = [$tabla];
        }
        return $tablas;
    }

    /**
     * Método que genera el respaldo
     * @param rut RUT del contribuyente que se desea respaldar
     * @param tablas Arreglo con las tablas a respaldar
     * @return Ruta del directorio donde se dejó el respaldo recién creado
         * @version 2019-04-10
     */
    public function generar($rut, $tablas = [])
    {
        // si no se especificaron tablas se respaldarán todas
        if (!$tablas) {
            $tablas = array_keys($this->tablas);
        }
        // procesar cada tabla
        $dir = $this->mkdirRespaldo($rut);
        $registros = [];
        foreach ($tablas as $tabla) {
            // si la tabla no se puede exportar se omite
            if (!isset($this->tablas[$tabla])) {
                continue;
            }
            // obtener datos de la tabla
            $info = $this->tablas[$tabla];
            $datos = $this->db->getTable(
                'SELECT * FROM '.$tabla.' WHERE '.$info['rut'].' = :rut',
                [':rut' => $rut]
            );
            if (empty($datos)) {
                continue;
            }
            $registros[$tabla] = count($datos);
            // si la tabla es la de configuraciones extras del contribuyente se
            // desencriptan las columnas que corresponden
            if ($tabla == 'contribuyente_config') {
                foreach ($datos as &$config) {
                    $key = $config['configuracion'].'_'.$config['variable'];
                    if (in_array($key, \website\Dte\Model_Contribuyente::$encriptar)) {
                        $config['valor'] = \website\Dte\Utility_Data::decrypt($config['valor']);
                    }
                }
            }
            // si hay que desencriptar datos se hace
            if (isset($info['encriptar'])) {
                foreach ($datos as &$row) {
                    foreach ($info['encriptar'] as $col) {
                        $row[$col] = trim(\website\Dte\Utility_Data::decrypt($row[$col]));
                    }
                }
            }
            // transformar booleanos a números
            foreach ($datos as &$row) {
                foreach ($row as &$value) {
                    if (is_bool($value)) {
                        $value = $value === true ? 1 : 0;
                    }
                }
            }
            // procesar archivos
            if (isset($info['archivos'])) {
                $pks = $this->getPKs($tabla);
                foreach ($datos as &$row) {
                    foreach ($info['archivos'] as $col => $file_meta) {
                        if (is_numeric($col)) {
                            $col = $file_meta;
                            $file_meta = ['ext' => 'xml', 'base64' => true];
                        }
                        if (!is_array($file_meta)) {
                            $file_meta = ['ext' => $file_meta, 'base64' => true];
                        }
                        // recuperar el archivo si está en base64 (o sea no está encriptado)
                        if ($file_meta['base64'] && (!isset($info['encriptar']) || !in_array($col, $info['encriptar']))) {
                            $row[$col] = base64_decode($row[$col]);
                        }
                        if (!empty($row[$col])) {
                            // nombre del archivo
                            $archivo = [];
                            foreach ($pks as $pk) {
                                $archivo[] = $row[$pk];
                            }
                            $archivo = implode('_', $archivo).'-'.$tabla.'-'.$col.'.'.$file_meta['ext'];
                            // guardar archivo
                            if (!file_exists($dir.'/'.$tabla)) {
                                mkdir($dir.'/'.$tabla);
                            }
                            file_put_contents($dir.'/'.$tabla.'/'.$archivo, $row[$col]);
                        }
                        // quitar columna de los datos
                        unset($row[$col]);
                    }
                }
            }
            // guardar datos de la tabla
            array_unshift($datos, array_keys($datos[0]));
            \sowerphp\general\Utility_Spreadsheet_CSV::save($datos, $dir.'/'.$tabla.'.csv');
        }
        // copiar logo si existe
        $logo = DIR_STATIC.'/contribuyentes/'.$rut.'/logo.png';
        if (file_exists($logo)) {
            copy($logo, $dir.'/logo.png');
        }
        // colocar información del respaldo realizado
        $msg = 'Respaldo de datos de LibreDTE'."\n";
        $msg .= '========================== == '."\n\n";
        $msg .= '- Contribuyente: '.$rut."\n";
        $msg .= '- Fecha y hora del respaldo: '.date('Y-m-d H:i:s')."\n\n";
        $msg .= 'Registros'."\n";
        $msg .= '---------'."\n\n";
        $total = 0;
        foreach ($registros as $tabla => $cantidad) {
            $total += $cantidad;
            $msg .= '- '.$tabla.': '.num($cantidad)."\n";
        }
        $msg .= "\n".'Total de registros: '.num($total)."\n\n\n\n";
        $msg .= "\n".'LibreDTE ¡facturación electrónica libre para Chile!'."\n";
        file_put_contents($dir.'/README.md', $msg);
        // entregar directorio
        return $dir;
    }

    /**
     * Método que crea el directorio temporal para el respaldo
         * @version 2021-09-26
     */
    private function mkdirRespaldo($rut, $prefix = 'libredte_contribuyente')
    {
        $dir = TMP.'/'.$prefix.'_'.$rut;
        if (file_exists($dir)) {
            \sowerphp\general\Utility_File::rmdir($dir);
        }
        if (file_exists($dir)) {
            throw new \Exception('Directorio de respaldo ya existe');
        }
        mkdir($dir);
        return $dir;
    }

    /**
     * Método que entrega las PKs de una tabla
         * @version 2019-04-10
     */
    private function getPKs($tabla)
    {
        if (!isset($this->_pks[$tabla])) {
            $this->_pks[$tabla] = $pks = $this->db->getPksFromTable($tabla);
            if (empty($pks)) {
                $this->_pks[$tabla] = $this->db->getPksFromTable($tabla, 'libredte');
            }
        }
        return $this->_pks[$tabla];
    }

    /**
     * Método que realiza el respaldo asociado a Boletas Electrónicas
         * @version 2021-09-26
     */
    public function boletas($Contribuyente, $fecha_creacion)
    {
        // obtener boletas
        $boletas = $this->db->getTable('
            SELECT
                emisor,
                dte,
                folio,
                fecha,
                receptor,
                exento,
                neto,
                iva,
                total,
                track_id,
                revision_estado,
                fecha_hora_creacion,
                xml
            FROM
                dte_emitido
            WHERE
                emisor = :emisor
                AND dte IN (39, 41)
                AND certificacion = :certificacion
                AND fecha_hora_creacion BETWEEN :fecha_creacion_desde AND :fecha_creacion_hasta
            ORDER BY fecha_hora_creacion
        ', [
            ':emisor' => $Contribuyente->rut,
            ':certificacion' => (int)$Contribuyente->enCertificacion(),
            ':fecha_creacion_desde' => $fecha_creacion.' 00:00:00',
            ':fecha_creacion_hasta' => $fecha_creacion.' 23:59:59',
        ]);
        // si no hay boletas informar falso, así no se genera un archivo
        if (empty($boletas)) {
            return false;
        }
        // procesar las boletas
        $dir = $this->mkdirRespaldo($Contribuyente->rut, 'libredte_boletas_'.$fecha_creacion);
        foreach ($boletas as &$boleta) {
            $filename = $Contribuyente->rut.'_'.$boleta['dte'].'_'.$boleta['folio'].'.xml';
            file_put_contents($dir.'/'.$filename, base64_decode($boleta['xml']));
            unset($boleta['xml']);
        }
        array_unshift($boletas, array_keys($boletas[0]));
        \sowerphp\general\Utility_Spreadsheet_CSV::save($boletas, $dir.'/boletas_'.$fecha_creacion.'.csv');
        // entregar nombre del directorio con el respaldo
        return $dir;
    }

}
