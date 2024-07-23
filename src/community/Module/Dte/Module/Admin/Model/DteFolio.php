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

namespace website\Dte\Admin;

/**
 * Clase para mapear la tabla dte_folio de la base de datos.
 */
class Model_DteFolio extends \sowerphp\autoload\Model
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
     * serializable.
     */
    public function save($exitOnFailTransaction = true): bool
    {
        if (!$this->getDatabaseConnection()->beginTransaction(true) && $exitOnFailTransaction) {
            return false;
        }
        parent::save();
        return $this->getDatabaseConnection()->commit();
    }

    /**
     * Método que calcula la cantidad de folios que quedan disponibles y guarda.
     */
    public function calcularDisponibles()
    {
        $this->getDatabaseConnection()->beginTransaction(true);
        $cafs = $this->getDatabaseConnection()->getTable('
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
        ', [
            ':emisor' => $this->emisor,
            ':dte' => $this->dte,
            'certificacion' => (int)$this->certificacion,
            ':folio' => $this->siguiente,
        ]);
        $n_cafs = count($cafs);
        if (!$n_cafs) {
            return false;
        }
        if ($n_cafs == 1) {
            $this->disponibles = $cafs[0]['hasta'] - $this->siguiente + 1;
        } else {
            for ($i=1; $i<$n_cafs; $i++) {
                if ($cafs[$i]['desde'] != ($cafs[$i - 1]['hasta'] + 1)) {
                    break;
                }
            }
            $this->disponibles = $cafs[$i - 1]['hasta'] - $this->siguiente + 1;
        }
        $status = $this->save(false);
        if (!$status) {
            $this->getDatabaseConnection()->rollback();
            return false;
        }
        $this->getDatabaseConnection()->commit();
        return true;
    }

    /**
     * Método que entrega el listado de archivos CAF que existen cargados para
     * el tipo de DTE.
     */
    public function getCafs(string $order = 'ASC')
    {
        $cafs = $this->getDatabaseConnection()->getTable('
            SELECT desde, hasta, (hasta - desde + 1) AS cantidad, xml
            FROM dte_caf
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
            ORDER BY desde '.($order == 'ASC'?'ASC':'DESC').'
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => $this->certificacion,
        ]);
        foreach ($cafs as &$caf) {
            try {
                $xml = decrypt($caf['xml']);
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
     * Método que entrega el CAF de un folio de cierto DTE.
     * @param folio Folio del CAF del DTE que se busca.
     * @return \sasco\LibreDTE\Sii\Folios
     */
    public function getCaf($folio = null)
    {
        if (!$folio) {
            $folio = $this->siguiente;
        }
        $caf = $this->getDatabaseConnection()->getValue('
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
        $caf = decrypt($caf);
        if (!$caf) {
            return false;
        }
        $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
        return $Caf->getTipo() ? $Caf : false;
    }

    /**
     * Método que entrega el objeto del tipo de DTE asociado al folio.
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del contribuyente asociado al mantenedor de folios.
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que permite realizar el timbraje de manera automática.
     */
    public function timbrar($cantidad = null)
    {
        // corregir cantidad si no se pasó
        if (!$cantidad) {
            if (!$this->alerta) {
                throw new \Exception('No hay alerta configurada.');
            }
            $cantidad = $this->alerta * 5;
        }
        // recuperar firma electrónica
        $Emisor = $this->getEmisor();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica.');
        }
        // solicitar timbraje
        $r = apigateway(
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
        if ($r['status']['code'] != 200) {
            throw new \Exception('No se pudo obtener el CAF desde el SII: '.$r['body']);
        }
        // entregar XML
        return $r['body'];
    }

    /**
     * Método que guardar un archivo de folios en la base de datos.
     */
    public function guardarFolios($xml)
    {
        $Emisor = $this->getEmisor();
        $Folios = new \sasco\LibreDTE\Sii\Folios($xml);
        // si no se pudo validar el caf error
        if (!$Folios->getTipo()) {
            throw new \Exception('No fue posible cargar el archivo XML del CAF:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // verificar que el caf sea del emisor
        if ($Folios->getEmisor() != $Emisor->rut.'-'.$Emisor->dv) {
            throw new \Exception('RUT del CAF '.$Folios->getEmisor().' no corresponde con el RUT de la empresa '.$Emisor->razon_social.' '.$Emisor->rut.'-'.$Emisor->dv);
        }
        // verificar que el folio que se está subiendo sea para el ambiente actual de la empresa
        $ambiente_empresa = $Emisor->enCertificacion() ? 'certificación' : 'producción';
        $ambiente_caf = $Folios->getCertificacion() ? 'certificación' : 'producción';
        if ($ambiente_empresa != $ambiente_caf) {
            throw new \Exception('Empresa está en ambiente de '.$ambiente_empresa.' pero folios son de '.$ambiente_caf.'.');
        }
        // crear caf para el folio
        $DteCaf = new Model_DteCaf(
            $this->emisor,
            $this->dte,
            (int)$Folios->getCertificacion(),
            $Folios->getDesde()
        );
        if ($DteCaf->exists()) {
            throw new \Exception('El archivo XML del CAF para el documento de tipo '.$DteCaf->dte.' que inicia en '.$Folios->getDesde().' en ambiente de '.$ambiente_caf.' ya estaba cargado en LibreDTE.');
        }
        $DteCaf->hasta = $Folios->getHasta();
        $DteCaf->xml = encrypt($xml);
        try {
            $DteCaf->save();
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            throw new \Exception('El CAF se guardó, pero no fue posible actualizar el mantenedor de folios, deberá actualizar manualmente. '.$e->getMessage());
        }
        return $Folios;
    }

    /**
     * Método que entrega el uso mensual de los folios.
     */
    public function getUsoMensual(int $limit = 12, string $order = 'ASC')
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha');
        return $this->getDatabaseConnection()->getTable('
            SELECT * FROM (
                SELECT '.$periodo_col.' AS mes, COUNT(*) AS folios
                FROM dte_emitido
                WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
                GROUP BY '.$periodo_col.'
                ORDER BY '.$periodo_col.' DESC
                LIMIT '.(int)$limit.'
            ) AS t ORDER BY mes '.($order == 'ASC'?'ASC':'DESC').'
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => $this->certificacion,
        ]);
    }

    /**
     * Método que entrega el primer folio usado del mantenedor.
     */
    public function getPrimerFolio(): int
    {
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT MIN(folio)
            FROM dte_emitido
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => $this->certificacion,
        ]);
    }

    /**
     * Método que entrega los folios que están antes del folio siguiente, para
     * los cuales hay CAF y no se han usado.
     */
    public function getSinUso(): array
    {
        // si no hay caf error
        if (!$this->getCafs()) {
            return [];
        }
        // buscar primer folio usado del CAF (se busca solo desde este en adelante)
        $primer_folio = $this->getPrimerFolio();
        if (!$primer_folio) {
            return [];
        }
        // buscar rango
        $rangos_aux = $this->getDatabaseConnection()->getTable('
            SELECT desde, hasta
            FROM dte_caf
            WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
            ORDER BY desde
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => $this->certificacion,
        ]);
        $folios = [];
        foreach ($rangos_aux as $r) {
            for ($folio=$r['desde']; $folio<=$r['hasta']; $folio++) {
                $folios[] = $folio;
            }
        }
        return $this->getDatabaseConnection()->getCol('
            SELECT folio
            FROM UNNEST(ARRAY['.implode(', ', $folios).']) AS folio
            WHERE
                folio NOT IN (
                    SELECT folio
                    FROM dte_emitido
                    WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
                )
                AND folio > :primer_folio
                AND folio < (
                    SELECT siguiente
                    FROM dte_folio
                    WHERE emisor = :rut AND dte = :dte AND certificacion = :certificacion
                )
            ORDER BY folio
        ', [
            ':rut' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => $this->certificacion,
            ':primer_folio' => $primer_folio,
        ]);
    }

    /**
     * Método que entrega el estado de todos los folios asociados a todos los
     * CAFs del mantenedor de folios.
     */
    public function getEstadoFolios($estados = 'recibidos,anulados,pendientes', int $retry = 10, int $sleep = 200000)
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
            [
                'emisor = :emisor',
                'dte = :dte',
                'certificacion = :certificacion',
            ],
            [
                ':emisor' => $this->emisor,
                ':dte' => $this->dte,
                ':certificacion' => (int)$this->certificacion,
            ]
        )->setOrderByStatement('desde')->getObjects();
        // recorrer cada caf e ir extrayendo los campos
        foreach($cafs as $DteCaf) {
            // obtener folios recibidos
            if (in_array('recibidos', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['recibidos'] = array_merge(
                            $folios['recibidos'],
                            $DteCaf->getFoliosRecibidos()
                        );
                        break;
                    } catch (\Exception $e) {
                        usleep($sleep);
                    }
                }
            }
            // obtener folios anulados
            if (in_array('anulados', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['anulados'] = array_merge(
                            $folios['anulados'],
                            $DteCaf->getFoliosAnulados()
                        );
                        break;
                    } catch (\Exception $e) {
                        usleep($sleep);
                    }
                }
            }
            // obtener folios pendientes
            if (in_array('pendientes', $estados)) {
                for ($i=0; $i<$retry; $i++) {
                    try {
                        $folios['pendientes'] = array_merge(
                            $folios['pendientes'],
                            $DteCaf->getFoliosPendientes()
                        );
                        break;
                    } catch (\Exception $e) {
                        usleep($sleep);
                    }
                }
            }
        }
        return $folios;
    }

}
