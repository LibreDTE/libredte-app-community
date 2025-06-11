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

use sowerphp\core\Network_Request as Request;

/**
 * Controlador para acciones del SII.
 */
class Controller_Sii extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite obtener los datos de la empresa desde el SII.
     */
    public function contribuyente_datos(Request $request, $rut)
    {
        $user = $request->user();
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $Emisor = (new Model_Contribuyentes())->get($rut);
            if (!$Emisor->usuarioAutorizado($user, 'admin')) {
                return redirect('/dte/contribuyentes/seleccionar')
                    ->withError(
                        __('Usted no es el administrador de la empresa solicitada.')
                    );
            }
            $Firma = $Emisor->getFirma($user->id);
            $certificacion = $Emisor->enCertificacion();
            $response = apigateway(
                '/sii/dte/contribuyentes/datos/'.$Emisor->getRUT().'?formato=html&certificacion='.$certificacion,
                [
                    'auth' => [
                        'cert' => [
                            'cert-data' => $Firma->getCertificate(),
                            'pkey-data' => $Firma->getPrivateKey(),
                        ],
                    ],
                ]
            );
            $this->response->sendAndExit($response['body']);
        }
        // se redirecciona al SII
        catch (\Exception $e) {
            return redirect('https://'.\sasco\LibreDTE\Sii::getServidor().'.sii.cl/cvc_cgi/dte/ad_empresa1');
        }
    }

    /**
     * Acción que permite obtener los usuarios de la empresa desde el SII.
     */
    public function contribuyente_usuarios(Request $request, $rut)
    {
        $user = $request->user();
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $Emisor = (new Model_Contribuyentes())->get($rut);
            if (!$Emisor->usuarioAutorizado($user, 'admin')) {
                return redirect('/dte/contribuyentes/seleccionar')
                    ->withError(
                        __('Usted no es el administrador de la empresa solicitada.')
                    );
            }
            $Firma = $Emisor->getFirma($user->id);
            if (!$Firma) {
                die('No hay firma electrónica asociada al usuario.');
            }
            $certificacion = $Emisor->enCertificacion();
            $response = apigateway(
                '/sii/dte/contribuyentes/usuarios/'.$Emisor->getRUT().'?formato=html&certificacion='.$certificacion,
                [
                    'auth' => [
                        'cert' => [
                            'cert-data' => $Firma->getCertificate(),
                            'pkey-data' => $Firma->getPrivateKey(),
                        ],
                    ],
                ]
            );
            $this->response->sendAndExit($response['body']);
        }
        // se redirecciona al SII
        catch (\Exception $e) {
            return redirect('https://'.\sasco\LibreDTE\Sii::getServidor().'.sii.cl/cvc_cgi/dte/eu_enrola_usuarios');
        }
    }

    /**
     * Acción que permite obtener si la empresa está o no autorizada para usar facturación electrónica.
     */
    public function contribuyente_autorizado($rut)
    {
        extract($this->request->getValidatedData([
            'certificacion' => \sasco\LibreDTE\Sii::PRODUCCION,
        ]));
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $response = apigateway(
                '/sii/dte/contribuyentes/autorizado/'.$rut.'?formato=html&certificacion='.$certificacion
            );
            $this->response->sendAndExit($response['body']);
        }
        // se redirecciona al SII
        catch (\Exception $e) {
            return redirect('https://'.\sasco\LibreDTE\Sii::getServidor($certificacion).'.sii.cl/cvc/dte/ee_empresas_dte.html');
        }
    }

    /**
     * Acción que permite obtener la situación tributaria de la empresa desde el SII.
     */
    public function contribuyente_situacion_tributaria($rut)
    {
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $response = apigateway(
                '/sii/contribuyentes/situacion_tributaria/tercero/'.$rut.'?formato=html'
            );
            $this->response->sendAndExit($response['body']);
        }
        // se redirecciona al SII
        catch (\Exception $e) {
            return redirect('https://zeus.sii.cl/cvc/stc/stc.html');
        }
    }

    /**
     * Acción que permite consultar el estado de un envío en el SII a partir del Track ID del DTE.
     */
    public function estado_envio(Request $request, $track_id)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $Firma = $Emisor->getFirma($user->id);
            if (!$Firma) {
                die('No hay firma electrónica asociada al usuario.');
            }
            $certificacion = $Emisor->enCertificacion();
            $response = apigateway(
                '/sii/dte/emitidos/estado_envio/'.$Emisor->getRUT().'/'.$track_id.'?certificacion='.$certificacion.'&formato=html',
                [
                    'auth' => [
                        'cert' => [
                            'cert-data' => $Firma->getCertificate(),
                            'pkey-data' => $Firma->getPrivateKey(),
                        ],
                    ],
                ]
            );
            $this->response->sendAndExit($response['body']);
        }
        // se crea enlace directo al SII
        catch (\Exception $e) {
            return $this->query(
                'QEstadoEnvio2',
                ['TrackId' => $track_id, 'NPagina' => 1]
            );
        }
    }

    /**
     * Acción que permite verificar los datos de un DTE en el SII a partir de los datos generales del DTE.
     */
    public function verificar_datos($receptor, $dte, $folio, $fecha, $total, $emisor = null)
    {
        list($receptor_rut, $receptor_dv) = explode('-', $receptor);
        list($emisor_rut, $emisor_dv) = $emisor ? explode('-', $emisor) : [null, null];
        return $this->query('QEstadoDTE', [
            'rutReceiver' => str_replace('.', '', $receptor_rut),
            'dvReceiver' => $receptor_dv,
            'tipoDTE' => $dte,
            'folioDTE' => $folio,
            'fechaDTE' => \sowerphp\general\Utility_Date::format($fecha, 'dmY'),
            'montoDTE' => $total,
            'rutCompany' => $emisor_rut ? str_replace('.', '', $emisor_rut) : null,
            'dvCompany' => $emisor_dv,
        ]);
    }

    /**
     * Realiza la consulta al SII.
     */
    private function query(Request $request, $query, $params)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener firma.
        $Firma = $Emisor->getFirma($user->id);
        if (!$Firma) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe firma asociada.')
                );
        }
        list($rutQuery, $dvQuery) = explode('-', $Firma->getId());
        $servidor = \sasco\LibreDTE\Sii::getServidor();
        if (empty($params['rutCompany'])) {
            $params['rutCompany'] = $Emisor->rut;
            $params['dvCompany'] = $Emisor->dv;
        }
        $url = 'https://'.$servidor.'.sii.cl/cgi_dte/UPL/'.$query.'?rutQuery='.$rutQuery.'&amp;dvQuery='.$dvQuery;
        foreach ($params as $k => $v) {
            $url .= '&amp;'.$k.'='.$v;
        }
        // Renderizar vista.
        return $this->render('Sii/query', [
            'url' => $url,
        ]);
    }

    /**
     * Muestra el estado de un DTE en el registro de compras y ventas.
     */
    public function dte_rcv(Request $request, $emisor, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar firma electrónica.
        $Firma = $Contribuyente->getFirma($user->id);
        if (!$Firma) {
            return redirect('/dte')
                ->withError(
                    __('No existe firma asociada.')
                );
        }
        // asignar variables para la vista.
        list($emisor_rut, $emisor_dv) = explode('-', str_replace('.', '', $emisor));
        $this->set([
            'Emisor' => new \website\Dte\Model_Contribuyente($emisor_rut),
            'DteTipo' => new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte),
            'folio' => $folio,
        ]);
        // Buscar eventos en el SII.
        try {
            $RCV = new \sasco\LibreDTE\Sii\RegistroCompraVenta($Firma);
            $eventos = $RCV->listarEventosHistDoc($emisor_rut, $emisor_dv, $dte, $folio);
            $cedible = $RCV->consultarDocDteCedible($emisor_rut, $emisor_dv, $dte, $folio);
            $fecha_recepcion = $RCV->consultarFechaRecepcionSii($emisor_rut, $emisor_dv, $dte, $folio);
            $this->set([
                'eventos' => $eventos !== false ? $eventos : null,
                'cedible' => $cedible !== false ? $cedible : null,
                'fecha_recepcion' => $fecha_recepcion !== false ? $fecha_recepcion : null,
            ]);
        } catch (\Exception $e) {
            return $this->render('Sii/dte_rcv_error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Acción que permite consultar el estado de un envío en el SII a partir del Track ID del AEC.
     */
    public function cesion_estado_envio(Request $request, $track_id)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $Firma = $Emisor->getFirma($user->id);
            if (!$Firma) {
                die('No hay firma electrónica asociada al usuario.');
            }
            $certificacion = $Emisor->enCertificacion();
            $response = apigateway(
                '/sii/rtc/cesiones/estado_envio/'.$track_id.'?certificacion='.$certificacion.'&formato=html',
                [
                    'auth' => [
                        'cert' => [
                            'cert-data' => $Firma->getCertificate(),
                            'pkey-data' => $Firma->getPrivateKey(),
                        ],
                    ],
                ]
            );
            $this->response->sendAndExit($response['body']);
        }
        // se crea enlace directo al SII
        catch (\Exception $e) {
            return redirect('https://'.\sasco\LibreDTE\Sii::getServidor().'.sii.cl/rtc/RTC/RTCAnotConsulta.html');
        }
    }

    /**
     * Acción que permite consultar el certificado de cesión de un DTE.
     */
    public function cesion_certificado(Request $request, $dte, $folio, $fecha)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // si existe el proveedor libredte se consulta al servicio web de LibreDTE oficial
        try {
            $Firma = $Emisor->getFirma($user->id);
            if (!$Firma) {
                die('No hay firma electrónica asociada al usuario.');
            }
            $certificacion = $Emisor->enCertificacion();
            $response = apigateway(
                '/sii/rtc/cesiones/certificado/'.$Emisor->getRUT().'/'.$dte.'/'.$folio.'/'.$fecha.'?certificacion='.$certificacion,
                [
                    'auth' => [
                        'pass' => [
                            'rut' => $Emisor->getRUT(),
                            'clave' => $Emisor->config_sii_pass,
                        ],
                    ],
                ]
            );
            $this->response->sendAndExit($response['body']);
        }
        // se crea enlace directo al SII
        catch (\Exception $e) {
            return redirect('https://'.\sasco\LibreDTE\Sii::getServidor().'.sii.cl/rtc/RTC/RTCObtCertif.html');
        }
    }

}
