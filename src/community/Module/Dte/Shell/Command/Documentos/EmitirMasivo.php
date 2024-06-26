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
 * Comando que permite emitir masivamente DTE a partir de un archivo CSV.
 */
class Shell_Command_Documentos_EmitirMasivo extends \Shell_App
{

    private $monedas = [
        'USD' => 'DOLAR USA',
        'EUR' => 'EURO',
        'CLP' => 'PESO CL',
    ]; // Tipo moneda para documentos de exportación

    private $time_start;

    public function main($emisor, $archivo, $usuario, $dte_real = false, $email = false, $pdf = false)
    {
        $this->time_start = microtime(true);
        // crear emisor/usuario y verificar permisos
        $Emisor = new Model_Contribuyente($emisor);
        if (!$usuario) {
            $usuario = $Emisor->usuario;
        }
        $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($usuario);
        if (!$Emisor->usuarioAutorizado($Usuario, '/dte/documentos/emitir')) {
            $this->notificarResultado($Emisor, $Usuario, 'Usuario '.$Usuario->usuario.' no está autorizado a operar con la empresa '.$Emisor->getNombre(), $dte_real, $email, $pdf);
            return 1;
        }
        // verificar sea leible
        if (!is_readable($archivo)) {
            $this->notificarResultado($Emisor, $Usuario, 'Archivo '.$archivo.' no puede ser leído', $dte_real, $email, $pdf);
            return 1;
        }
        // verificar archivo sea UTF-8
        exec('file -i '.$archivo, $output);
        $aux = explode('charset=', $output[0]);
        if (!isset($aux[1]) || !in_array($aux[1], ['us-ascii', 'utf-8'])) {
            $this->notificarResultado($Emisor, $Usuario, 'Codificación del archivo es '.$aux[1].' y debe ser utf-8', $dte_real, $email, $pdf);
            return 1;
        }
        // cargar archivo y crear documentos
        $datos = \sowerphp\general\Utility_Spreadsheet_CSV::read($archivo);
        if (strpos($archivo, '/tmp') === 0) {
            unlink($archivo);
        }
        $datos[0][] = 'resultado_codigo';
        $datos[0][] = 'resultado_glosa';
        $n_datos = count($datos);
        $documentos = [];
        $documento = null;
        $error_formato = false;
        for ($i=1; $i<$n_datos; $i++) {
            // fila es un documento nuevo
            if (!empty($datos[$i][0])) {
                // si existe un documento asignado previamente se agrega al listado
                if ($documento) {
                    $documentos[] = $documento;
                }
                // se crea documento de la fila que se está viendo
                try {
                    $documento = $this->crearDocumento($datos[$i]);
                    // verificar que el usuario esté autorizado a emitir el tipo de documento
                    if (!$Emisor->documentoAutorizado($documento['Encabezado']['IdDoc']['TipoDTE'], $Usuario)) {
                        $error_formato = true;
                        $datos[$i][] = 2;
                        $datos[$i][] = 'No está autorizado a emitir el tipo de documento '.$documento['Encabezado']['IdDoc']['TipoDTE'];
                    }
                    // si se quiere enviar por correo, verificar que exista correo
                    else if ($email && empty($documento['Encabezado']['Receptor']['CorreoRecep'])) {
                        $error_formato = true;
                        $datos[$i][] = 3;
                        $datos[$i][] = 'Debe indicar correo del receptor';
                    }
                } catch (\Exception $e) {
                    $error_formato = true;
                    $datos[$i][] = 1;
                    $datos[$i][] = $e->getMessage();
                }
            }
            // si la fila no es documento nuevo, se agrega el detalle al documento que ya existe
            else {
                try {
                    if (!empty($datos[$i][13])) {
                        $datosItem = array_merge(
                            // Datos originales del item (vienen juntos en el archivo).
                            array_slice($datos[$i], 11, 8),
                            // Datos adicionales del item (vienen después del item, "al final",
                            // porque se añadieron después de los previos al archivo)
                            [
                                // CodImpAdic.
                                !empty($datos[$i][38]) ? $datos[$i][38] : null,
                            ]
                        );
                        $this->agregarItem($documento, $datosItem);
                    }
                    $this->agregarReferencia($documento, array_slice($datos[$i], 28, 5));
                } catch (\Exception $e) {
                    $error_formato = true;
                    $datos[$i][] = 1;
                    $datos[$i][] = $e->getMessage();
                }
            }
        }
        $documentos[] = $documento;
        // si hay errores de formato se notifica al usuario y se detiene la ejecución
        if ($error_formato) {
            $this->notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email, $pdf);
            return 1;
        }
        // si se solicitó incluir los PDF se crea directorio para irlos generando
        $pdf = (bool)$pdf;
        if ($pdf) {
            $dir = DIR_TMP.'/libredte_dte_emitido_pdf_'.$Emisor->rut.'_'.md5(date('U').$Usuario->ultimo_ingreso_hash);
            if (file_exists($dir)) {
                \sowerphp\general\Utility_File::rmdir($dir);
            }
            if (file_exists($dir)) {
                $pdf = __('Error al crear directorio para ir guardando los PDF (%s) ya existe y no se pudo eliminar', $dir);
            } else {
                mkdir($dir);
            }
        }
        if ($pdf && is_string($pdf)) {
            $this->notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email, $pdf);
            return 1;
        }
        // ir generando cada documento
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($Usuario->hash);
        foreach($documentos as $dte) {
            if ($this->verbose) {
                $this->out('Generando DTE T'.$dte['Encabezado']['IdDoc']['TipoDTE'].'F'.$dte['Encabezado']['IdDoc']['Folio']);
            }
            // agregar RUT emisor
            $dte['Encabezado']['Emisor']['RUTEmisor'] = $Emisor->rut.'-'.$Emisor->dv;
            // extraer configuración adicional del DTE si viene en el arreglo
            if (!empty($dte['LibreDTE'])) {
                $dte_config = $dte['LibreDTE'];
                unset($dte['LibreDTE']);
            }
            // emitir DTE temporal
            $response = $rest->post(url('/api/dte/documentos/emitir'), $dte);
            if ($response['status']['code'] != 200) {
                $this->documentoAgregarResultado(
                    $datos,
                    $dte['Encabezado']['IdDoc']['TipoDTE'],
                    $dte['Encabezado']['IdDoc']['Folio'],
                    4,
                    $response['body']
                );
                continue;
            }
            // procesar DTE temporal (ya que no se genera el real)
            if (!$dte_real) {
                // crear PDF del DTE temporal
                if ($pdf) {
                    $response_pdf = $rest->get(url('/api/dte/dte_tmps/pdf/'.$response['body']['receptor'].'/'.$response['body']['dte'].'/'.$response['body']['codigo'].'/'.$response['body']['emisor'].'?cotizacion=1'));
                    if ($response_pdf['status']['code'] == 200) {
                        $filename = !empty($dte_config['pdf']['nombre'])
                            ? $dte_config['pdf']['nombre']
                            : 'LibreDTE_{rut}_{folio}'
                        ;
                        $file_pdf = $dir.'/'.str_replace(
                            ['{rut}', '{dv}', '{dte}', '{folio}'],
                            [$Emisor->rut, $Emisor->dv, $response['body']['dte'], $response['body']['dte'].'-'.strtoupper(substr($response['body']['codigo'],0,7))],
                            $filename
                        ).'.pdf';
                        file_put_contents($file_pdf, $response_pdf['body']);
                    }
                }
                // enviar DTE temporal por correo al receptor
                if ($email) {
                    $DteTmp = new Model_DteTmp(
                        $response['body']['emisor'],
                        $response['body']['receptor'],
                        $response['body']['dte'],
                        $response['body']['codigo']
                    );
                    try {
                        // enviar el correo indicado en el archivo
                        if (!empty($dte['Encabezado']['Receptor']['CorreoRecep'])) {
                            $DteTmp->email($dte['Encabezado']['Receptor']['CorreoRecep']);
                        }
                        // tratar de enviar a correos existentes en la base de datos
                        else {
                            $DteTmp->email();
                        }
                    } catch (\Exception $e) {
                        $this->documentoAgregarResultado(
                            $datos,
                            $dte['Encabezado']['IdDoc']['TipoDTE'],
                            $dte['Encabezado']['IdDoc']['Folio'],
                            6,
                            $e->getMessage()
                        );
                        continue;
                    }
                }
            }
            // emitir DTE real
            else {
                // consumir servicio web
                $response = $rest->post(url('/api/dte/documentos/generar'), $response['body']);
                if ($response['status']['code'] != 200) {
                    $this->documentoAgregarResultado(
                        $datos,
                        $dte['Encabezado']['IdDoc']['TipoDTE'],
                        $dte['Encabezado']['IdDoc']['Folio'],
                        5,
                        $response['body']
                    );
                    continue;
                }
                // crear PDF del DTE real
                if ($pdf === true) {
                    $response_pdf = $rest->get(url('/api/dte/dte_emitidos/pdf/'.$response['body']['dte'].'/'.$response['body']['folio'].'/'.$response['body']['emisor']));
                    if ($response_pdf['status']['code'] == 200) {
                        $filename = !empty($dte_config['pdf']['nombre'])
                            ? $dte_config['pdf']['nombre']
                            : 'LibreDTE_{rut}_T{dte}F{folio}'
                        ;
                        $file_pdf = $dir.'/'.str_replace(
                            ['{rut}', '{dv}', '{dte}', '{folio}'],
                            [$Emisor->rut, $Emisor->dv, $response['body']['dte'], $response['body']['folio']],
                            $filename
                        ).'.pdf';
                        file_put_contents($file_pdf, $response_pdf['body']);
                    }
                }
                // enviar DTE real por correo al receptor
                if ($email) {
                    $DteEmitido = new Model_DteEmitido(
                        $response['body']['emisor'],
                        $response['body']['dte'],
                        $response['body']['folio'],
                        $Emisor->enCertificacion()
                    );
                    try {
                        // enviar el correo indicado en el archivo
                        if (!empty($dte['Encabezado']['Receptor']['CorreoRecep'])) {
                            $DteEmitido->email($dte['Encabezado']['Receptor']['CorreoRecep'], null, null, true);
                        }
                        // tratar de enviar a correos existentes en la base de datos
                        else {
                            $DteEmitido->email($DteEmitido->getEmails(), null, null, true);
                        }
                    } catch (\Exception $e) {
                        $this->documentoAgregarResultado(
                            $datos,
                            $dte['Encabezado']['IdDoc']['TipoDTE'],
                            $dte['Encabezado']['IdDoc']['Folio'],
                            6,
                            $e->getMessage()
                        );
                        continue;
                    }
                }
            }
        }
        // si se solicitó PDF entonces se comprime directorio y se entrega
        if ($pdf) {
            $compress = 'zip';
            \sowerphp\general\Utility_File::compress(
                $dir, ['format' => $compress, 'delete' => true, 'download' => false]
            );
            $output = $dir.'.'.$compress;
            $filename = DIR_STATIC.'/emision_masiva_pdf/'.basename($output);
            if (!rename($output, $filename)) {
                $pdf = 'Error al mover el archivo comprimido al directorio de descarga.';
            } else {
                $pdf = url('/static/emision_masiva_pdf/'.basename($filename)).' (enlace válido por 24 horas).';
            }
        }
        // notificar al usuario que solicitó la emisión masiva
        $this->notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email, $pdf);
        // estadisticas y terminar
        $this->showStats();
        return 0;
    }

    private function crearDocumento($datos)
    {
        // verificar datos mínimos
        if (empty($datos[0])) {
            throw new \Exception('Falta tipo de documento.');
        }
        if (empty($datos[1])) {
            throw new \Exception('Falta folio del documento.');
        }
        if (empty($datos[4])) {
            throw new \Exception('Falta RUT del receptor.');
        }
        // verificar datos si no es boleta
        if (!in_array($datos[0], [39, 41])) {
            if (empty($datos[5])) {
                throw new \Exception('Falta razón social del receptor.');
            }
            if (empty($datos[6])) {
                throw new \Exception('Falta giro del receptor.');
            }
            if (empty($datos[9])) {
                throw new \Exception('Falta dirección del receptor.');
            }
            if (empty($datos[10])) {
                throw new \Exception('Falta comuna del receptor.');
            }
        }
        // armar dte
        $documento = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => (int)$datos[0],
                    'Folio' => (int)$datos[1],
                ],
                'Receptor' => [
                    'RUTRecep' => str_replace('.', '', $datos[4]),
                ],
            ],
            'Detalle' => [],
        ];
        if (!empty($datos[2])) {
            if (!\sowerphp\general\Utility_Date::check($datos[2])) {
                throw new \Exception('Fecha emisión '.$datos[2].' es incorrecta, debe ser formato AAAA-MM-DD.');
            }
            $documento['Encabezado']['IdDoc']['FchEmis'] = $datos[2];
        }
        if (!empty($datos[3])) {
            if (!\sowerphp\general\Utility_Date::check($datos[3])) {
                throw new \Exception('Fecha vencimiento '.$datos[3].' es incorrecta, debe ser formato AAAA-MM-DD.');
            }
            $documento['Encabezado']['IdDoc']['FchVenc'] = $datos[3];
        }
        if (!empty($datos[5])) {
            $documento['Encabezado']['Receptor']['RznSocRecep'] = mb_substr(trim($datos[5]), 0, 100);
        }
        if (!empty($datos[6])) {
            $documento['Encabezado']['Receptor']['GiroRecep'] = mb_substr(trim($datos[6]), 0, 40);
        }
        if (!empty($datos[7])) {
            $documento['Encabezado']['Receptor']['Contacto'] = mb_substr(trim($datos[7]), 0, 80);
        }
        if (!empty($datos[8])) {
            if (!filter_var($datos[8], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Correo electrónico '.$datos[8].' no es válido.');
            }
            $documento['Encabezado']['Receptor']['CorreoRecep'] = mb_substr(trim($datos[8]), 0, 80);
        }
        if (!empty($datos[9])) {
            $documento['Encabezado']['Receptor']['DirRecep'] = mb_substr(trim($datos[9]), 0, 70);
        }
        if (!empty($datos[10])) {
            $documento['Encabezado']['Receptor']['CmnaRecep'] = mb_substr(trim($datos[10]), 0, 20);
        }
        if (!empty($datos[19])) {
            $documento['Encabezado']['IdDoc']['TermPagoGlosa'] = mb_substr(trim($datos[19]), 0, 100);
        }
        if (!empty($datos[20])) {
            if (!\sowerphp\general\Utility_Date::check($datos[20])) {
                throw new \Exception('Fecha período desde '.$datos[20].' es incorrecta, debe ser formato AAAA-MM-DD.');
            }
            $documento['Encabezado']['IdDoc']['PeriodoDesde'] = $datos[20];
        }
        if (!empty($datos[21])) {
            if (!\sowerphp\general\Utility_Date::check($datos[21])) {
                throw new \Exception('Fecha período hasta '.$datos[21].' es incorrecta, debe ser formato AAAA-MM-DD.');
            }
            $documento['Encabezado']['IdDoc']['PeriodoHasta'] = $datos[21];
        }
        if (in_array($documento['Encabezado']['IdDoc']['TipoDTE'], [110,111,112])) {
            // agregar moneda
            if (empty($datos[33])) {
                $datos[33] = 'USD';
            }
            if (empty($this->monedas[$datos[33]])) {
                throw new \Exception('El tipo de moneda '.$datos[33].' no está permitido, solo: USD, EUR y CLP.');
            }
            $documento['Encabezado']['Totales']['TpoMoneda'] = $this->monedas[$datos[33]];
            // agregar ID del receptor
            if (!empty($datos[34])) {
                $documento['Encabezado']['Receptor']['Extranjero']['NumId'] = mb_substr(trim($datos[34]), 0, 20);
            }
        }
        if (!empty($datos[35])) {
            if (strpos($datos[35], '%')) {
                $TpoValor_global = '%';
                $ValorDR_global = (float)substr($datos[35], 0, -1);
            } else {
                $TpoValor_global = '$';
                $ValorDR_global = (float)$datos[35];
            }
            $documento['DscRcgGlobal'][] = [
                'TpoMov' => 'D',
                'TpoValor' => $TpoValor_global,
                'ValorDR' => $ValorDR_global,
                'IndExeDR' => 1,
            ];
        }
        if (!empty($datos[36])) {
            $documento['LibreDTE']['pdf']['nombre'] = $datos[36];
        }
        if (!empty($datos[37])) {
            if (!in_array($datos[37], [1,2,3])) {
                throw new \Exception('Forma de pago de código '.$datos[37].' es incorrecta, debe ser: 1 (contado), 2 (crédito) o 3 (sin costo).');
            }
            $documento['Encabezado']['IdDoc']['FmaPago'] = (int)$datos[37];
        }
        $datosItem = array_merge(
            // Datos originales del item (vienen juntos en el archivo).
            array_slice($datos, 11, 8),
            // Datos adicionales del item (vienen después del item, "al final",
            // porque se añadieron después de los previos al archivo)
            [
                // CodImpAdic.
                !empty($datos[38]) ? $datos[38] : null,
            ]
        );
        $this->agregarItem($documento, $datosItem);
        $this->agregarTransporte($documento, array_slice($datos, 22, 6));
        $this->agregarReferencia($documento, array_slice($datos, 28, 5));
        return $documento;
    }

    private function agregarItem(&$documento, $item)
    {
        // verificar datos mínimos
        if (empty($item[2])) {
            throw new \Exception('Falta nombre del item.');
        }
        if (empty($item[4])) {
            throw new \Exception('Falta cantidad del item.');
        }
        if (empty($item[6])) {
            throw new \Exception('Falta precio del item.');
        }
        // crear detalle
        $detalle = [
            'NmbItem' => mb_substr(trim($item[2]), 0, 80),
            'QtyItem' => (float)str_replace(',', '.', $item[4]),
            'PrcItem' => (float)str_replace(',', '.', $item[6]),
        ];
        if (!empty($item[0])) {
            $detalle['CdgItem'] = [
                'TpoCodigo' => 'INT1',
                'VlrCodigo' => mb_substr(trim($item[0]), 0, 35),
            ];
        }
        if (!empty($item[1])) {
            $detalle['IndExe'] = (int)$item[1];
        }
        if (!empty($item[3])) {
            $detalle['DscItem'] = mb_substr(trim($item[3]), 0, 1000);
        }
        if (!empty($item[5])) {
            $detalle['UnmdItem'] = mb_substr(trim($item[5]), 0, 4);
        }
        if (!empty($item[7])) {
            if (strpos($item[7], '%')) {
                $detalle['DescuentoPct'] = (float)substr($item[7], 0, -1);
            } else {
                $detalle['DescuentoMonto'] = (float)$item[7];
            }
        }
        if (!empty($item[8])) {
            $detalle['CodImpAdic'] = (int)trim($item[8]);
        }
        // agregar detalle al documento
        $documento['Detalle'][] = $detalle;
    }

    private function agregarTransporte(&$documento, $transporte)
    {
        $vacios = true;
        foreach ($transporte as $t) {
            if (!empty($t)) {
                $vacios = false;
            }
        }
        if ($vacios) {
            return;
        }
        if ($transporte[0]) {
            $documento['Encabezado']['Transporte']['Patente'] = mb_substr(trim($transporte[0]),0,8);
        }
        if ($transporte[1]) {
            $documento['Encabezado']['Transporte']['RUTTrans'] = mb_substr(str_replace('.','',trim($transporte[1])),0,10);
        }
        if ($transporte[2] && $transporte[3]) {
            $documento['Encabezado']['Transporte']['Chofer']['RUTChofer'] = mb_substr(str_replace('.','',trim($transporte[2])),0,10);
            $documento['Encabezado']['Transporte']['Chofer']['NombreChofer'] = mb_substr(trim($transporte[3]),0,30);
        }
        if ($transporte[4]) {
            $documento['Encabezado']['Transporte']['DirDest'] = mb_substr(trim($transporte[4]),0,70);
        }
        if ($transporte[5]) {
            $documento['Encabezado']['Transporte']['CmnaDest'] = mb_substr(trim($transporte[5]),0,20);
        }
    }

    private function agregarReferencia(&$documento, $referencia)
    {
        $Referencia = [];
        $vacios = true;
        foreach ($referencia as $r) {
            if (!empty($r)) {
                $vacios = false;
            }
        }
        if ($vacios) {
            return;
        }
        if (empty($referencia[0])) {
            throw new \Exception('Tipo del documento de referencia no puede estar vacío.');
        }
        $Referencia['TpoDocRef'] = mb_substr(trim($referencia[0]),0,3);
        if (empty($referencia[1])) {
            throw new \Exception('Folio del documento de referencia no puede estar vacío.');
        }
        $Referencia['FolioRef'] = mb_substr(trim($referencia[1]),0,18);
        if (empty($referencia[2]) || !\sowerphp\general\Utility_Date::check($referencia[2])) {
            throw new \Exception('Fecha del documento de referencia debe ser en formato AAAA-MM-DD.');
        }
        $Referencia['FchRef'] = $referencia[2];
        if (!empty($referencia[3])) {
                $Referencia['CodRef'] = (int)$referencia[3];
        }
        if (!empty($referencia[4])) {
                $Referencia['RazonRef'] = mb_substr(trim($referencia[4]), 0, 90);
        }
        $documento['Referencia'][] = $Referencia;
    }

    private function documentoAgregarResultado(&$datos, $tipo_dte, $folio, $resultado_codigo, $resultado_glosa)
    {
        foreach ($datos as &$d) {
            if ($d[0] == $tipo_dte && $d[1] == $folio) {
                $d[] = $resultado_codigo;
                $d[] = $resultado_glosa;
                break;
            }
        }
    }

    private function notificarResultado($Emisor, $Usuario, $datos, $dte_real, $email, $pdf)
    {
        // datos del envío
        $id = date('YmdHis');
        $tiempo = round(microtime(true) - $this->time_start, 2);
        if (is_array($datos)) {
            $file = [
                'tmp_name' => tempnam('/tmp', $Emisor->rut.'_dte_masivo_'),
                'name' => $Emisor->rut.'_dte_masivo_'.$id.'.csv',
                'type' => 'text/csv',
            ];
            \sowerphp\general\Utility_Spreadsheet_CSV::save($datos, $file['tmp_name']);
        } else $file = null;
        // enviar correo
        $titulo = 'Resultado emisión masiva de DTE #'.$id;
        $msg = $Usuario->nombre.','."\n\n";
        if ($file) {
            $msg .= 'Se adjunta archivo CSV con el detalle de la emisión para cada DTE solicitado.'."\n\n";
        } else if ($datos) {
            $msg .= 'Ha ocurrido un error y el archivo no ha podido ser procesado: '.$datos."\n\n";
        }
        $msg .= '- Generar DTE real: '.($dte_real?'Si':'No')."\n";
        $msg .= '- Enviar DTE por correo: '.($email?'Si':'No')."\n";
        $msg .= '- Descarga de PDF: '.($pdf?(is_string($pdf) ? $pdf:'Si'):'No')."\n";
        $msg .= '- Tiempo ejecución: '.num($tiempo).' segundos'."\n";
        // mensaje por consola con el resultado (mismo que se envía por email)
        $this->out("\n".$msg."\n");
        if ($Emisor->notificar($titulo, $msg, $Usuario->email, null, $file)) {
            $this->out('Correo enviado al emisor con el resultado.'."\n");
        } else {
            $this->out('No fue posible enviar el correo al emisor con el resultado.'."\n");
        }
        // borrar archivo si existe
        if ($file) {
            unlink($file['tmp_name']);
        }
    }

}
