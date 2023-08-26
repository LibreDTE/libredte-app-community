<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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
 * Clase para mapear la tabla dte_folio de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_folio
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Model_DteFolio extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_folio'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $siguiente; ///< integer(32) NOT NULL DEFAULT ''
    public $disponibles; ///< integer(32) NOT NULL DEFAULT ''
    public $alerta; ///< integer(32) NOT NULL DEFAULT ''
    public $alertado; ///< boolean() NOT NULL DEFAULT 'false'

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_tipo', 'column' => 'codigo')
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'siguiente' => array(
            'name'      => 'Siguiente',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'disponibles' => array(
            'name'      => 'Disponibles',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'alerta' => array(
            'name'      => 'Alerta',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'alertado' => array(
            'name'      => 'Alertado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte\Admin',
        'Model_DteTipo' => 'website\Dte\Admin'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método para guardar el mantenedor del folio usando una transacción
     * serializable
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function save($exitOnFailTransaction = true)
    {
        if (!$this->db->beginTransaction(true) and $exitOnFailTransaction) {
            return false;
        }
        parent::save();
        return $this->db->commit();
    }

    /**
     * Método que calcula la cantidad de folios que quedan disponibles y guarda
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-27
     */
    public function calcularDisponibles()
    {
        $this->db->beginTransaction(true);
        $cafs = $this->db->getTable('
            SELECT desde, hasta
            FROM dte_caf
            WHERE
                emisor = :emisor
                AND dte = :dte
                AND certificacion = :certificacion
                AND desde >= (
                    SELECT desde
                    FROM dte_caf
                    WHERE
                        emisor = :emisor
                        AND dte = :dte
                        AND certificacion = :certificacion
                        AND :folio BETWEEN desde AND hasta
                )
        ', [':emisor' => $this->emisor, ':dte'=>$this->dte, 'certificacion' => (int)$this->certificacion, ':folio'=>$this->siguiente]);
        $n_cafs = count($cafs);
        if (!$n_cafs)
            return false;
        if ($n_cafs==1) {
            $this->disponibles = $cafs[0]['hasta'] - $this->siguiente + 1;
        }
        else {
            for ($i=1; $i<$n_cafs; $i++) {
                if ($cafs[$i]['desde']!=($cafs[$i-1]['hasta']+1))
                    break;
            }
            $this->disponibles = $cafs[$i-1]['hasta'] - $this->siguiente + 1;
        }
        $status = $this->save(false);
        if (!$status) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de archivos CAF que existen cargados para
     * el tipo de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-08-25
     */
    public function getCafs($order = 'ASC')
    {
        $cafs = $this->db->getTable('
            SELECT desde, hasta, (hasta - desde + 1) AS cantidad, xml
            FROM dte_caf
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
            ORDER BY desde '.($order=='ASC'?'ASC':'DESC').'
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion]);
        foreach ($cafs as &$caf) {
            try {
                $xml = \website\Dte\Utility_Data::decrypt($caf['xml']);
                $Caf = new \sasco\LibreDTE\Sii\Folios($xml);
                $caf['fecha_autorizacion'] = $Caf->getFechaAutorizacion();
                $caf['fecha_vencimiento'] = $Caf->getFechaVencimiento();
                $caf['meses_autorizacion'] = $Caf->getMesesAutorizacion();
                $caf['vigente'] = $Caf->vigente();
            } catch (\Exception $e) {
                $caf['fecha_autorizacion'] = null;
                $caf['fecha_vencimiento'] = null;
                $caf['meses_autorizacion'] = null;
                $caf['vigente'] = null;
            }
            unset($caf['xml']);
        }
        return $cafs;
    }

    /**
     * Método que entrega el CAF de un folio de cierto DTE
     * @param dte Tipo de documento para el cual se quiere su CAF
     * @param folio Folio del CAF del DTE que se busca
     * @return \sasco\LibreDTE\Sii\Folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-16
     */
    public function getCaf($folio = null)
    {
        if (!$folio) {
            $folio = $this->siguiente;
        }
        $caf = $this->db->getValue('
            SELECT xml
            FROM dte_caf
            WHERE
                emisor = :rut
                AND dte = :dte
                AND certificacion = :certificacion
                AND :folio BETWEEN desde AND hasta
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => (int)$this->certificacion,
            ':folio' => $folio,
        ]);
        if (!$caf) {
            return false;
        }
        $caf = \website\Dte\Utility_Data::decrypt($caf);
        if (!$caf) {
            return false;
        }
        $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
        return $Caf->getTipo() ? $Caf : false;
    }

    /**
     * Método que entrega el objeto del tipo de DTE asociado al folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-19
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del contribuyente asociado al mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que permite realizar el timbraje de manera automática
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function timbrar($cantidad = null)
    {
        // corregir cantidad si no se pasó
        if (!$cantidad) {
            if (!$this->alerta) {
                throw new \Exception('No hay alerta configurada');
            }
            $cantidad = $this->alerta * 5;
        }
        // recuperar firma electrónica
        $Emisor = $this->getEmisor();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica');
        }
        // solicitar timbraje
        $r = libredte_api_consume(
            '/sii/dte/caf/solicitar/'.$Emisor->getRUT().'/'.$this->dte.'/'.$cantidad.'?certificacion='.(int)$this->certificacion,
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
            ]
        );
        if ($r['status']['code']!=200) {
            throw new \Exception('No se pudo obtener el CAF desde el SII: '.$r['body']);
        }
        // entregar XML
        return $r['body'];
    }

    /**
     * Método que guardar un archivo de folios en la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-05
     */
    public function guardarFolios($xml)
    {
        $Emisor = $this->getEmisor();
        $Folios = new \sasco\LibreDTE\Sii\Folios($xml);
        // si no se pudo validar el caf error
        if (!$Folios->getTipo()) {
            throw new \Exception('No fue posible cargar el CAF:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // verificar que el caf sea del emisor
        if ($Folios->getEmisor()!=$Emisor->rut.'-'.$Emisor->dv) {
            throw new \Exception('RUT del CAF '.$Folios->getEmisor().' no corresponde con el RUT de la empresa '.$Emisor->razon_social.' '.$Emisor->rut.'-'.$Emisor->dv);
        }
        // verificar que el folio que se está subiendo sea para el ambiente actual de la empresa
        $ambiente_empresa = $Emisor->enCertificacion() ? 'certificación' : 'producción';
        $ambiente_caf = $Folios->getCertificacion() ? 'certificación' : 'producción';
        if ($ambiente_empresa!=$ambiente_caf) {
            throw new \Exception('Empresa está en ambiente de '.$ambiente_empresa.' pero folios son de '.$ambiente_caf);
        }
        // crear caf para el folio
        $DteCaf = new Model_DteCaf($this->emisor, $this->dte, (int)$Folios->getCertificacion(), $Folios->getDesde());
        if ($DteCaf->exists()) {
            throw new \Exception('El CAF para el documento de tipo '.$DteCaf->dte.' que inicia en '.$Folios->getDesde().' en ambiente de '.$ambiente_caf.' ya estaba cargado');
        }
        $DteCaf->hasta = $Folios->getHasta();
        $DteCaf->xml = \website\Dte\Utility_Data::encrypt($xml);
        try {
            $DteCaf->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('No fue posible guardar el CAF: '.$e->getMessage());
        }
        // actualizar mantenedor de folios
        if (!$this->disponibles) {
            $this->siguiente = $Folios->getDesde();
            $this->disponibles = $Folios->getHasta() - $Folios->getDesde() + 1;
        } else {
            $this->disponibles += $Folios->getHasta() - $Folios->getDesde() + 1;
        }
        $this->alertado = 'f';
        try {
            $this->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('El CAF se guardó, pero no fue posible actualizar el mantenedor de folios, deberá actualizar manualmente. '.$e->getMessage());
        }
        return $Folios;
    }

    /**
     * Método que entrega el uso mensual de los folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-10-20
     */
    public function getUsoMensual($limit = 12, $order = 'ASC')
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        return $this->db->getTable('
            SELECT * FROM (
                SELECT '.$periodo_col.' AS mes, COUNT(*) AS folios
                FROM dte_emitido
                WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
                GROUP BY '.$periodo_col.'
                ORDER BY '.$periodo_col.' DESC
                LIMIT '.(int)$limit.'
            ) AS t ORDER BY mes '.($order=='ASC'?'ASC':'DESC').'
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion]);
    }

    /**
     * Método que entrega el primer folio usado del mantenedor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-11
     */
    public function getPrimerFolio()
    {
        return $this->db->getValue(
            'SELECT MIN(folio) FROM dte_emitido WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion',
            [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion]
        );
    }

    /**
     * Método que entrega los folios que están antes del folio siguiente, para
     * los cuales hay CAF y no se han usado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-11
     */
    public function getSinUso()
    {
        // si no hay caf error
        if (!$this->getCafs()) {
            return [];
        }
        // buscar primer folio usado del CAF (se busca sólo desde este en adelante)
        $primer_folio = $this->getPrimerFolio();
        if (!$primer_folio) {
            return [];
        }
        // buscar rango
        $rangos_aux = $this->db->getTable('
            SELECT desde, hasta
            FROM dte_caf
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
            ORDER BY desde
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion]);
        $folios = [];
        foreach ($rangos_aux as $r) {
            for ($folio=$r['desde']; $folio<=$r['hasta']; $folio++) {
                $folios[] = $folio;
            }
        }
        return $this->db->getCol('
            SELECT folio
            FROM UNNEST(ARRAY['.implode(', ', $folios).']) AS folio
            WHERE
                folio NOT IN (
                    SELECT folio FROM dte_emitido WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
                )
                AND folio > :primer_folio
                AND folio < (SELECT siguiente FROM dte_folio WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion)
            ORDER BY folio
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>$this->certificacion, ':primer_folio'=>$primer_folio]);
    }

    /**
     * Método que entrega el estado de todos los folios asociados a todos los
     * CAFs del mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getEstadoFolios($estados = 'recibidos,anulados,pendientes', $retry = 100)
    {
        $estados = explode(',', $estados);
        // arreglo para resultado
        $folios = [];
        if (in_array('recibidos', $estados)) {
            $folios['recibidos'] = [];
        }
        if (in_array('anulados', $estados)) {
            $folios['anulados'] = [];
        }
        if (in_array('pendientes', $estados)) {
            $folios['pendientes'] = [];
        }
        // obtener todos los cafs existentes
        $cafs = (new Model_DteCafs())->setWhereStatement(
            ['emisor = :emisor', 'dte = :dte', 'certificacion = :certificacion'],
            [':emisor'=>$this->emisor, ':dte'=>$this->dte, ':certificacion'=>(int)$this->certificacion]
        )->setOrderByStatement('desde')->getObjects();
        // recorrer cada caf e ir extrayendo los campos
        foreach($cafs as $DteCaf) {
            // obtener folios recibidos
            if (in_array('recibidos', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['recibidos'] = array_merge($folios['recibidos'], $DteCaf->getFoliosRecibidos());
                        break;
                    } catch (\Exception $e) {
                        usleep(200000);
                    }
                }
            }
            // obtener folios anulados
            if (in_array('anulados', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['anulados'] = array_merge($folios['anulados'], $DteCaf->getFoliosAnulados());
                        break;
                    } catch (\Exception $e) {
                        usleep(200000);
                    }
                }
            }
            // obtener folios pendientes
            if (in_array('pendientes', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['pendientes'] = array_merge($folios['pendientes'], $DteCaf->getFoliosPendientes());
                        break;
                    } catch (\Exception $e) {
                        usleep(200000);
                    }
                }
            }
        }
        return $folios;
    }

}
