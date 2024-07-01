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

/**
 * Clase para mapear la tabla dte_intercambio_resultado de la base de datos.
 */
class Model_DteIntercambioResultado extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_intercambio_resultado'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $responde; ///< integer(32) NOT NULL DEFAULT '' PK
    public $recibe; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $codigo; ///< character(32) NOT NULL DEFAULT '' PK
    public $contacto; ///< character varying(40) NULL DEFAULT ''
    public $telefono; ///< character varying(40) NULL DEFAULT ''
    public $email; ///< character varying(80) NULL DEFAULT ''
    public $fecha_hora; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'responde' => array(
            'name'      => 'Responde',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'recibe' => array(
            'name'      => 'Recibe',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
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
        'contacto' => array(
            'name'      => 'Contacto',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 40,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'telefono' => array(
            'name'      => 'Telefono',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 40,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'email' => array(
            'name'      => 'Email',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora' => array(
            'name'      => 'Fecha Hora',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
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

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que guarda el XML del Resultado de un intercambio.
     */
    public function saveXML($Emisor, $xml)
    {
        // crear respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->loadXML($xml);
        if (!$RespuestaEnvio->esResultadoDTE()) {
            return null; // no es ResultadoDTE se debe procesar otro archivo
        }
        // no cumple con esquema XML del SII (no se procesa)
        if (!$RespuestaEnvio->schemaValidate()) {
            throw new \Exception('Falló la validación del esquema del XML: '.implode(' / ', \sasco\LibreDTE\Log::readAll()));
        }
        // el RUT no es válido
        $Resultado = $RespuestaEnvio->toArray()['RespuestaDTE']['Resultado'];
        if (explode('-', $Resultado['Caratula']['RutRecibe'])[0] != $Emisor->rut) {
            throw new \Exception('El RUT del receptor no es válido.');
        }
        // guardar recepción
        $this->db->beginTransaction();
        $this->responde = explode('-', $Resultado['Caratula']['RutResponde'])[0];
        $this->recibe = $Emisor->rut;
        $this->codigo = md5($xml);
        $this->contacto = !empty($Resultado['Caratula']['NmbContacto'])
            ? substr($Resultado['Caratula']['NmbContacto'], 0, 40)
            : null
        ;
        $this->telefono = !empty($Resultado['Caratula']['FonoContacto'])
            ? substr($Resultado['Caratula']['FonoContacto'], 0, 40)
            : null
        ;
        $this->email = !empty($Resultado['Caratula']['MailContacto'])
            ? substr($Resultado['Caratula']['MailContacto'], 0, 80)
            : null
        ;
        $this->fecha_hora = str_replace('T', ' ', $Resultado['Caratula']['TmstFirmaResp']);
        $this->xml = base64_encode($xml);
        if (!$this->save()) {
            $this->db->rollback();
            throw new \Exception('No fue posible guardar el resultado del intercambio.');
        }
        // procesar cada resultado
        foreach ($RespuestaEnvio->getResultados() as $Resultado) {
            // si el RUT del emisor no corresponde con el del contribuyente el
            // acuse no es para este
            if (explode('-', $Resultado['RUTEmisor'])[0] != $Emisor->rut) {
                $this->db->rollback();
                throw new \Exception('El RUT del emisor del DTE informado no corresponde.');
            }
            // buscar DTE emitido en el ambiente del emisor
            $DteEmitido = new Model_DteEmitido(
                $Emisor->rut,
                $Resultado['TipoDTE'],
                $Resultado['Folio'],
                $Emisor->enCertificacion()
            );
            // si no existe o si los datos del DTE emitido no corresponden error
            if (
                !$DteEmitido->exists()
                || explode('-', $Resultado['RUTRecep'])[0] != $DteEmitido->receptor
                || $Resultado['FchEmis'] != $DteEmitido->fecha
                || $Resultado['MntTotal'] != $DteEmitido->total
            ) {
                $this->db->rollback();
                throw new \Exception('DTE informado no existe o sus datos no corresponden.');
            }
            // guardar recibo para el DTE
            $DteIntercambioResultadoDte = new Model_DteIntercambioResultadoDte(
                $DteEmitido->emisor, $DteEmitido->dte, $DteEmitido->folio, $DteEmitido->certificacion
            );
            $DteIntercambioResultadoDte->responde = $this->responde;
            $DteIntercambioResultadoDte->codigo = $this->codigo;
            $DteIntercambioResultadoDte->estado = $Resultado['EstadoDTE'];
            $DteIntercambioResultadoDte->glosa = isset($Resultado['EstadoDTEGlosa'])
                ? substr($Resultado['EstadoDTEGlosa'], 0, 256)
                : ''
            ;
            if (!$DteIntercambioResultadoDte->save()) {
                $this->db->rollback();
                throw new \Exception('No fue posible guardar el DTE del resultado del intercambio.');
            }
        }
        // aceptar transacción
        $this->db->commit();
        return true;
    }

}
