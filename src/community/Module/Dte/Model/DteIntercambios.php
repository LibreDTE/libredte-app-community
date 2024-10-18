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

use sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "dte_intercambio" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_DteIntercambios extends Model_Plural
{

    /**
     * Método que entrega el total de documentos de intercambio pendientes de ser procesados.
     */
    public function getTotalPendientes(): int
    {
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM dte_intercambio
            WHERE receptor = :receptor AND certificacion = :certificacion AND usuario IS NULL
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
    }

    /**
     * Método que crea los filtros para ser usados en las consultas de documentos recibidos.
     */
    private function crearFiltrosDocumentos(array $filtros): array
    {
        list($col_documentos, $col_totales) = $this->getDatabaseConnection()->xml('i.archivo_xml', [
            '/*/SetDTE/DTE/*/Encabezado/IdDoc/TipoDTE|/*/SetDTE/DTE/*/Encabezado/IdDoc/Folio',
            '/*/SetDTE/DTE/*/Encabezado/Totales/MntTotal',
        ], 'http://www.sii.cl/SiiDte');
        $filtros = array_merge([
            'soloPendientes' => true,
            'p' => 0, // página de intercambios
        ], $filtros);
        $where = [
            'i.receptor = :receptor',
            'i.certificacion = :certificacion',
        ];
        $vars = [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ];
        if (!empty($filtros['codigo'])) {
            $where[] = 'i.codigo = :codigo';
            $vars[':codigo'] = $filtros['codigo'];
        }
        if (!empty($filtros['recibido_desde'])) {
            $where[] = 'i.fecha_hora_email >= :recibido_desde';
            $vars[':recibido_desde'] = $filtros['recibido_desde'];
        }
        if (!empty($filtros['recibido_hasta'])) {
            $where[] = 'i.fecha_hora_email <= :recibido_hasta';
            $vars[':recibido_hasta'] = $filtros['recibido_hasta'].' 23:59:59';
        }
        if (!empty($filtros['asunto'])) {
            $where[] = 'LOWER(i.asunto) LIKE :asunto';
            $vars[':asunto'] = '%'.strtolower($filtros['asunto']).'%';
        }
        if (!empty($filtros['de'])) {
            $where[] = 'LOWER(i.de) LIKE :de';
            $vars[':de'] = '%'.strtolower($filtros['de']).'%';
        }
        if (!empty($filtros['emisor'])) {
            if (is_numeric($filtros['emisor'])) {
                $where[] = 'i.emisor = :emisor';
                $vars['emisor'] = $filtros['emisor'];
            }
            else if (strpos($filtros['emisor'], '-')) {
                $part0 = explode('-', str_replace('.', '', $filtros['emisor']))[0];
                if (is_numeric($part0)) {
                    $where[] = 'i.emisor = :emisor';
                    $vars['emisor'] = $part0;
                } else {
                    $where[] = 'LOWER(e.razon_social) LIKE :emisor';
                    $vars['emisor'] = '%'.strtolower($filtros['emisor']).'%';
                }
            } else {
                $where[] = 'LOWER(e.razon_social) LIKE :emisor';
                $vars['emisor'] = '%'.strtolower($filtros['emisor']).'%';
            }
        }
        if (!empty($filtros['firma_desde'])) {
            $where[] = 'i.fecha_hora_firma >= :firma_desde';
            $vars[':firma_desde'] = $filtros['firma_desde'];
        }
        if (!empty($filtros['firma_hasta'])) {
            $where[] = 'i.fecha_hora_firma <= :firma_hasta';
            $vars[':firma_hasta'] = $filtros['firma_hasta'].' 23:59:59';
        }
        if (isset($filtros['estado'])) {
            // solo pendientes
            if ($filtros['estado'] == 1) {
                $where[] = 'i.estado IS NULL';
            }
            // solo procesados
            else if ($filtros['estado'] == 2) {
                $where[] = 'i.estado IS NOT NULL';
            }
            // solo aceptados
            else if ($filtros['estado'] == 3) {
                $where[] = 'i.estado = 0';
            }
            // solo rechazados
            else if ($filtros['estado'] == 4) {
                $where[] = 'i.estado != 0';
            }
        } else if ($filtros['soloPendientes']) {
            $where[] = 'i.estado IS NULL';
        }
        if (!empty($filtros['usuario'])) {
            if ($filtros['usuario'] == '!null') {
                $where[] = 'i.usuario IS NOT NULL';
            } else {
                $where[] = 'u.usuario = :usuario';
                $vars[':usuario'] = $filtros['usuario'];
            }
        }
        // si se debe hacer búsqueda dentro de los XML
        if (!empty($filtros['dte'])) {
            $dte_where = $this->getDatabaseConnection()->xml(
                'i.archivo_xml',
                '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/TipoDTE',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = '((is_numeric('.$dte_where.') = true AND '.$dte_where.'::INTEGER = :dte_n) OR (is_numeric('.$dte_where.') = false AND '.$dte_where.' LIKE :dte_s))';
            $vars[':dte_n'] = (int)$filtros['dte'];
            $vars[':dte_s'] = '%'.(int)$filtros['dte'].'%';
        }
        if (!empty($filtros['folio'])) {
            $folio_where = $this->getDatabaseConnection()->xml(
                'i.archivo_xml',
                '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/Folio',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = '((is_numeric('.$folio_where.') = true AND '.$folio_where.'::INTEGER = :folio_n) OR (is_numeric('.$folio_where.') = false AND '.$folio_where.' LIKE :folio_s))';
            $vars[':folio_n'] = (int)$filtros['folio'];
            $vars[':folio_s'] = '%'.(int)$filtros['folio'].'%';
        }
        if (!empty($filtros['item'])) {
            $item_where = $this->getDatabaseConnection()->xml(
                'i.archivo_xml',
                '/*/SetDTE/DTE/Documento/Detalle/NmbItem',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = 'LOWER('.$item_where.') LIKE :item';
            $vars[':item'] = '%'.strtolower($filtros['item']).'%';
        }
        if (!empty($filtros['fecha_emision_desde'])) {
            $fecha_emision_desde = $this->getDatabaseConnection()->xml(
                'i.archivo_xml',
                '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/FchEmis',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = $fecha_emision_desde.' >= :fecha_emision_desde';
            $vars[':fecha_emision_desde'] = $filtros['fecha_emision_desde'];
        }
        if (!empty($filtros['fecha_emision_hasta'])) {
            $fecha_emision_hasta = $this->getDatabaseConnection()->xml(
                'i.archivo_xml',
                '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/FchEmis',
                'http://www.sii.cl/SiiDte'
            );
            $where[] = $fecha_emision_hasta.' <= :fecha_emision_hasta';
            $vars[':fecha_emision_hasta'] = $filtros['fecha_emision_hasta'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = $col_totales.'::INTEGER >= :total_desde';
            $vars[':total_desde'] = (int)$filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = $col_totales.'::INTEGER <= :total_hasta';
            $vars[':total_hasta'] = (int)$filtros['total_hasta'];
        }
        if (!empty($filtros['xml'])) {
            $i = 1;
            foreach ((array)$filtros['xml'] as $nodo => $valor) {
                $nodo = preg_replace('/[^A-Za-z\/]/', '', $nodo);
                $where[] = 'LOWER('.$this->getDatabaseConnection()->xml(
                    'i.archivo_xml',
                    '/*/SetDTE/DTE/Documento/'.$nodo,
                    'http://www.sii.cl/SiiDte'
                ) . ') LIKE :xml' . $i;
                $vars[':xml'.$i] = '%'.strtolower($valor).'%';
                $i++;
            }
        }
        // entregar filtros
        return [$where, $vars, $col_documentos, $col_totales];
    }

    /**
     * Método que cuenta los casos de intercambio del contribuyente.
     */
    public function countDocumentos(array $filtros = []): int
    {
        list($where, $vars) = $this->crearFiltrosDocumentos($filtros);
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT
                COUNT(*)
            FROM
                dte_intercambio AS i
                LEFT JOIN contribuyente AS e ON i.emisor = e.rut
                LEFT JOIN usuario AS u ON i.usuario = u.id
            WHERE '.implode(' AND ',$where).'
        ', $vars);
    }

    /**
     * Método que entrega la tabla con los casos de intercambio del contribuyente.
     */
    public function getDocumentos(array $filtros = []): array
    {
        list($where, $vars, $col_documentos, $col_totales) = $this->crearFiltrosDocumentos($filtros);
        // armar limite
        if (!empty($filtros['p'])) {
            $limit = config('app.ui.pagination.registers');
            $offset = ($filtros['p'] - 1) * $limit;
            $limit = 'LIMIT '.$limit.' OFFSET '.$offset;
        } else {
            $limit = '';
        }
        // crear consulta
        $intercambios = $this->getDatabaseConnection()->getTable('
            SELECT
                i.codigo,
                i.emisor,
                e.razon_social,
                '.$col_documentos.' AS documentos,
                i.fecha_hora_email,
                '.$col_totales.' AS totales,
                i.documentos AS n_documentos,
                i.estado,
                u.usuario
            FROM
                dte_intercambio AS i
                LEFT JOIN contribuyente AS e ON i.emisor = e.rut
                LEFT JOIN usuario AS u ON i.usuario = u.id
            WHERE '.implode(' AND ',$where).'
            ORDER BY i.fecha_hora_email DESC
            '.$limit.'
        ', $vars);
        // corregir datos
        foreach ($intercambios as &$i) {
            if (!empty($i['razon_social'])) {
                $i['emisor'] = $i['razon_social'];
            }
            if (isset($i['estado']) && is_numeric($i['estado'])) {
                $i['estado'] = (bool)!$i['estado'];
            }
            if (!empty($i['documentos'])) {
                $nuevo_dte = true;
                $n_letras = strlen($i['documentos']);
                for ($j=0; $j<$n_letras; $j++) {
                    if ($i['documentos'][$j] == ',') {
                        $nuevo_dte = !$nuevo_dte;
                        if ($nuevo_dte) {
                            $i['documentos'][$j] = '|';
                        }
                    }
                }
                $documentos = explode('|', $i['documentos']);
                foreach ($documentos as &$d) {
                    $aux = explode(',', $d);
                    if (isset($aux[1])) {
                        list($tipo, $folio) = $aux;
                        $d = 'T'.$tipo.'F'.(int)$folio;
                    }
                }
                $i['documentos'] = $documentos;
            } else {
                $i['documentos'] = $i['n_documentos'];
            }
            $i['totales'] = !empty($i['totales']) ? explode(',', $i['totales']) : [];
            unset($i['razon_social'], $i['n_documentos']);
        }
        // entregar los intercambios
        return $intercambios;
    }

    /**
     * Método para actualizar la bandeja de intercambio. Guarda los DTE
     * recibidos por intercambio y guarda los acuses de recibos de DTE
     * enviados por otros contribuyentes.
     */
    public function actualizar(int $dias = 7): array
    {
        // Despachar evento para verificar cosas previo a actualizar la bandeja
        // de intercambio del contribuyente.
        $event_actualizar = event(
            'dte_dte_intercambio_actualizar',
            [$this->getContribuyente()],
            true
        );
        // Si el evento entrega false la bandeja no se actualizará y el error
        // será de manera silenciosa.
        if ($event_actualizar === false) {
            return [
                'n_uids' => 0,
                'omitidos' => 0,
                'n_EnvioDTE' => 0,
                'n_EnvioRecibos' => 0,
                'n_RecepcionEnvio' => 0,
                'n_ResultadoDTE' => 0,
            ];
        }
        // Si el evento entrega un arreglo es el resultado de la actualización
        // de la bandeja de intercambio.
        else if (is_array($event_actualizar)) {
            return $event_actualizar;
        }
        // Obtener correo.
        try {
            $Imap = is_object($event_actualizar)
                ? $event_actualizar
                : $this->getContribuyente()->getEmailReceiver()
            ;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
        if (!$Imap) {
            throw new \Exception(__(
                'No fue posible conectar mediante IMAP a %s, verificar mailbox, usuario y/o contraseña de correo de intercambio:<br/>%s',
                $this->getContribuyente()->config_email_intercambio_imap,
                implode('<br/>', imap_errors())
            ), 500);
        }
        // obtener mensajes sin leer
        if ($dias) {
            $hoy = date('Y-m-d');
            $since = \sowerphp\general\Utility_Date::getPrevious($hoy, 'D', (int)$dias);
            $uids = $Imap->search('UNSEEN SINCE "'.$since.'"');
        } else {
            $uids = $Imap->search();
        }
        if (!$uids) {
            if ($dias) {
                throw new \Exception(__(
                    'No se encontraron documentos sin leer en los últimos %s días en el correo de intercambio.',
                    num($dias)
                ), 204);
            } else {
                throw new \Exception('No se encontraron documentos sin leer en el correo de intercambio.', 204);
            }
        }
        // procesar cada mensaje sin leer
        $n_EnvioDTE = $n_acuse = $n_EnvioRecibos = $n_RecepcionEnvio = $n_ResultadoDTE = 0;
        $errores = [];
        foreach ($uids as &$uid) {
            try {
                $m = $Imap->getMessage(
                    $uid,
                    [
                        'subtype' => ['PLAIN', 'HTML', 'XML'],
                        'extension' => ['xml']
                    ]
                );
            } catch (\Exception $e) {
                $errores[$uid] = $e->getMessage();
                continue;
            }
            if ($m && isset($m['attachments'][0])) {
                $datos_email = [
                    'fecha_hora_email' => $m['date'],
                    'asunto' => !empty($m['header']->subject)
                        ? substr($m['header']->subject, 0, 100)
                        : 'Sin asunto'
                    ,
                    'de' => substr($m['header']->from[0]->mailbox.'@'.$m['header']->from[0]->host, 0, 80),
                    'mensaje' => $m['body']['plain']
                        ? base64_encode($m['body']['plain'])
                        : null
                    ,
                    'mensaje_html' => $m['body']['html']
                        ? base64_encode($m['body']['html'])
                        : null
                    ,
                ];
                if (isset($m['header']->reply_to[0])) {
                    $datos_email['responder_a'] = substr($m['header']->reply_to[0]->mailbox.'@'.$m['header']->reply_to[0]->host, 0, 80);
                }
                $acuseContado = false;
                $n_attachments = count($m['attachments']);
                $procesados = 0;
                foreach ($m['attachments'] as $file) {
                    // si el archivo no tiene datos se omite
                    if (empty($file['data'])) {
                        $procesados++;
                        continue;
                    }
                    // tratar de procesar como EnvioDTE
                    try {
                        $procesarEnvioDTE = $this->procesarEnvioDTE($file, $datos_email);
                    } catch (\Exception $e) {
                        $procesarEnvioDTE = false;
                    }
                    if ($procesarEnvioDTE !== null) {
                        if ($procesarEnvioDTE) {
                            $n_EnvioDTE++;
                        }
                        $procesados++;
                        continue;
                    }
                    // tratar de procesar como Recibo
                    try {
                        $procesarRecibo = (new Model_DteIntercambioRecibo())
                            ->saveXML($this->getContribuyente(), $file['data'])
                        ;
                    } catch (\Exception $e) {
                        $procesarRecibo = false;
                    }
                    if ($procesarRecibo !== null) {
                        if ($procesarRecibo) {
                            $n_EnvioRecibos++;
                            if (!$acuseContado) {
                                $acuseContado = true;
                                $n_acuse++;
                            }
                        }
                        $procesados++;
                        continue;
                    }
                    // tratar de procesar como Recepción
                    try {
                        $procesarRecepcion = (new Model_DteIntercambioRecepcion())
                            ->saveXML($this->getContribuyente(), $file['data'])
                        ;
                    } catch (\Exception $e) {
                        $procesarRecepcion = false;
                    }
                    if ($procesarRecepcion !== null) {
                        if ($procesarRecepcion) {
                            $n_RecepcionEnvio++;
                            if (!$acuseContado) {
                                $acuseContado = true;
                                $n_acuse++;
                            }
                        }
                        $procesados++;
                        continue;
                    }
                    // tratar de procesar como Resultado
                    try {
                        $procesarResultado = (new Model_DteIntercambioResultado())
                            ->saveXML($this->getContribuyente(), $file['data'])
                        ;
                    } catch (\Exception $e) {
                        $procesarResultado = false;
                    }
                    if ($procesarResultado !== null) {
                        if ($procesarResultado) {
                            $n_ResultadoDTE++;
                            if (!$acuseContado) {
                                $acuseContado = true;
                                $n_acuse++;
                            }
                        }
                        $procesados++;
                        continue;
                    }
                }
                // marcar email como leído si fueron procesados todos los archivos adjuntos
                if ($procesados == $n_attachments) {
                    $Imap->setSeen($uid);
                }
            }
        }
        $n_uids = count($uids);
        $omitidos = $n_uids - $n_EnvioDTE - $n_acuse;
        return compact('n_uids', 'omitidos', 'n_EnvioDTE', 'n_EnvioRecibos', 'n_RecepcionEnvio', 'n_ResultadoDTE', 'errores');
    }

    /**
     * Método que procesa el archivo EnvioDTE recibido desde un contribuyente.
     * @param receptor RUT del receptor sin puntos ni dígito verificador.
     * @param datos_email Arreglo con los índices: fecha_hora_email, asunto, de, mensaje, mensaje_html.
     * @param file Arreglo con los índices: name, data, size y type.
     */
    public function procesarEnvioDTE(array $file, array $datos_email = [])
    {
        // preparar datos
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($file['data']) || !$EnvioDte->getID()) {
            return null; // no es un EnvioDTE, no se procesa
        }
        if ($EnvioDte->esBoleta()) {
            throw new \Exception('El XML es de boleta, no se procesa.');
        }
        $caratula = $EnvioDte->getCaratula();
        if (((int)(bool)!$caratula['NroResol']) != $this->getContribuyente()->enCertificacion()) {
            return null; // se deja sin procesar ya que no es del ambiente correcto
        }
        if (substr($caratula['RutReceptor'], 0, -2) != $this->getContribuyente()->rut) {
            throw new \Exception('El RUT del receptor no es válido.');
        }
        if (!isset($caratula['SubTotDTE'][0])) {
            $caratula['SubTotDTE'] = [$caratula['SubTotDTE']];
        }
        $documentos = 0;
        foreach($caratula['SubTotDTE'] as $SubTotDTE) {
            $documentos += $SubTotDTE['NroDTE'];
        }
        if (!$documentos) {
            throw new \Exception('El intercambio no tiene DTE.');
        }
        // si no hay datos de correo no se debe guardar y solo se está probando el XML
        if (empty($datos_email)) {
            throw new \Exception('Envio ok. No guardado. Sin datos de correo para guardar el XML.');
        }
        // preparar datos que se guardarán
        if (empty($file['name'])) {
            $file['name'] = md5($file['data']).'.xml';
        }
        $datos_enviodte = [
            'certificacion' => (int)(bool)!$caratula['NroResol'],
            'emisor' => substr($caratula['RutEmisor'], 0, -2),
            'fecha_hora_firma' => date('Y-m-d H:i:s', strtotime($caratula['TmstFirmaEnv'])),
            'documentos' => $documentos,
            'archivo' => $file['name'],
            'archivo_xml' => base64_encode($file['data']),
        ];
        $datos_enviodte['archivo_md5'] = md5($datos_enviodte['archivo_xml']);
        // crear objeto de intercambio
        $DteIntercambio = new Model_DteIntercambio();
        $DteIntercambio->set($datos_email + $datos_enviodte);
        $DteIntercambio->receptor = $this->getContribuyente()->rut;
        // si el documento ya existe en la bandeja de intercambio se omite
        if ($DteIntercambio->recibidoPreviamente()) {
            throw new \Exception('El intercambio ya había sido recibido previamente.');
        }
        // guardar envío de intercambio
        if (!$DteIntercambio->save()) {
            throw new \Exception('No fue posible guardar el intercambio.');
        }
        // si no se procesó el intercambio de manera automática se marca como DTE agregado (=true) para ser reportado
        return !$DteIntercambio->procesarRespuestaAutomatica() ? true : false;
    }

    /**
     * Método que entrega la cantidad de intercambios que se han recibido en el periodo.
     */
    public function countPeriodo($periodo = null): int
    {
        if (!$periodo) {
            $periodo = date('Ym');
        }
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'fecha_hora_email');
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM dte_intercambio
            WHERE receptor = :receptor AND '.$periodo_col.' = :periodo
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':periodo' => $periodo,
        ]);
    }

    /**
     * Método que busca el o los intercambios asociados a un DTE.
     * @warning Esta función es muy costosa, ya que debe buscar en los XML y además abrir luego cada intercambio para confirmar que el DTE que se encontró es correcto.
     */
    public function buscarIntercambiosDte($emisor, $dte, $folio): array
    {
        $dte_col = $this->getDatabaseConnection()->xml(
            'archivo_xml',
            '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/TipoDTE',
            'http://www.sii.cl/SiiDte'
        );
        $folio_col = $this->getDatabaseConnection()->xml(
            'archivo_xml',
            '/*/SetDTE/DTE/Documento/Encabezado/IdDoc/Folio',
            'http://www.sii.cl/SiiDte'
        );
        // buscar intercambios que probablemente sean
        $intercambios = (new Model_DteIntercambios())->setWhereStatement(
            [
                'receptor = :receptor',
                'certificacion = :certificacion',
                'emisor = :emisor',
                $dte_col.' LIKE :dte',
                $folio_col.' LIKE :folio',
            ],
            [
                ':receptor' => $this->getContribuyente()->rut,
                ':certificacion' => $this->getContribuyente()->enCertificacion(),
                ':emisor' => $emisor,
                ':dte' => '%'.$dte.'%',
                ':folio' => '%'.$folio.'%',
            ]
        )->getObjects();
        // verificar que el DTE solicitado esté en cada intercambio encontrado
        // esto es necesario porque la búsqueda no hace match perfecto entre TIPO DTE y FOLIO y podría haber elegido
        // una tupla incorrecta (¿se podría mejorar esto? -> revisar consultas a XML desde PostgreSQL)
        $intercambios_reales = [];
        foreach ($intercambios as $DteIntercambio) {
            if ($DteIntercambio->getDocumento($emisor, $dte, $folio)) {
                $DteIntercambio->certificacion = (int)$DteIntercambio->certificacion;
                $intercambios_reales[] = $DteIntercambio;
            }
        }
        return $intercambios_reales;
    }

    /**
     * Método que entrega el último código de intercambio usado por un receptor de DTE.
     */
    public function getUltimoCodigo(): int
    {
        $where = ['i.receptor = :receptor', 'i.certificacion = :certificacion'];
        $vars = [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ];
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT MAX(codigo)
            FROM dte_intercambio AS i
            WHERE '.implode(' AND ', $where).'
        ', $vars);
    }

}
