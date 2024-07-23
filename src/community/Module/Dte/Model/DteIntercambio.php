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

use \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas;

/**
 * Clase para mapear la tabla dte_intercambio de la base de datos.
 */
class Model_DteIntercambio extends \sowerphp\autoload\Model
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_intercambio'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $codigo; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $fecha_hora_email; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $asunto; ///< character varying(100) NOT NULL DEFAULT ''
    public $de; ///< character varying(80) NOT NULL DEFAULT ''
    public $responder_a; ///< character varying(80) NULL DEFAULT ''
    public $mensaje; ///< text() NULL DEFAULT ''
    public $mensaje_html; ///< text() NULL DEFAULT ''
    public $emisor; ///< integer(32) NOT NULL DEFAULT ''
    public $fecha_hora_firma; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $documentos; ///< smallint(16) NOT NULL DEFAULT ''
    public $archivo; ///< character varying(100) NOT NULL DEFAULT ''
    public $archivo_xml; ///< text() NOT NULL DEFAULT ''
    public $archivo_md5; ///< character(32) NOT NULL DEFAULT ''
    public $fecha_hora_respuesta; ///< timestamp without time zone() NULL DEFAULT ''
    public $estado; ///< smallint(16) NULL DEFAULT ''
    public $recepcion_xml; ///< text() NULL DEFAULT ''
    public $recibos_xml; ///< text() NULL DEFAULT ''
    public $resultado_xml; ///< text() NULL DEFAULT ''
    public $usuario; ///< integer(32) NULL DEFAULT '' FK:usuario.id

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
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
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
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
        'fecha_hora_email' => array(
            'name'      => 'Fecha Hora Email',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'asunto' => array(
            'name'      => 'Asunto',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'de' => array(
            'name'      => 'De',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'responder_a' => array(
            'name'      => 'Responder A',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'mensaje' => array(
            'name'      => 'Mensaje',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'mensaje_html' => array(
            'name'      => 'Mensaje Html',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora_firma' => array(
            'name'      => 'Fecha Hora Firma',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'documentos' => array(
            'name'      => 'Documentos',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo' => array(
            'name'      => 'Archivo',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo_xml' => array(
            'name'      => 'Archivo Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'archivo_md5' => array(
            'name'      => 'Archivo Md5',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora_respuesta' => array(
            'name'      => 'Fecha Hora Respuesta',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'estado' => array(
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'recepcion_xml' => array(
            'name'      => 'Recepcion Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'recibos_xml' => array(
            'name'      => 'Recibos Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'resultado_xml' => array(
            'name'      => 'Resultado Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
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

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que indica si ya existe previamente el documento (mismo archivo).
     */
    private function existeArchivo(): bool
    {
        return (bool)(int)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM dte_intercambio
            WHERE
                receptor = :receptor
                AND certificacion = :certificacion
                AND fecha_hora_firma = :fecha_hora_firma
                AND archivo_md5 = :archivo_md5
        ', [
            'receptor' => $this->receptor,
            'certificacion' => $this->certificacion,
            'fecha_hora_firma' => $this->fecha_hora_firma,
            'archivo_md5' => $this->archivo_md5,
        ]);
    }

    /**
     * Método que guarda el enviodte que se ha recibido desde otro contribuyente.
     */
    public function save(array $options = []): bool
    {
        $this->certificacion = (int)$this->certificacion;
        if (!isset($this->codigo)) {
            // ver si existe una entrada igual (mismo archivo)
            if ($this->existeArchivo()) {
                return true;
            }
            // corregir datos
            $this->archivo = utf8_encode($this->archivo);
            // guardar entrada
            $this->getDatabaseConnection()->beginTransaction(true);
            $this->codigo = (int)$this->getDatabaseConnection()->getValue('
                SELECT MAX(codigo)
                FROM dte_intercambio
                WHERE receptor = :receptor AND certificacion = :certificacion
            ', [
                ':receptor' => $this->receptor,
                'certificacion' => $this->certificacion,
            ]) + 1;
            try {
                $status = parent::save();
                $this->getDatabaseConnection()->commit();
                return $status;
            } catch (\Exception $e) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('Error al guardar el archivo \''.$this->archivo.'\' del intercambio enviado por '.$this->de.' con el asunto \''.$this->asunto.'\' del día '.$this->fecha_hora_email.' / '.$e->getMessage());
            }
        } else {
            return parent::save();
        }
    }

    /**
     * Método que entrega el objeto EnvioDte.
     * @return \sasco\LibreDTE\Sii\EnvioDte
     */
    public function getEnvioDte(): \sasco\LibreDTE\Sii\EnvioDte
    {
        if (!isset($this->EnvioDte)) {
            $this->EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $this->EnvioDte->loadXML(base64_decode($this->archivo_xml));
        }
        return $this->EnvioDte;
    }

    /**
     * Método que entrega un arreglo con los objetos Dte con los documentos.
     * @return array Arreglo de \sasco\LibreDTE\Sii\Dte
     */
    public function getDocumentos()
    {
        if (!isset($this->Documentos)) {
            $this->Documentos = $this->getEnvioDte()->getDocumentos(false); // usar saveXML en vez de C14N
        }
        return $this->Documentos;
    }

    /**
     * Método que entrega un objetos Dte con el documento solicitado o false si no se encontró.
     * @return \sasco\LibreDTE\Sii\Dte
     */
    public function getDocumento($emisor, $dte, $folio): \sasco\LibreDTE\Sii\Dte
    {
        return $this->getEnvioDte()->getDocumento($emisor, $dte, $folio);
    }

    /**
     * Método que entrega el objeto del receptor del intercambio.
     */
    public function getReceptor()
    {
        if (!isset($this->Receptor)) {
            $this->Receptor = (new Model_Contribuyentes())->get($this->receptor);
        }
        return $this->Receptor;
    }

    /**
     * Método que entrega el objeto del emisor del intercambio.
     */
    public function getEmisor()
    {
        if (!isset($this->Emisor)) {
            $this->Emisor = (new Model_Contribuyentes())->get($this->emisor);
            if (!$this->Emisor->exists()) {
                $this->Emisor->dv = \sowerphp\app\Utility_Rut::dv($this->emisor);
                $this->Emisor->razon_social = \sowerphp\app\Utility_Rut::addDV($this->emisor);
                $this->Emisor->save();
            }
        }
        return $this->Emisor;
    }

    /**
     * Método que entrega el objeto del estado del intercambio.
     */
    public function getEstado()
    {
        if (!isset($this->estado)) {
            return (object)['estado' => null];
        }
        return (object)['estado' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$this->estado]];
    }

    /**
     * Método que entrega el asunto del correo del intercambio.
     * Se recomienda usar este método para tratar de obtener el texto en la codificación correcta.
     */
    public function getEmailAsunto()
    {
        if (mb_detect_encoding($this->asunto, 'UTF-8, ISO-8859-1') == 'ISO-8859-1') {
            return utf8_encode($this->asunto);
        }
        return $this->asunto;
    }

    /**
     * Método que entrega el contenido del correo del intercambio en TXT.
     * Se recomienda usar este método para tratar de obtener el texto en la codificación correcta.
     */
    public function getEmailTxt()
    {
        if (!$this->mensaje) {
            return false;
        }
        $txt = str_replace("\n", '<br/>', strip_tags(base64_decode($this->mensaje)));
        if (mb_detect_encoding($txt, 'UTF-8, ISO-8859-1') == 'ISO-8859-1') {
            $txt = utf8_encode($txt);
        }
        return $txt;
    }

    /**
     * Método que entrega el contenido del correo del intercambio en HTML.
     * Se recomienda usar este método para tratar de obtener el texto en la codificación correcta.
     */
    public function getEmailHtml()
    {
        if (!$this->mensaje_html) {
            return false;
        }
        $html = base64_decode($this->mensaje_html);
        if (mb_detect_encoding($html, 'UTF-8, ISO-8859-1') == 'ISO-8859-1') {
            $html = utf8_encode($html);
        }
        return $html;
    }

    /**
     * Método que indica si intercambio se encuentra asociado a un DTE recibido.
     */
    public function recibido()
    {
        return (bool)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM dte_recibido
            WHERE receptor = :receptor AND emisor = :emisor AND certificacion = :certificacion AND intercambio = :intercambio
        ', [
            ':receptor' => $this->receptor,
            ':emisor' => $this->emisor,
            ':certificacion' => (int)$this->certificacion,
            ':intercambio' => $this->codigo,
        ]);
    }

    /**
     * Método que busca si los documentos del intercambio ya están en otro intercambio previamente recibido.
     */
    public function recibidoPreviamente()
    {
        // ver si existe una entrada igual (mismo archivo)
        if ($this->existeArchivo()) {
            return true;
        }
        // buscar por documentos (si ya están presentes en otros intercambios)
        $documentos = $this->getDocumentos();
        $n_documentos = count($documentos);
        $existen = 0;
        $DteIntercambios = (new Model_DteIntercambios())
            ->setContribuyente($this->getReceptor())
        ;
        foreach ($documentos as $Dte) {
            $docs = $DteIntercambios->buscarIntercambiosDte(
                substr($Dte->getEmisor(), 0, -2),
                $Dte->getTipo(),
                $Dte->getFolio()
            );
            if ($docs) {
                $existen++;
            }
        }
        // si todos los documentos existen se omiten
        // (si a lo menos uno no existe, se deja el intercambio)
        if ($existen == $n_documentos) {
            return true;
        }
        // no existe el intercambio previamente
        return false;
    }

    /**
     * Método para procesar el intercambio de manera automática si es que así
     * está configurado.
     */
    public function procesarRespuestaAutomatica($silenciosa = true)
    {
        $respondido = false;
        if ($this->getReceptor()->config_recepcion_intercambio_automatico) {
            // procesar el intercambio usando el servicio web del contribuyente
            // se espera como respuesta un =true, =false o arreglo (según parámetro de método responder)
            // si es un string se entenderá como que no se logró determinar qué hacer y se omitirá
            // la acción
            $accion = null;
            $ApiDteIntercambioResponder = $this->getReceptor()->getApiClient('dte_intercambio_responder');
            if ($ApiDteIntercambioResponder) {
                $response = $ApiDteIntercambioResponder->post(
                    $ApiDteIntercambioResponder->url,
                    ['xml' => $this->archivo_xml]
                );
                if ($response['status']['code'] == 200 && !is_string($response['body'])) {
                    if (is_array($response['body'])) {
                        if (!empty($response['body']['recibir']) || !empty($response['body']['reclamar'])) {
                            $accion = $response['body'];
                        }
                    } else {
                        $accion = (bool)$response['body'];
                    }
                }
            }
            // Despachar evento de aceptación/rechazo de los intercambios, y si
            // corresponde aceptar o reclamar el DTE. Si el evento entrega
            // 'null' (no existe handler para el evento o bien el evento
            // retornó 'null') se deja sin procesar la acción. Se despacha el
            // evento solo si no se logró determinar la acción usando el
            // servicio web del contribuyente.
            if ($accion === null) {
                $accion = event('dte_dte_intercambio_responder', [$this], true);
            }
            // Ejecutar acción según se haya indicado.
            if ($accion !== null) {
                try {
                    if (
                        is_array($accion)
                        && isset($accion['accion'])
                        && isset($accion['config'])
                    ) {
                        $config = $accion['config'];
                        $accion = $accion['accion'];
                    } else {
                        $config = [];
                    }
                    $config['user_id'] = $this->getReceptor()->getUsuario()->id;
                    $respondido = $this->responder($accion, $config);
                } catch (\Exception $e) {
                    // Se puede fallar de forma silenciosa. Si falla entonces
                    // $respondido es false y se notificará como DTE por
                    // responder. Si no se solicitó de manera silenciosa,
                    // entonces se lanzará una excepción.
                    if (!$silenciosa) {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        }
        return $respondido;
    }

    /**
     * Método que genera y envía la respuesta del intercambio
     * @param accion =true recibir todo el intercambio, =false reclama todo el intercambio, =array procesa los documentos indicados, debe tener índice recibir y/o reclamar o bien indíce númerico y se asume es el listado de documentos.
     * @param config Configuración global para la respuesta con índices: user_id, NmbContacto, MailContacto, sucursal, Recinto, responder_a, periodo.
     */
    public function responder($accion, array $config = [])
    {
        // configuración común a todos los DTE que se están respondiendo
        $config = array_merge([
            'user_id' => $this->getReceptor()->getUsuario()->id,
            'NmbContacto' => $this->getReceptor()->getUsuario()->nombre,
            'MailContacto' => $this->getReceptor()->getUsuario()->email,
            'sucursal' => 0,
            'Recinto' => $this->getReceptor()->direccion.', '.$this->getReceptor()->getComuna()->comuna,
            'responder_a' => $this->getEmisor()->config_email_intercambio_user
                ? $this->getEmisor()->config_email_intercambio_user
                : $this->de
            ,
            'periodo' => date('Ym'),
        ], $config);
        // obtener firma
        $Firma = $this->getReceptor()->getFirma($config['user_id']);
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            throw new \Exception($message);
        }
        // si es un booleano se acepta o reclaman todos los documentos del intercambio
        if (is_bool($accion) || is_numeric($accion)) {
            $docs = $this->getDocumentos();
            $recibir = [];
            $reclamar = [];
            foreach ($docs as $Dte) {
                $info = [
                    'TipoDTE' => $Dte->getTipo(),
                    'Folio' => $Dte->getFolio(),
                    'FchEmis' => $Dte->getFechaEmision(),
                    'RUTEmisor' => $Dte->getEmisor(),
                    'RUTRecep' => $Dte->getReceptor(),
                    'MntTotal' => $Dte->getMontoTotal(),
                ];
                if ($accion) {
                    $recibir[] = $info;
                } else {
                    $reclamar[] = $info;
                }
            }
            $accion = ['recibir' => $recibir, 'reclamar' => $reclamar];
        }
        // si es un arreglo con un índice númerico entonces se pasó el arreglo con los documentos directamente
        else if (is_array($accion) && isset($accion[0])) {
            $documentos = $accion;
        }
        // si no es arreglo o faltan ambos índices aceptar o reclamar -> error
        else if (!is_array($accion) || (empty($accion['recibir']) && empty($accion['reclamar']))) {
            throw new \Exception('Acción no válida para responder el intercambio.');
        }
        // armar un único arreglo con los documentos a procesar
        if (empty($documentos)) {
            $documentos = [];
            if (!empty($accion['recibir'])) {
                foreach ($accion['recibir'] as &$doc) {
                    if (empty($doc['EstadoRecepDTE'])) {
                        $doc['EstadoRecepDTE'] = 'ERM';
                    }
                    if (empty($doc['RecepDTEGlosa'])) {
                        $doc['RecepDTEGlosa'] = 'Otorga recibo de mercaderías o servicios.';
                    }
                }
                $documentos = array_merge($documentos, $accion['recibir']);
            }
            if (!empty($accion['reclamar'])) {
                foreach ($accion['reclamar'] as &$doc) {
                    if (empty($doc['EstadoRecepDTE'])) {
                        $doc['EstadoRecepDTE'] = 'RCD';
                    }
                    if (empty($doc['RecepDTEGlosa'])) {
                        $doc['RecepDTEGlosa'] = 'Reclamo al contenido del documento.';
                    }
                }
                $documentos = array_merge($documentos, $accion['reclamar']);
            }
        }
        // procesar los documentos
        $guardar_dte = [];
        foreach ($documentos as &$dte) {
            // validar datos requeridos
            if (empty($dte['TipoDTE']) || empty($dte['Folio'])) {
                throw new \Exception('Falta tipo o folio del DTE.');
            }
            // si no están los ruts se agregan
            // WARNING si el EnvioDTE pudiese contener más de un emisor de DTE
            // este if sería un problema (¿fix? -> se recomienda entregar siempre
            // el RUTEmisor a este método). La verificación de abajo del Dte
            // existiendo filtra un poco, pero hay un caso de borde donde en el
            // intercambio vengan 2 DTE de 2 emisores diferentes con el mismo
            // tipo y folio (si ocurriese, habría problema) ¿puede ocurrir Sres SII?
            if (empty($dte['RUTEmisor'])) {
                $dte['RUTEmisor'] = $this->emisor.'-'.\sowerphp\app\Utility_Rut::dv($this->emisor);
            }
            if (empty($dte['RUTRecep'])) {
                $dte['RUTRecep'] = $this->receptor.'-'.\sowerphp\app\Utility_Rut::dv($this->receptor);
            }
            // verificar que el DTE solicitado exista en el envío
            $Dte = $this->getDocumento($dte['RUTEmisor'], $dte['TipoDTE'], $dte['Folio']);
            if (!$Dte) {
                throw new \Exception('DTE T'.$dte['TipoDTE'].'F'.$dte['Folio'].' no existe en el intercambio.');
            }
            // agregar datos que no están pero que se pueden buscar en el documento de intercambio
            if (empty($dte['FchEmis']) || !isset($dte['MntTotal'])) {
                if (empty($dte['FchEmis'])) {
                    $dte['FchEmis'] = $Dte->getFechaEmision();
                }
                if (!isset($dte['MntTotal'])) {
                    $dte['MntTotal'] = $Dte->getMontoTotal();
                }
            }
            // asignar si se debe o no hacer acuse de recibo del DTE (solo estado ERM)
            $dte['acuse'] = (int)($dte['EstadoRecepDTE'] == 'ERM');
            // si tiene acuse de recibo, el DTE se marca para ser guardado
            if ($dte['acuse']) {
                $guardar_dte[] = 'T'.$dte['TipoDTE'].'F'.$dte['Folio'];
            }
        }
        // generar los 3 XML con las respuestas
        $xmlRecepcionDte = $this->crearXmlRecepcionDte($documentos, $config, $Firma);
        $xmlEnvioRecibos = $this->crearXmlEnvioRecibos($documentos, $config, $Firma);
        $xmlResultadoDte = $this->crearXmlResultadoDte($documentos, $config, $Firma);
        // enviar respuesta al SII
        $resultado_rc = $this->enviarRespuestaSII($documentos, $Firma);
        // guardar estado del intercambio y usuario que lo procesó
        if ($xmlRecepcionDte !== false) {
            $RecepcionDte = new \sasco\LibreDTE\XML();
            $RecepcionDte->loadXML($xmlRecepcionDte);
            $this->estado = $RecepcionDte->toArray()['RespuestaDTE']['Resultado']['RecepcionEnvio']['EstadoRecepEnv'];
            $this->recepcion_xml = base64_encode($xmlRecepcionDte);
        } else {
            $this->estado = 0; // estado "mentiroso" podría ser rechazo de un DTE 43 y quedar ok (=> se borra el intercambio)
        }
        $this->recibos_xml = $xmlEnvioRecibos
            ? base64_encode($xmlEnvioRecibos)
            : null
        ;
        $this->resultado_xml = $xmlResultadoDte
            ? base64_encode($xmlResultadoDte)
            : null
        ;
        $this->fecha_hora_respuesta = date('Y-m-d H:i:s');
        $this->usuario = $config['user_id'];
        $this->save();
        // guardar los documentos con acuse de recibo
        $this->guardarDocumentosRecibidos($guardar_dte, $resultado_rc['accion'], $config);
        // enviar XML al emisor del intercambio por corre electrónico
        $resultado_email = $this->enviarEmailRespuestaXML($config['responder_a'], $xmlRecepcionDte, $xmlEnvioRecibos, $xmlResultadoDte);
        // todo ok, intercambio fue respondido (independientemente del tipo de respuesta)
        return ['rc' => $resultado_rc, 'email' => $resultado_email];
    }

    /**
     * Método que crea el XML RecepcionDTE.
     */
    private function crearXmlRecepcionDte($documentos, $config, $Firma)
    {
        $RecepcionDTE = [];
        $EstadoRecepEnv = 99;
        foreach ($documentos as $dte) {
            if ($dte['TipoDTE'] == 43) {
                return false;
            }
            if (in_array($dte['EstadoRecepDTE'], ['ACD', 'ERM'])) {
                $EstadoRecepDTE = 0;
                $EstadoRecepEnv = 0;
            } else {
                $EstadoRecepDTE = 99;
            }
            $RecepcionDTE[] = [
                'TipoDTE' => $dte['TipoDTE'],
                'Folio' => $dte['Folio'],
                'FchEmis' => $dte['FchEmis'],
                'RUTEmisor' => $dte['RUTEmisor'],
                'RUTRecep' => $dte['RUTRecep'],
                'MntTotal' => $dte['MntTotal'],
                'EstadoRecepDTE' => $EstadoRecepDTE,
                'RecepDTEGlosa' => $dte['RecepDTEGlosa'],
            ];
        }
        // armar respuesta de envío
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(base64_decode($this->archivo_xml));
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->agregarRespuestaEnvio([
            'NmbEnvio' => mb_substr($this->archivo, 0, 80),
            'CodEnvio' => $this->codigo,
            'EnvioDTEID' => $EnvioDte->getID(),
            'Digest' => $EnvioDte->getDigest(),
            'RutEmisor' => $EnvioDte->getEmisor(),
            'RutReceptor' => $EnvioDte->getReceptor(),
            'EstadoRecepEnv' => $EstadoRecepEnv,
            'RecepEnvGlosa' => !$EstadoRecepEnv
                ? 'EnvioDTE recibido.'
                : 'No se aceptaron los DTE del EnvioDTE.'
            ,
            'NroDTE' => count($RecepcionDTE),
            'RecepcionDTE' => $RecepcionDTE,
        ]);
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $this->getReceptor()->rut.'-'.$this->getReceptor()->dv,
            'RutRecibe' => $this->emisor.'-'.\sowerphp\app\Utility_Rut::dv($this->emisor),
            'IdRespuesta' => $this->codigo,
            'NmbContacto' => $config['NmbContacto'],
            'MailContacto' => $config['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $RecepcionDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            throw new \Exception('No fue posible generar RecepcionDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // entregar XML
        return $RecepcionDTE_xml;
    }

    /**
     * Método que crea el XML EnvioRecibos.
     */
    private function crearXmlEnvioRecibos($documentos, $config, $Firma)
    {
        $EnvioRecibos = new \sasco\LibreDTE\Sii\EnvioRecibos();
        $EnvioRecibos->setCaratula([
            'RutResponde' => $this->getReceptor()->rut.'-'.$this->getReceptor()->dv,
            'RutRecibe' => $this->emisor.'-'.\sowerphp\app\Utility_Rut::dv($this->emisor),
            'NmbContacto' => $config['NmbContacto'],
            'MailContacto' => $config['MailContacto'],
        ]);
        $EnvioRecibos->setFirma($Firma);
        // procesar cada DTE
        $EnvioRecibos_r = [];
        foreach ($documentos as $dte) {
            if ($dte['TipoDTE'] == 43) {
                return false;
            }
            if ($dte['acuse']) {
                $EnvioRecibos->agregar([
                    'TipoDoc' => $dte['TipoDTE'],
                    'Folio' => $dte['Folio'],
                    'FchEmis' => $dte['FchEmis'],
                    'RUTEmisor' => $dte['RUTEmisor'],
                    'RUTRecep' => $dte['RUTRecep'],
                    'MntTotal' => $dte['MntTotal'],
                    'Recinto' => $config['Recinto'],
                    'RutFirma' => $Firma->getID(),
                ]);
                $EnvioRecibos_r[] = 'T'.$dte['TipoDTE'].'F'.$dte['Folio'];
            }
        }
        // generar y validar XML
        if ($EnvioRecibos_r) {
            $EnvioRecibos_xml = $EnvioRecibos->generar();
            if (!$EnvioRecibos->schemaValidate()) {
                throw new \Exception('No fue posible generar EnvioRecibos.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            // entregar XML
            return $EnvioRecibos_xml;
        }
        return false;
    }

    /**
     * Método que crea el XML ResultadoDTE.
     */
    private function crearXmlResultadoDte($documentos, $config, $Firma)
    {
        // objeto para la respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        // procesar cada DTE
        $i = 1;
        foreach ($documentos as $dte) {
            if ($dte['TipoDTE'] == 43) {
                return false;
            }
            $estado = in_array($dte['EstadoRecepDTE'], ['ACD', 'ERM']) ? 0 : 2;
            $RespuestaEnvio->agregarRespuestaDocumento([
                'TipoDTE' => $dte['TipoDTE'],
                'Folio' => $dte['Folio'],
                'FchEmis' => $dte['FchEmis'],
                'RUTEmisor' => $dte['RUTEmisor'],
                'RUTRecep' => $dte['RUTRecep'],
                'MntTotal' => $dte['MntTotal'],
                'CodEnvio' => $i++,
                'EstadoDTE' => $estado,
                'EstadoDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$estado],
            ]);
        }
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula([
            'RutResponde' => $this->getReceptor()->rut.'-'.$this->getReceptor()->dv,
            'RutRecibe' => $this->emisor.'-'.\sowerphp\app\Utility_Rut::dv($this->emisor),
            'IdRespuesta' => $this->codigo,
            'NmbContacto' => $config['NmbContacto'],
            'MailContacto' => $config['MailContacto'],
        ]);
        $RespuestaEnvio->setFirma($Firma);
        // generar y validar XML
        $ResultadoDTE_xml = $RespuestaEnvio->generar();
        if (!$RespuestaEnvio->schemaValidate()) {
            throw new \Exception('No fue posible generar ResultadoDTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // entregar XML
        return $ResultadoDTE_xml;
    }

    /**
     * Método que envía los 3 XML (si existen) por correo electrónico al emisor del intercambio.
     */
    private function enviarEmailRespuestaXML($responder_a, $xmlRecepcionDte, $xmlEnvioRecibos, $xmlResultadoDte)
    {
        $email = $this->getReceptor()->getEmailSender();
        $email->to($responder_a);
        $email->subject($this->getReceptor()->rut.'-'.$this->getReceptor()->dv.' - Respuesta intercambio DTE N° '.$this->codigo);
        foreach (['RecepcionDte', 'EnvioRecibos', 'ResultadoDte'] as $xml) {
            if (${'xml'.$xml}) {
                $email->attach([
                    'data' => ${'xml'.$xml},
                    'name' => $xml.'_'.$this->getReceptor()->rut.'-'.$this->getReceptor()->dv.'_'.$this->codigo.'.xml',
                    'type' => 'application/xml',
                ]);
            }
        }
        return $email->send('Se adjuntan XMLs de respuesta a intercambio de DTE.');
    }

    /**
     * Método que permite ingresar las acciones (respuestas) al registro de compras del SII.
     */
    private function enviarRespuestaSII($documentos, $Firma)
    {
        $resultado = ['estado' => [], 'accion' => []];
        try {
            $RCV = new \sasco\LibreDTE\Sii\RegistroCompraVenta($Firma);
        } catch (\Exception $e) {
            throw new \Exception('No fue posible informar al SII, por favor reintentar. El error fue: '.$e->getMessage());
        }
        foreach ($documentos as $dte) {
            if (in_array($dte['TipoDTE'], array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes))) {
                list($emisor_rut, $emisor_dv) = explode('-', $dte['RUTEmisor']);
                $r = $RCV->ingresarAceptacionReclamoDoc(
                    $emisor_rut,
                    $emisor_dv,
                    $dte['TipoDTE'],
                    $dte['Folio'],
                    $dte['EstadoRecepDTE']
                );
                $resultado['estado'][] = 'T'.$dte['TipoDTE'].'F'.$dte['Folio'].': '.$r['glosa'];
                if (!$r['codigo']) {
                    $resultado['accion']['T'.$dte['TipoDTE'].'F'.$dte['Folio']] = $dte['EstadoRecepDTE'];
                }
            }
        }
        return $resultado;
    }

    /**
     * Método que guarda los documentos que han sido aceptados (con acuse de recibo).
     */
    private function guardarDocumentosRecibidos(array $guardar_dte, array $rc_accion, array $config)
    {
        if ($guardar_dte) {
            // actualizar datos del emisor si no tine usuario asociado
            $EmisorIntercambio = $this->getEmisor();
            if (!$EmisorIntercambio->usuario) {
                $emisor = $this->getDocumentos()[0]->getDatos()['Encabezado']['Emisor'];
                $EmisorIntercambio->razon_social = $emisor['RznSoc'];
                if (!empty($emisor['GiroEmis'])) {
                    $EmisorIntercambio->giro = $emisor['GiroEmis'];
                }
                if (!empty($emisor['CorreoEmisor'])) {
                    $EmisorIntercambio->email = $emisor['CorreoEmisor'];
                }
                if (!empty($emisor['Acteco'])) {
                    $actividad_economica = $EmisorIntercambio->actividad_economica;
                    $EmisorIntercambio->actividad_economica = $emisor['Acteco'];
                    // dejar como estaba originalmente si no existe la actividad del XML
                    if (!$EmisorIntercambio->getActividadEconomica()->exists()) {
                        $EmisorIntercambio->actividad_economica = $actividad_economica;
                    }
                }
                if (!empty($emisor['CmnaOrigen'])) {
                    $comuna = (new Model_Comunas())->getComunaByName($emisor['CmnaOrigen']);
                    if ($comuna) {
                        $EmisorIntercambio->direccion = $emisor['DirOrigen'];
                        $EmisorIntercambio->comuna = $comuna;
                    }
                }
                $EmisorIntercambio->modificado = date('Y-m-d H:i:s');
                try {
                    $EmisorIntercambio->save();
                } catch (\Exception $e) {
                }
            }
            // guardar documentos que han sido aceptados como dte recibidos
            $Documentos = $this->getDocumentos();
            foreach ($Documentos as $Dte) {
                $dte_id = $Dte->getID(true);
                if (in_array($dte_id, $guardar_dte)) {
                    // procesar DTE recibido
                    $resumen = $Dte->getResumen();
                    $DteRecibido = new Model_DteRecibido(
                        $this->getEmisor()->rut,
                        $resumen['TpoDoc'],
                        $resumen['NroDoc'],
                        (int)$this->certificacion
                    );
                    $DteRecibido->rcv_accion = !empty($rc_accion[$dte_id])
                        ? $rc_accion[$dte_id]
                        : ($DteRecibido->rcv_accion ? $DteRecibido->rcv_accion : '000')
                    ;
                    if (!$DteRecibido->exists()) {
                        $DteRecibido->receptor = $this->getReceptor()->rut;
                        $DteRecibido->tasa = (int)$resumen['TasaImp'];
                        $DteRecibido->fecha = $resumen['FchDoc'];
                        $DteRecibido->sucursal_sii = (int)$resumen['CdgSIISucur'];
                        if ($resumen['MntExe']) {
                            $DteRecibido->exento = $resumen['MntExe'];
                        }
                        if ($resumen['MntNeto']) {
                            $DteRecibido->neto = $resumen['MntNeto'];
                        }
                        $DteRecibido->iva = (int)$resumen['MntIVA'];
                        $DteRecibido->total = (int)$resumen['MntTotal'];
                        $DteRecibido->usuario = $config['user_id'];
                        $DteRecibido->intercambio = $this->codigo;
                        $DteRecibido->impuesto_tipo = 1; // se asume siempre que es IVA
                        $periodo_dte = (int)substr(str_replace('-', '', $DteRecibido->fecha), 0, 6);
                        if (!empty($config['periodo']) && $config['periodo']>$periodo_dte) {
                            $DteRecibido->periodo = $config['periodo'];
                        }
                        if (!empty($config['sucursal'])) {
                            $DteRecibido->sucursal_sii_receptor = $config['sucursal'];
                        }
                        // si hay IVA y esta fuera de plazo se marca como no recuperable
                        if ($DteRecibido->iva && $DteRecibido->periodo) {
                            $meses = \sowerphp\general\Utility_Date::countMonths($periodo_dte, $DteRecibido->periodo);
                            if ($meses > 2) {
                                $DteRecibido->iva_no_recuperable = json_encode([
                                    ['codigo' => 2, 'monto' => $DteRecibido->iva]
                                ]);
                            }
                        }
                        // copiar impuestos adicionales
                        $datos = $Dte->getDatos();
                        if (!empty($datos['Encabezado']['Totales']['ImptoReten'])) {
                            if (!isset($datos['Encabezado']['Totales']['ImptoReten'][0])) {
                                $datos['Encabezado']['Totales']['ImptoReten'] = [$datos['Encabezado']['Totales']['ImptoReten']];
                            }
                            $DteRecibido->impuesto_adicional = [];
                            $impuesto_sin_credito = 0;
                            foreach ($datos['Encabezado']['Totales']['ImptoReten'] as $ia) {
                                if (
                                    $this->getReceptor()->config_extra_impuestos_sin_credito
                                    && in_array(
                                        $ia['TipoImp'],
                                        $this->getReceptor()->config_extra_impuestos_sin_credito
                                    )
                                ) {
                                    $impuesto_sin_credito += $ia['MontoImp'];
                                } else {
                                    $DteRecibido->impuesto_adicional[] = [
                                        'codigo' => $ia['TipoImp'],
                                        'tasa' => !empty($ia['TasaImp'])
                                            ? $ia['TasaImp']
                                            : null
                                        ,
                                        'monto' => $ia['MontoImp'],
                                    ];
                                }
                            }
                            if ($DteRecibido->impuesto_adicional) {
                                $DteRecibido->impuesto_adicional = json_encode($DteRecibido->impuesto_adicional);
                            }
                            if ($impuesto_sin_credito) {
                                $DteRecibido->impuesto_sin_credito = $impuesto_sin_credito;
                            }
                        }
                        // si es empresa exenta el IVA es no recuperable
                        if ($DteRecibido->iva && $this->getReceptor()->config_extra_exenta) {
                            $DteRecibido->iva_no_recuperable = json_encode([
                                ['codigo' => 1, 'monto' => $DteRecibido->iva]
                            ]);
                        }
                    }
                    // si ya estaba recibido y no existe intercambio se asigna
                    else if (!$DteRecibido->intercambio) {
                        $DteRecibido->intercambio = $this->codigo;
                    }
                    // guardar DTE recibido (actualiza acción RCV si existe)
                    $DteRecibido->save();
                }
            }
        }
    }

    /**
     * Método que entrega el PDF del intercambio.
     * Entrega un PDF con todos los documentos del intercambio o bien puede
     * entregar el PDF de un DTE específico que venga en el intercambio.
     */
    public function getPDF(array $config = [])
    {
        // obtener XML que se debe usar y opciones del DTE para buscar configuración del PDF
        $get_1_documento = (
            !empty($config['documento']['emisor'])
            && !empty($config['documento']['dte'])
            && !empty($config['documento']['folio'])
        );
        if ($get_1_documento || $this->documentos == 1) {
            // obtener documento
            if ($get_1_documento) {
                $Documento = $this->getDocumento(
                    $config['documento']['emisor'],
                    $config['documento']['dte'],
                    $config['documento']['folio']
                );
                if (!$Documento) {
                    throw new \Exception('No existe el DTE T'.$config['documento']['dte'].'F'.$config['documento']['folio'].' del RUT '.$config['documento']['emisor'].' en el intercambio N° '.$this->codigo, 404);
                }
            } else {
                $Documento = $this->getDocumentos()[0];
            }
            // obtener XML
            if ($get_1_documento) {
                $xml = base64_encode($Documento->saveXML());
            } else {
                $xml = $this->archivo_xml;
            }
            // obtener options que se usarán para la configuración del PDF
            $datos = $Documento->getDatos();
            $options = [
                'documento' => $datos['Encabezado']['IdDoc']['TipoDTE'],
                'actividad' => !empty($datos['Encabezado']['Emisor']['Acteco'])
                    ? $datos['Encabezado']['Emisor']['Acteco']
                    : '*'
                ,
                'sucursal' => !empty($datos['Encabezado']['Emisor']['CdgSIISucur'])
                    ? $datos['Encabezado']['Emisor']['CdgSIISucur']
                    : '*'
                ,
            ];
            unset($Documento);
            unset($datos);
        } else {
            $xml = $this->archivo_xml;
            $options = [
                'documento' => '*',
                'actividad' => '*',
                'sucursal' => '*',
            ];
        }
        // configuración por defecto para el PDF
        $config_emisor = $this->getEmisor()->getConfigPDF($options, $config);
        $default_config = [
            'cedible' => false,
            'compress' => !($get_1_documento || $this->documentos == 1),
            'copias_tributarias' => 1,
            'copias_cedibles' => 1,
            'xml' => $xml,
            'caratula' => [
                'FchResol' => $this->certificacion
                    ? $this->getEmisor()->config_ambiente_certificacion_fecha
                    : $this->getEmisor()->config_ambiente_produccion_fecha
                ,
                'NroResol' => $this->certificacion
                    ? 0
                    : $this->getEmisor()->config_ambiente_produccion_numero
                ,
            ],
            'hash' => $this->getReceptor()->getUsuario()->hash,
        ];
        $default_config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct(
            $default_config, $config_emisor
        );
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct(
            $default_config, $config
        );
        // consultar servicio web del contribuyente
        $ApiDtePdfClient = $this->getEmisor()->getApiClient('dte_pdf');
        if ($ApiDtePdfClient) {
            unset($config['hash']);
            $response = $ApiDtePdfClient->post($ApiDtePdfClient->url, $config);
        }
        // crear a partir de formato de PDF no estándar
        else if ($config['formato'] != 'estandar') {
            $apps = $this->getEmisor()->getApps('dtepdfs');
            if (
                empty($apps[$config['formato']])
                || empty($apps[$config['formato']]->getConfig()->disponible)
            ) {
                throw new \Exception('Formato de PDF '.$config['formato'].' no se encuentra disponible.', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web de LibreDTE
        else {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($config['hash']);
            unset($config['hash']);
            $response = $rest->post(url('/api/utilidades/documentos/generar_pdf'), $config);
        }
        // procesar respuesta
        if ($response === false) {
            throw new \Exception(implode("\n", $rest->getErrors(), 500));
        }
        if ($response['status']['code'] != 200) {
            throw new \Exception($response['body'], $response['status']['code']);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

    /**
     * Método que prueba el XML para corroborar eventual problema por archivo
     * con codificación errónea.
     */
    public function testXML()
    {
        $filtros = [
            'codigo' => $this->codigo,
            'soloPendientes' => false,
        ];
        try {
            $docs = (new Model_DteIntercambios())
                ->setContribuyente($this->getReceptor())
                ->getDocumentos($filtros)
            ;
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Método que indica si el intercambio es el último (según código).
     */
    public function esUltimoIntercambio(): bool
    {
        $ultimo_codigo = (new Model_DteIntercambios())
            ->setContribuyente($this->getReceptor())
            ->getUltimoCodigo()
        ;
        return $ultimo_codigo == $this->codigo;
    }

}
