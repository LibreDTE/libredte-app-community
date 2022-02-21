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
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_tmp de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_tmp
 * @author SowerPHP Code Generator
 * @version 2015-09-22 01:01:43
 */
class Model_DteTmp extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_tmp'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $codigo; ///< character(32) NOT NULL DEFAULT '' PK
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $datos; ///< text() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $usuario; ///< integer(32) NULL DEFAULT ''
    public $extra; ///< text() NULL DEFAULT ''

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
        'receptor' => array(
            'name'      => 'Receptor',
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
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total' => array(
            'name'      => 'Total',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'datos' => array(
            'name'      => 'Datos',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii' => array(
            'name'      => 'Sucursal SII',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'extra' => array(
            'name'      => 'Extra',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_DteTipo' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios',
    ); ///< Namespaces que utiliza esta clase

    private $Receptor; ///< Caché para el receptor
    private $cache_datos; ///< Caché para los datos del documento

    /**
     * Método que genera el XML de EnvioDTE a partir de los datos ya
     * normalizados de un DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-06
     */
    public function getEnvioDte($folio = 0, \sasco\LibreDTE\Sii\Folios $Folios = null, \sasco\LibreDTE\FirmaElectronica $Firma = null, $RutReceptor = null, $fecha_emision = null)
    {
        $dte = json_decode($this->datos, true);
        if (!$dte) {
            return false;
        }
        $dte['Encabezado']['IdDoc']['Folio'] = $folio;
        if ($fecha_emision) {
            $dte['Encabezado']['IdDoc']['FchEmis'] = $fecha_emision;
        }
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte, false);
        if ($Folios and !$Dte->timbrar($Folios)) {
            return false;
        }
        if ($Firma and !$Dte->firmar($Firma)) {
            return false;
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($Dte);
        if ($Firma) {
            $EnvioDte->setFirma($Firma);
        }
        $Emisor = $this->getEmisor();
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : false,
            'RutReceptor' => $RutReceptor ? $RutReceptor : $Dte->getReceptor(),
            'FchResol' => $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $Emisor->enCertificacion() ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        return $EnvioDte;
    }

    /**
     * Método que entrega el objeto de receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-24
     */
    public function getReceptor()
    {
        if ($this->Receptor === null) {
            $this->Receptor = (new Model_Contribuyentes())->get($this->receptor);
            // si es boleta o documento de exportación se buscan datos adicionales del receptor
            if (in_array($this->dte, [39, 41, 110, 111, 112])) {
                // datos del receptor generales (si es boleta o doc de exp)
                if (empty($this->Receptor->telefono)) {
                    $this->Receptor->telefono = $this->getTelefono();
                }
                if (empty($this->Receptor->email)) {
                    $emails = $this->getEmails();
                    if (!empty($emails)) {
                        $this->Receptor->email = array_shift($emails);
                    }
                }
                // datos del receptor si es boleta
                if (in_array($this->dte, [39, 41])) {
                    if ($this->receptor==66666666) {
                        $datos = json_decode($this->datos, true)['Encabezado']['Receptor'];
                        $this->Receptor->razon_social = !empty($datos['RznSocRecep']) ? $datos['RznSocRecep'] : null;
                        $this->Receptor->direccion = !empty($datos['DirRecep']) ? $datos['DirRecep'] : null;
                        $this->Receptor->comuna = !empty($datos['CmnaRecep']) ? $datos['CmnaRecep'] : null;
                        $this->Receptor->giro = null;
                    }
                }
                // datos del receptor si es documento de exportación
                else if (in_array($this->dte, [110, 111, 112])) {
                    $datos = json_decode($this->datos, true)['Encabezado']['Receptor'];
                    $this->Receptor->razon_social = $datos['RznSocRecep'];
                    $this->Receptor->direccion = $datos['DirRecep'];
                    $this->Receptor->comuna = null;
                }
            }
        }
        return $this->Receptor;
    }

    /**
     * Método que entrega el objeto del tipo de dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-04
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del emisor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el folio del documento temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-13
     */
    public function getFolio()
    {
        return $this->dte.'-'.strtoupper(substr($this->codigo, 0, 7));
    }

    /**
     * Método que entrega la sucursal asociada al documento temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-20
     */
    public function getSucursal()
    {
        return $this->getEmisor()->getSucursal($this->sucursal_sii);
    }

    /**
     * Método que crea el DTE real asociado al DTE temporal
     * Permite usar el facturador local de LibreDTE o el del Portal MIPYME del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function generar($user_id = null, $fecha_emision = null, $retry = null, $gzip = null)
    {
        // facturador local de LibreDTE
        if (!$this->getEmisor()->config_libredte_facturador) {
            return $this->generarConFacturadorLocal($user_id, $fecha_emision, $retry, $gzip);
        }
        // facturador del Portal MIPYME del SII
        else if ($this->getEmisor()->config_libredte_facturador == 1) {
            return $this->generarConFacturadorSii($user_id, $fecha_emision);
        }
        // facturador mixto
        else if ($this->getEmisor()->config_libredte_facturador == 2) {
            // facturador local de LibreDTE para boletas
            if (in_array($this->dte, [39, 41])) {
                return $this->generarConFacturadorLocal($user_id, $fecha_emision, $retry, $gzip);
            }
            // facturador del Portal MIPYME del SII para otros documentos
            else {
                return $this->generarConFacturadorSii($user_id, $fecha_emision);
            }
        }
    }

    /**
     * Método que crea el DTE real asociado al DTE temporal usando LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    private function generarConFacturadorLocal($user_id = null, $fecha_emision = null, $retry = null, $gzip = null)
    {
        $Emisor = $this->getEmisor();
        if (!$user_id) {
            $user_id = $Emisor->usuario;
        }
        // obtener firma electrónica
        $Firma = $Emisor->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de generar el DTE. [faq:174]', 506);
        }
        // no hay fecha de resolución configurada
        $fecha_resolucion = $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha;
        if (empty($fecha_resolucion)) {
            throw new \Exception('No hay fecha de resolución de SII configurada para el ambiente de emisión. Debe configurar la fecha antes de generar el DTE. [faq:249]', 400);
        }
        // solicitar folio
        $datos_dte = $this->getDatos();
        $folio_dte = !empty($datos_dte['Encabezado']['IdDoc']['Folio']) ? (int)$datos_dte['Encabezado']['IdDoc']['Folio'] : 0;
        if ($folio_dte) {
            $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($user_id);
            if (!$Emisor->puedeAsignarFolio($Usuario)) {
                $folio_dte = 0;
            }
        }
        $FolioInfo = $Emisor->getFolio($this->dte, $folio_dte);
        if (!$FolioInfo) {
            throw new \Exception('No fue posible obtener un folio para '.$this->getTipo()->tipo.'. Revise el mantenedor de folios y corrobore que tiene un CAF vigente en uso. [faq:10]', 508);
        }
        // si el CAF no está vigente se alerta al usuario
        if (!$FolioInfo->Caf->vigente()) {
            throw new \Exception('Se obtuvo el CAF que contiene el folio '.$FolioInfo->folio.' de '.$this->getTipo()->tipo.', sin embargo este folio no puede ser usado porque el CAF no está vigente (está vencido). Debe anular los folios del CAF vencido y solicitar uno nuevo. [faq:3]', 508);
        }
        // si quedan pocos folios timbrar o alertar según corresponda
        if ($FolioInfo->DteFolio->disponibles <= $FolioInfo->DteFolio->alerta) {
            $timbrado = false;
            // timbrar automáticmente
            if ($Emisor->config_sii_timbraje_automatico==1) {
                try {
                    $xml = $FolioInfo->DteFolio->timbrar($FolioInfo->DteFolio->alerta*$Emisor->config_sii_timbraje_multiplicador);
                    $FolioInfo->DteFolio->guardarFolios($xml);
                    $timbrado = true;
                } catch (\Exception $e) {
                }
            }
            // notificar al usuario administrador
            if (!$timbrado and !$FolioInfo->DteFolio->alertado) {
                $asunto = 'Alerta de folios tipo '.$FolioInfo->DteFolio->dte;
                $msg = 'Se ha alcanzado el límite de folios del tipo de DTE '.$FolioInfo->DteFolio->dte.' para el contribuyente '.$Emisor->razon_social.', quedan '.$FolioInfo->DteFolio->disponibles.'. Por favor, solicite un nuevo archivo CAF y súbalo a LibreDTE en '.\sowerphp\core\Configure::read('app.url').'/dte/admin/dte_folios';
                if ($Emisor->notificar($asunto, $msg)) {
                    $FolioInfo->DteFolio->alertado = 1;
                    $FolioInfo->DteFolio->save();
                }
            }
        }
        // armar xml a partir del DTE temporal
        $EnvioDte = $this->getEnvioDte($FolioInfo->folio, $FolioInfo->Caf, $Firma, null, $fecha_emision);
        if (!$EnvioDte) {
            throw new \Exception('No fue posible generar el objeto del EnvioDTE. Folio '.$FolioInfo->folio.' quedará sin usar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 510);
        }
        $xml = $EnvioDte->generar();
        if (!$xml or !$EnvioDte->schemaValidate()) {
            throw new \Exception('No fue posible generar el XML del EnvioDTE. Folio '.$FolioInfo->folio.' quedará sin usar.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()).'<br/>[faq:69]', 510);
        }
        // guardar DTE
        $r = $EnvioDte->getDocumentos()[0]->getResumen();
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $r['TpoDoc'], $r['NroDoc'], $Emisor->enCertificacion());
        if ($DteEmitido->exists()) {
            throw new \Exception('Ya existe un DTE del tipo '.$r['TpoDoc'].' y folio '.$r['NroDoc'].' emitido en LibreDTE. Probablemente debe corregir el folio siguiente a usar. [faq:93]', 409);
        }
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'sucursal_sii'=>'CdgSIISucur', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false) {
                $DteEmitido->$attr = $r[$col];
            }
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = $xml; // guardar XML generado en LibreDTE
        $DteEmitido->usuario = $user_id;
        if (in_array($DteEmitido->dte, [110, 111, 112])) {
            $DteEmitido->total = $DteEmitido->exento = $this->total;
        }
        $DteEmitido->anulado = 0;
        $DteEmitido->iva_fuera_plazo = 0;
        if (!empty($this->extra)) {
            $DteEmitido->extra = $this->extra;
        }
        $DteEmitido->save();
        // guardar referencias si existen
        $datos = json_decode($this->datos, true);
        $nc_referencia_boleta = false;
        if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $referencia) {
                if (!empty($referencia['TpoDocRef']) and is_numeric($referencia['TpoDocRef']) and $referencia['TpoDocRef']<200) {
                    // guardar referencia
                    $DteReferencia = new Model_DteReferencia();
                    $DteReferencia->emisor = $DteEmitido->emisor;
                    $DteReferencia->dte = $DteEmitido->dte;
                    $DteReferencia->folio = $DteEmitido->folio;
                    $DteReferencia->certificacion = $DteEmitido->certificacion;
                    $DteReferencia->referencia_dte = $referencia['TpoDocRef'];
                    $DteReferencia->referencia_folio = $referencia['FolioRef'];
                    $DteReferencia->codigo = !empty($referencia['CodRef']) ? $referencia['CodRef'] : null;
                    $DteReferencia->razon = !empty($referencia['RazonRef']) ? $referencia['RazonRef'] : null;
                    $DteReferencia->save();
                    // si es nota de crédito asociada a boleta se recuerda por si se debe invalidar RCOF
                    if ($DteEmitido->dte==61 and in_array($referencia['TpoDocRef'], [39, 41])) {
                        $nc_referencia_boleta = true;
                    }
                    // si es nota de crédito que anula un DTE con cobro programado se borra el cobro programado
                    if (in_array($DteEmitido->dte, [61,112]) and $DteReferencia->codigo==1) {
                        $DteEmitidoReferencia = $DteReferencia->getDocumento();
                        if ($DteEmitidoReferencia->exists()) {
                            $pagos = $DteEmitidoReferencia->getPagosProgramados();
                            if ($pagos) {
                                try {
                                    $this->db->query(
                                        'DELETE FROM cobranza WHERE emisor = :emisor AND dte = :dte AND folio = :folio AND certificacion = :certificacion',
                                        [
                                            ':emisor' => $DteEmitidoReferencia->emisor,
                                            ':dte' => $DteEmitidoReferencia->dte,
                                            ':folio' => $DteEmitidoReferencia->folio,
                                            ':certificacion' => $DteEmitidoReferencia->certificacion,
                                        ]
                                    );
                                } catch (\Exception $e) {
                                    // fallo silencioso (se tendría que borrar a mano en el módulo de cobranza)
                                }
                            }
                        }
                    }
                }
            }
        }
        // guardar pagos programados si existen
        $MntPagos = $DteEmitido->getPagosProgramados();
        if (!empty($MntPagos)) {
            foreach ($MntPagos as $pago) {
                $Cobranza = new \website\Dte\Cobranzas\Model_Cobranza();
                $Cobranza->emisor = $DteEmitido->emisor;
                $Cobranza->dte = $DteEmitido->dte;
                $Cobranza->folio = $DteEmitido->folio;
                $Cobranza->certificacion = $DteEmitido->certificacion;
                $Cobranza->fecha = $pago['FchPago'];
                $Cobranza->monto = $pago['MntPago'];
                $Cobranza->glosa = !empty($pago['GlosaPagos']) ? $pago['GlosaPagos'] : null;
                $Cobranza->save();
            }
        }
        // invalidar RCOF si es una boleta o referencia de boleta y la fecha de
        // emisión es anterior al día actual
        if ($DteEmitido->fecha < date('Y-m-d')) {
            if (in_array($DteEmitido->dte, [39, 41]) or $nc_referencia_boleta) {
                $DteBoletaConsumo = new Model_DteBoletaConsumo($DteEmitido->emisor, $DteEmitido->fecha, (int)$DteEmitido->certificacion);
                if ($DteBoletaConsumo->track_id) {
                    $DteBoletaConsumo->track_id = null;
                    $DteBoletaConsumo->revision_estado = null;
                    $DteBoletaConsumo->revision_detalle = null;
                    $DteBoletaConsumo->save();
                }
            }
        }
        // enviar al SII
        try {
            $DteEmitido->enviar($user_id, $retry, $gzip);
        } catch (\Exception $e) {
        }
        // ejecutar trigger asociado a la generación del DTE real
        \sowerphp\core\Trigger::run('dte_documento_generado', $this, $DteEmitido);
        // eliminar DTE temporal
        $this->delete();
        // entregar DTE emitido
        return $DteEmitido;
    }

    /**
     * Método que crea el DTE real asociado al DTE temporal usando el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    private function generarConFacturadorSii($user_id = null, $fecha_emision = null)
    {
        throw new \Exception('Facturador del Portal MIPYME del SII aun no disponible.');
    }

    /**
     * Método que realiza verificaciones a campos antes de guardar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    public function save()
    {
        // trigger al guardar el DTE temporal
        \sowerphp\core\Trigger::run('dte_dte_tmp_guardar', $this);
        // si los datos extras existen y son un arreglo se convierte antes de guardar
        if (!empty($this->extra) and is_array($this->extra)) {
            $this->extra = json_encode($this->extra);
        }
        // guardar DTE temporal
        return parent::save();
    }

    /**
     * Método que borra el DTE temporal y su cobro asociado si existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function delete($borrarCobro = true)
    {
        $this->db->beginTransaction();
        if ($borrarCobro and $this->getEmisor()->config_pagos_habilitado) {
            $Cobro = $this->getCobro(false);
            if ($Cobro->exists() and !$Cobro->pagado) {
                if (!$Cobro->delete(false)) {
                    $this->db->rollback();
                    return false;
                }
            }
        }
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega el listado de correos a los que se podría enviar el documento
     * temporal (correo receptor, correo del dte y contacto comercial)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-02-12
     */
    public function getEmails()
    {
        $origen = (int)$this->getEmisor()->config_emision_origen_email;
        $emails = [];
        $datos = $this->getDatos();
        if (!in_array($this->dte, [39, 41])) {
            if (in_array($origen, [0, 1, 2]) and !empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
                $emails['Documento'] = strtolower($datos['Encabezado']['Receptor']['CorreoRecep']);
            }
        } else if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $r) {
                if (strpos($r['RazonRef'], 'Email receptor:')===0) {
                    $aux = explode('Email receptor:', $r['RazonRef']);
                    if (!empty($aux[1])) {
                        $email_dte = strtolower(trim($aux[1]));
                        if (in_array($origen, [0, 1, 2]) and $email_dte) {
                            $emails['Documento'] = $email_dte;
                        }
                    }
                    break;
                }
            }
        }
        if (in_array($origen, [0]) and $this->getReceptor()->email and !in_array($this->getReceptor()->email, $emails)) {
            $emails['Compartido LibreDTE'] = strtolower($this->getReceptor()->email);
        }
        if (in_array($origen, [0, 1]) and $this->getReceptor()->usuario and $this->getReceptor()->getUsuario()->email and !in_array(strtolower($this->getReceptor()->getUsuario()->email), $emails)) {
            $emails['Usuario LibreDTE'] = strtolower($this->getReceptor()->getUsuario()->email);
        }
        if ($this->emisor==\sowerphp\core\Configure::read('libredte.proveedor.rut')) {
            if ($this->getReceptor()->config_app_contacto_comercial) {
                $i = 1;
                foreach($this->getReceptor()->config_app_contacto_comercial as $contacto) {
                    if (!in_array(strtolower($contacto->email), $emails)) {
                        $emails['Comercial LibreDTE #'.$i++] = strtolower($contacto->email);
                    }
                }
            }
        }
        $emails_trigger = \sowerphp\core\Trigger::run('dte_dte_tmp_emails', $this, $emails);
        return $emails_trigger ? $emails_trigger : $emails;
    }

    /**
     * Método que envía el DTE temporal por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-02-21
     */
    public function email($to = null, $subject = null, $msg = null, $cotizacion = true, $use_template = true)
    {
        $Request = new \sowerphp\core\Network_Request();
        // variables por defecto
        if (!$to) {
            $to = $this->getEmails();
        }
        if (!$to) {
            throw new \Exception('No hay correo a quien enviar el DTE.');
        }
        if (!is_array($to)) {
            $to = [$to];
        }
        if (!$subject) {
            $subject = 'Documento N° '.$this->getFolio().' de '.$this->getEmisor()->getNombre().' ('.$this->getEmisor()->getRUT().')';
        }
        // armar cuerpo del correo
        $msg_html = $use_template ? $this->getEmisor()->getEmailFromTemplate('dte', $this, $msg) : false;
        if (!$use_template and $msg) {
            $msg = ['html' => $msg];
        }
        if (!$msg) {
            $msg .= 'Se adjunta documento N° '.$this->getFolio().' del día '.\sowerphp\general\Utility_Date::format($this->fecha).' por un monto total de $'.num($this->total).'.-'."\n\n";
            $links = $this->getLinks();
            if (!empty($links['pagar'])) {
                $msg .= 'Enlace pago en línea: '.$links['pagar']."\n\n";
            } else if (!empty($links['pdf'])) {
                $msg .= 'Puede descargar el documento en: '.$links['pdf']."\n\n";
            }
        }
        if ($msg_html) {
            $msg = ['text' => $msg, 'html' => $msg_html];
        }
        // crear email
        $email = $this->getEmisor()->getEmailSender();
        $email->to($to);
        $email->subject($subject);
        // agregar reply to si corresponde
        if (!empty($this->getEmisor()->config_email_intercambio_sender->reply_to)) {
            $email->replyTo($this->getEmisor()->config_email_intercambio_sender->reply_to);
        } else if ($this->getEmisor()->config_pagos_email) {
            $email->replyTo($this->getEmisor()->config_pagos_email);
        } else if ($this->getEmisor()->email) {
            $email->replyTo($this->getEmisor()->email);
        }
        // adjuntar PDF
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->getEmisor()->getUsuario()->hash);
        if ($cotizacion) {
            $response = $rest->get($Request->url.'/dte/dte_tmps/cotizacion/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor);
        } else {
            $response = $rest->get($Request->url.'/api/dte/dte_tmps/pdf/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor);
        }
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body']);
        }
        $email->attach([
            'data' => $response['body'],
            'name' => ($cotizacion?'cotizacion':'dte_tmp').'_'.$this->getEmisor()->getRUT().'_'.$this->getFolio().'.pdf',
            'type' => 'application/pdf',
        ]);
        // enviar email
        $status = $email->send($msg);
        if ($status===true) {
            // registrar envío del email
            $fecha_hora = date('Y-m-d H:i:s');
            foreach ($to as $dest) {
                try {
                    $this->db->query('
                        INSERT INTO dte_tmp_email
                        VALUES (:emisor, :receptor, :dte, :codigo, :email, :fecha_hora)
                    ', [
                        ':emisor' => $this->emisor,
                        ':receptor' => $this->receptor,
                        ':dte' => $this->dte,
                        ':codigo' => $this->codigo,
                        ':email' => $dest,
                        ':fecha_hora' => $fecha_hora,
                    ]);
                } catch (\Exception $e) {
                }
            }
            // todo ok
            return true;
        } else {
            throw new \Exception('No fue posible enviar el email: '.$status['message']);
        }
    }

    /**
     * Método que entrega el resumen de los correos enviados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-09
     */
    public function getEmailEnviadosResumen()
    {
        return $this->db->getTable('
            SELECT email, COUNT(*) AS enviados, MIN(fecha_hora) AS primer_envio, MAX(fecha_hora) AS ultimo_envio
            FROM dte_tmp_email
            WHERE emisor = :emisor AND receptor = :receptor AND  dte = :dte AND codigo = :codigo
            GROUP BY email
            ORDER BY ultimo_envio DESC, primer_envio ASC
        ', [
            ':emisor' => $this->emisor,
            ':receptor' => $this->receptor,
            ':dte' => $this->dte,
            ':codigo' => $this->codigo,
        ]);
    }

    /**
     * Método que entrega el arreglo con los datos del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    public function getDatos()
    {
        if (!isset($this->cache_datos)) {
            $this->cache_datos = json_decode($this->datos, true);
            $extra = (array)$this->getExtra();
            if (!empty($extra['dte'])) {
                $this->cache_datos = \sowerphp\core\Utility_Array::mergeRecursiveDistinct(
                    $this->cache_datos, $extra['dte']
                );
            }
        }
        return $this->cache_datos;
    }

    /**
     * Método que entrega el cobro asociado al DTE temporal
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-16
     */
    public function getCobro($crearSiNoExiste = true)
    {
        return (new \libredte\oficial\Pagos\Model_Cobro())->setDocumento($this, $crearSiNoExiste);
    }

    /**
     * Método que entrega el vencimiento del documento si es que existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-15
     */
    public function getVencimiento()
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? $datos['Encabezado']['IdDoc']['FchVenc'] : null;
    }

    /**
     * Método que entrega el detalle del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-20
     */
    public function getDetalle()
    {
        $Detalle = $this->getDatos()['Detalle'];
        return isset($Detalle[0]) ? $Detalle : [$Detalle];
    }

    /**
     * Método que entrega los enlaces públicos del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-06-16
     */
    public function getLinks()
    {
        $Request = new \sowerphp\core\Network_Request();
        $links = [];
        $links['ver'] = $Request->url.'/dte/dte_tmps/ver/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo;
        $links['pdf'] = $Request->url.'/dte/dte_tmps/cotizacion/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor;
        $links_trigger = \sowerphp\core\Trigger::run('dte_dte_tmp_links', $this, $links);
        return $links_trigger ? $links_trigger : $links;
    }

    /**
     * Método que entrega el teléfono asociado al DTE, ya sea porque existe en el DTE o asociado directamente al receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-03-24
     */
    public function getTelefono()
    {
        if (!isset($this->_telefono)) {
            $this->_telefono = null;
            if (!empty($this->getDatos()['Encabezado']['Receptor']['Contacto']) and $this->getDatos()['Encabezado']['Receptor']['Contacto'][0]=='+') {
                $this->_telefono = $this->getDatos()['Encabezado']['Receptor']['Contacto'];
            } else if (!empty($this->getReceptor()->telefono) and $this->getReceptor()->telefono[0]=='+') {
                $this->_telefono = $this->getReceptor()->telefono;
            }
        }
        return $this->_telefono;
    }

    /**
     * Método que entrega el celular asociado al DTE si existe
     * @warning Sólo detecta como celular un número chileno (+56 9)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-03-24
     */
    public function getCelular()
    {
        if (!isset($this->_celular)) {
            $this->_celular = null;
            if ($this->getTelefono() and strpos($this->getTelefono(), '+56 9')===0) {
                $this->_celular = $this->getTelefono();
            }
        }
        return $this->_celular;
    }

    /**
     * Método que entrega los datos extras del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    public function getExtra()
    {
        if (empty($this->extra)) {
            return null;
        }
        if (is_string($this->extra)) {
            $this->extra = json_decode($this->extra, true);
        }
        return $this->extra;
    }

    /**
     * Método que entrega la actividad económica asociada al documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-04
     */
    public function getActividad($default = null)
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['Emisor']['Acteco']) ? $datos['Encabezado']['Emisor']['Acteco'] : $default;
    }

    /**
     * Método que entrega el PDF del documento temporal.
     * Entrega el PDF que se ha generado con LibreDTE a partir del JSON del DTE
     * temporal.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-07
     */
    public function getPDF(array $config = [])
    {
        // configuración por defecto para el PDF
        $config_emisor = $this->getEmisor()->getConfigPDF($this, $config);
        $default_config = [
            'cotizacion' => 0,
            'cedible' => false,
            'compress' => false,
            'hash' => $this->getEmisor()->getUsuario()->hash,
            'extra' => (array)$this->getExtra(),
        ];
        $default_config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($config_emisor, $default_config);
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
        // armar xml a partir de datos del dte temporal
        $xml = $this->getEnvioDte($config['cotizacion'] ? $this->getFolio() : 0)->generar();
        if (!$xml) {
            throw new \Exception('No fue posible crear el PDF:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507);
        }
        $config['xml'] = base64_encode($xml);
        // consultar servicio web del contribuyente
        $ApiDtePdfClient = $this->getEmisor()->getApiClient('dte_pdf');
        if ($ApiDtePdfClient) {
            unset($config['hash']);
            $response = $ApiDtePdfClient->post($ApiDtePdfClient->url, $config);
        }
        // crear a partir de formato de PDF no estándar
        else if ($config['formato'] != 'estandar') {
            $apps = $this->getEmisor()->getApps('dtepdfs');
            if (empty($apps[$config['formato']]) or empty($apps[$config['formato']]->getConfig()->disponible)) {
                throw new \Exception('Formato de PDF '.$config['formato'].' no se encuentra disponible', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web local de LibreDTE
        else {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($config['hash']);
            unset($config['hash']);
            $Request = new \sowerphp\core\Network_Request();
            $response = $rest->post($Request->url.'/api/utilidades/documentos/generar_pdf', $config);
            if ($response===false) {
                throw new \Exception(implode("\n", $rest->getErrors()), 500);
            }
        }
        // procesar respuesta
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body'], $response['status']['code']);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

    /**
     * Método que entrega el código ESCPOS del documento temporal.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-02-28
     */
    public function getESCPOS(array $config = [])
    {
        // configuración por defecto para el código ESCPOS
        $default_config = [
            'formato' => 'estandar', // en el futuro podría salir de una configuración por DTE como los PDF
            'cotizacion' => 0,
            'cedible' => false,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 0,
            'webVerificacion' => \sowerphp\core\Configure::read('dte.web_verificacion'),
            'caratula' => [
                'FchResol' => $this->getEmisor()->enCertificacion() ? $this->getEmisor()->config_ambiente_certificacion_fecha : $this->getEmisor()->config_ambiente_produccion_fecha,
                'NroResol' => $this->getEmisor()->enCertificacion() ? 0 : $this->getEmisor()->config_ambiente_produccion_numero,
            ],
            'papelContinuo' => 80,
            'profile' => 'default',
            'hash' => $this->getEmisor()->getUsuario()->hash,
            'casa_matriz' => [
                'direccion' => $this->getEmisor()->direccion,
                'comuna' => $this->getEmisor()->getComuna()->comuna,
            ],
            'pdf417' => null,
            'extra' => (array)$this->getExtra(),
        ];
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
        // armar xml a partir de datos del dte temporal
        $xml = $this->getEnvioDte($config['cotizacion'] ? $this->getFolio() : 0)->generar();
        if (!$xml) {
            throw new \Exception('No fue posible crear el ESCPOS:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507);
        }
        $config['xml'] = base64_encode($xml);
        // logo
        $formatoEstandar = $this->getEmisor()->getApp('dtepdfs.estandar');
        if (!empty($formatoEstandar) and !empty($formatoEstandar->getConfig()->continuo->logo->posicion)) {
            $logo_file = DIR_STATIC.'/contribuyentes/'.$this->getEmisor()->rut.'/logo.png';
            if (is_readable($logo_file)) {
                $config['logo'] = base64_encode(file_get_contents($logo_file));
            }
        }
        // consultar servicio web del contribuyente
        $ApiDteEscPosClient = $this->getEmisor()->getApiClient('dte_escpos');
        if ($ApiDteEscPosClient) {
            unset($config['hash']);
            $response = $ApiDteEscPosClient->post($ApiDteEscPosClient->url, $config);
        }
        // consultar aplicación de ESCPOS según el formato solicitado
        else if ($apps = $this->getEmisor()->getApps('dteescpos')) {
            if (empty($apps[$config['formato']]) or empty($apps[$config['formato']]->getConfig()->disponible)) {
                throw new \Exception('Formato de ESCPOS '.$config['formato'].' no se encuentra disponible.', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web de LibreDTE
        else {
            unset($config['hash']);
            $response = libredte_api_consume('/libredte/dte/documentos/escpos', $config);
        }
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body'], 500);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

}
