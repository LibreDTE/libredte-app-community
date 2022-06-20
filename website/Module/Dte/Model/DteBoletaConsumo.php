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
 * Clase para mapear la tabla dte_boleta_consumo de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_boleta_consumo
 * @author SowerPHP Code Generator
 * @version 2016-02-14 05:05:56
 */
class Model_DteBoletaConsumo extends Model_Base_Envio
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_boleta_consumo'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dia; ///< date() NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $secuencia; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< text() NULL DEFAULT ''

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
        'dia' => array(
            'name'      => 'Día',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
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
        'secuencia' => array(
            'name'      => 'Secuencia',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'track_id' => array(
            'name'      => 'Track ID',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Detalle',
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
        'Model_Contribuyente' => 'website\Dte'
    ); ///< Namespaces que utiliza esta clase

    private $_Emisor; //< Para emisor

    /**
     * Método que obtiene el objeto del emisor y lo guarda en caché en la clase
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-10
     */
    public function getEmisor()
    {
        if (!isset($this->_Emisor)) {
            $this->_Emisor = (new Model_Contribuyentes())->get($this->emisor);
        }
        return $this->_Emisor;
    }

    /**
     * Método que indica si el RCOF se debe enviar o no al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-06-20
     */
    public function seEnvia(): bool
    {
        // desde el 1ero de agosto de 2022 no se envían según Res Ex 53 del 2022
        if ($this->dia >= '2022-08-01') {
            return false;
        }
        // casos donde no se puede enviar
        if ($this->dia >= date('Y-m-d')) {
            return false;
        }
        if ($this->getEmisor()->config_sii_envio_rcof_desde and $this->dia < $this->getEmisor()->config_sii_envio_rcof_desde) {
            return false;
        }
        if ($this->getEmisor()->config_sii_envio_rcof_hasta and $this->dia > $this->getEmisor()->config_sii_envio_rcof_hasta) {
            return false;
        }
        // otros días se pueden enviar
        return true;
    }

    /**
     * Método que envia el reporte de consumo de folios al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-10
     */
    public function enviar($user_id = null)
    {
        if (!$this->seEnvia()) {
            $msg = 'Sólo se pueden enviar RCOF de días pasados.';
            if ($this->getEmisor()->config_sii_envio_rcof_desde and $this->getEmisor()->config_sii_envio_rcof_hasta) {
                $msg .= sprintf(
                    ' Y sólo entre los días %s y %s.',
                    \sowerphp\general\Utility_Date::format($this->getEmisor()->config_sii_envio_rcof_desde),
                    \sowerphp\general\Utility_Date::format($this->getEmisor()->config_sii_envio_rcof_hasta)
                );
            } else if ($this->getEmisor()->config_sii_envio_rcof_desde) {
                $msg .= sprintf(
                    ' Y sólo desde el día %s.',
                    \sowerphp\general\Utility_Date::format($this->getEmisor()->config_sii_envio_rcof_desde)
                );
            } else if ($this->getEmisor()->config_sii_envio_rcof_hasta) {
                $msg .= sprintf(
                    ' Y sólo hasta el día %s.',
                    \sowerphp\general\Utility_Date::format($this->getEmisor()->config_sii_envio_rcof_hasta)
                );
            }
            throw new \Exception($msg);
        }
        // enviar reporte
        $ConsumoFolio = $this->generarConsumoFolio($user_id);
        $xml = $ConsumoFolio->generar();
        if (!$ConsumoFolio->schemaValidate()) {
            return false;
        }
        $this->track_id = $ConsumoFolio->enviar();
        if (!$this->track_id) {
            return false;
        }
        $this->secuencia = $ConsumoFolio->getSecuencia();
        $this->xml = base64_encode($xml);
        $this->revision_estado = null;
        $this->revision_detalle = null;
        return $this->save() ? $this->track_id : false;
    }

    /**
     * Método que entrega el XML del consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-14
     */
    public function getXML()
    {
        if ($this->xml) {
            return base64_decode($this->xml);
        }
        return $this->generarXML();
    }

    /**
     * Método que genera el XML del consumo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-09-14
     */
    private function generarXML()
    {
        $ConsumoFolio = $this->generarConsumoFolio(null);
        $xml = $ConsumoFolio->generar();
        if (!$ConsumoFolio->schemaValidate()) {
            return false;
        }
        return $xml;
    }

    /**
     * Método que crea el objeto del consumo de folios de LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-09-14
     */
    private function generarConsumoFolio($user_id)
    {
        $Emisor = $this->getEmisor();
        $dtes = [];
        foreach ($Emisor->getDocumentosAutorizados() as $dte) {
            if (in_array($dte['codigo'], [39, 41, 61])) {
                $dtes[] = $dte['codigo'];
            }
        }
        sort($dtes);
        $documentos = $Emisor->getDocumentosConsumoFolios($this->dia);
        $ConsumoFolio = new \sasco\LibreDTE\Sii\ConsumoFolio();
        $Firma = $Emisor->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes generar el RCOF. [faq:174]', 506);
        }
        $ConsumoFolio->setFirma($Firma);
        $ConsumoFolio->setDocumentos($dtes);
        foreach ($documentos as $documento) {
            $ConsumoFolio->agregar([
                'TpoDoc' => $documento['dte'],
                'NroDoc' => $documento['folio'],
                'TasaImp' => $documento['tasa'],
                'FchDoc' => $documento['fecha'],
                'MntExe' => $documento['exento'],
                'MntNeto' => $documento['neto'],
                'MntIVA' => $documento['iva'],
                'MntTotal' => $documento['total'],
            ]);
        }
        $ConsumoFolio->setCaratula([
            'RutEmisor' => $Emisor->rut.'-'.$Emisor->dv,
            'FchResol' => $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $Emisor->enCertificacion() ? 0 : $Emisor->config_ambiente_produccion_numero,
            'FchInicio' => $this->dia,
            'FchFinal' => $this->dia,
            'SecEnvio' => $this->secuencia + 1,
        ]);
        return $ConsumoFolio;
    }

    /**
     * Método que actualiza el estado del RCOF enviado al SII, en realidad
     * es un wrapper para las verdaderas llamadas
     * @param usarWebservice =true se consultará vía servicio web =false vía email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function actualizarEstado($user_id = null, $usarWebservice = true)
    {
        if (!$this->track_id) {
            throw new \Exception('RCOF no tiene Track ID, primero debe enviarlo al SII');
        }
        if ($this->getEmisor()->isEmailReceiverLibredte('sii')) {
            $usarWebservice = true;
        }
        return $usarWebservice ? $this->actualizarEstadoWebservice($user_id) : $this->actualizarEstadoEmail();
    }

    /**
     * Método que actualiza el estado del RCOF enviado al SII a través del
     * servicio web que dispone el SII para esta consulta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    private function actualizarEstadoWebservice($user_id = null)
    {
        // obtener firma
        $Firma = $this->getEmisor()->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de actualizar el estado del RCOF. [faq:174]');
        }
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // solicitar token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token');
        }
        // consultar estado enviado
        $estado_up = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$this->getEmisor()->rut, $this->getEmisor()->dv, $this->track_id, $token]);
        // si el estado no se pudo recuperar error
        if ($estado_up===false) {
            throw new \Exception('No fue posible obtener el estado del RCOF');
        }
        // armar estado del dte
        $estado = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0];
        if (isset($estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0])) {
            $glosa = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
        } else {
            $glosa = null;
        }
        $this->revision_estado = $glosa ? ($estado.' - '.$glosa) : $estado;
        if (!empty($estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/NUM_ATENCION')[0])) {
            $this->revision_detalle = trim(explode('( ', (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/NUM_ATENCION')[0])[1],')');
        } else {
            $this->revision_detalle = null;
        }
        if ($estado=='EPR') {
            $this->revision_estado = 'CORRECTO';
        }
        else if (in_array($estado, \website\Dte\Model_DteEmitidos::$revision_estados['rechazados'])) {
            $this->revision_estado = 'ERRONEO';
        }
        // guardar estado del dte
        try {
            $this->save();
            return [
                'track_id' => $this->track_id,
                'revision_estado' => $this->revision_estado,
                'revision_detalle' => $this->revision_detalle,
            ];
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
        }
    }

    /**
     * Método que actualiza el estado del RCOF enviado al SII a través del
     * email que es recibido desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-15
     */
    private function actualizarEstadoEmail()
    {
        $Emisor = $this->getEmisor();
        // buscar correo con respuesta
        $Imap = $Emisor->getEmailReceiver('sii');
        if (!$Imap) {
            throw new \Exception(
                'No fue posible conectar mediante IMAP a '.$Emisor->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors())
            );
        }
        $asunto = 'TipoEnvio=Automatico TrackID='.$this->track_id.' Rut='.$Emisor->rut.'-'.$Emisor->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            throw new \Exception(
                'No se encontró respuesta de envío del reporte de consumo de folios, espere unos segundos'
            );
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $estado = $detalle = null;
            $m = $Imap->getMessage($uid);
            if (!$m)
                continue;
            foreach ($m['attachments'] as $file) {
                if (!in_array($file['type'], ['application/xml', 'text/xml'])) {
                    continue;
                }
                $xml = new \SimpleXMLElement($file['data'], LIBXML_COMPACT);
                // obtener estado y detalle
                if (isset($xml->DocumentoResultadoConsumoFolios)) {
                    if ($xml->DocumentoResultadoConsumoFolios->Identificacion->Envio->TrackId==$this->track_id) {
                        $estado = (string)$xml->DocumentoResultadoConsumoFolios->Resultado->Estado;
                        $detalle = str_replace('T', ' ', (string)$xml->DocumentoResultadoConsumoFolios->Identificacion->Envio->TmstRecepcion);
                        if (!empty($xml->DocumentoResultadoConsumoFolios->Resultado->Reparos->Reparo)) {
                            $detalle = (string)$xml->DocumentoResultadoConsumoFolios->Resultado->Reparos->Reparo->Descripcion.': '.(string)$xml->DocumentoResultadoConsumoFolios->Resultado->Reparos->Reparo->Detalle.' ('.$detalle.')';
                        }
                        if (!empty($xml->DocumentoResultadoConsumoFolios->Resultado->Errores->Error)) {
                            $detalle = (string)$xml->DocumentoResultadoConsumoFolios->Resultado->Errores->Error->Descripcion.': '.(string)$xml->DocumentoResultadoConsumoFolios->Resultado->Errores->Error->Detalle.' ('.$detalle.')';
                        }
                    }
                }
            }
            if (isset($estado)) {
                $this->revision_estado = $estado;
                $this->revision_detalle = $detalle;
                try {
                    $this->save();
                    $Imap->setSeen($uid);
                    return [
                        'track_id' => $this->track_id,
                        'revision_estado' => $this->revision_estado,
                        'revision_detalle' => $this->revision_detalle,
                    ];
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    throw new \Exception(
                        'El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Método que entrega un resumen de los datos del RCOF enviado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-12-20
     */
    public function getResumen()
    {
        $xml = new \sasco\LibreDTE\XML();
        $xml->loadXML($this->getXML());
        $rcof = $xml->toArray();
        $resumen = [];
        if (!empty($rcof['ConsumoFolios']['DocumentoConsumoFolios']['Resumen'])) {
            if (!isset($rcof['ConsumoFolios']['DocumentoConsumoFolios']['Resumen'][0])) {
                $rcof['ConsumoFolios']['DocumentoConsumoFolios']['Resumen'] = [
                    $rcof['ConsumoFolios']['DocumentoConsumoFolios']['Resumen']
                ];
            }
            foreach ($rcof['ConsumoFolios']['DocumentoConsumoFolios']['Resumen'] as $r) {
                if (!empty($r['FoliosEmitidos'])) {
                    $resumen[$r['TipoDocumento']] = $r;
                }
            }
        }
        return $resumen;
    }

}
