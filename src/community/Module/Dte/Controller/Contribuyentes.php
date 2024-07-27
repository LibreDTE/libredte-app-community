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

use \sowerphp\core\Network_Request as Request;
use \sowerphp\core\Facade_Session_Message as SessionMessage;

/**
 * Clase para el controlador asociado a la tabla contribuyente de la base de
 * datos.
 */
class Controller_Contribuyentes extends \sowerphp\autoload\Controller_Model
{

    /**
     * Método que selecciona la empresa con la que se trabajará en el módulo DTE.
     * @param rut Si se pasa un RUT se tratará de seleccionar.
     * @param url URL a la que redirigir después de seleccionar el contribuyente.
     */
    public function seleccionar(Request $request, $rut = null, ?string $url = null)
    {
        $auth = app('auth');
        // Obtener usuario autenticado en la aplicación.
        $user = $request->user();
        // Si se está pidiendo una empresa en particular se tratará de usar.
        if ($rut) {
            // Obtener contribuyente para usar en la aplicación.
            try {
                $Contribuyente = libredte()->authenticate($rut, $user);
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/seleccionar')
                    ->withError(
                        __('%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
            // Verificar si se requiere auth2 en el usuario para poder usar la
            // empresa.
            if ($Contribuyente->config_usuarios_auth2) {
                $auth2_enabled = (bool)$user->getAuth2();
                if (!$auth2_enabled) {
                    $auth2_required = (
                        // Todos los usuarios obligados a usar Auth2.
                        $Contribuyente->config_usuarios_auth2 == 2
                        // Los usuarios admin obligados a usar Auth2.
                        || (
                            $Contribuyente->config_usuarios_auth2 == 1
                            && $Contribuyente->usuarioAutorizado($user, 'admin')
                        )
                    );
                    if ($auth2_required) {
                        return redirect('/dte/contribuyentes/seleccionar')
                            ->withError(
                                __('Debe [habilitar el mecanismo de autenticación secundaria (2FA) en su perfil de usuario](%(url)s) antes de poder ingresar al contribuyente %(contribuyente_nombre)s.',
                                    [
                                        'url' => url('/usuarios/perfil#auth:2fa'),
                                        'contribuyente_nombre' => $Contribuyente->getNombre()
                                    ]
                                )
                            )
                        ;
                    }
                }
            }
            // Se guarda el emisor en la sesión y se actualiza el usuario.
            libredte()->setSessionContribuyente($Contribuyente);
            $Contribuyente->setPermisos($user);
            auth()->save();
            // Determinar página de redirección y mensaje si corresponde.
            if (!$url) {
                SessionMessage::info(__(
                    'Desde ahora estará operando con %s.',
                    $Contribuyente->getNombre()
                ));
            }
            $redirect = session('referer');
            if ($redirect) {
                session()->forget('referer');
            }
            else if ($url) {
                $redirect = base64_decode($url);
            }
            else {
                $event_referer = event(
                    'contribuyente_seleccionar_redirect',
                    [$Contribuyente, $redirect],
                    true
                );
                if ($event_referer) {
                    $redirect = $event_referer;
                } else {
                    $redirect = $auth->checkResourcePermission('/dte')
                        ? '/dte'
                        : '/'
                    ;
                }
            }
            // Redireccionar.
            return redirect($redirect);
        }
        // Asignar variables para la vista.
        return $this->render(null, [
            'empresas' => (new Model_Contribuyentes())->getByUsuario($user->id),
            'registrar_empresa' => $auth->checkResourcePermission(
                '/dte/contribuyentes/registrar'
            ),
            'soporte' => $user->inGroup(['soporte']),
        ]);
    }

    /**
     * Método que permite registrar un nuevo contribuyente y asociarlo a un usuario.
     */
    public function registrar(Request $request)
    {
        $user = $request->user();
        // verificar si el usuario puede registrar más empresas (solo si está definido el valor
        if ($user->config_contribuyentes_autorizados !== null) {
            $n_empresas = count((new Model_Contribuyentes())->getByUsuario($user->id));
            if ($n_empresas >= $user->config_contribuyentes_autorizados) {
                return redirect('/dte/contribuyentes/seleccionar')
                    ->withError(
                        __('Ha llegado al límite de empresas que puede registrar (%(numero_contribuyentes)s). Si requiere una cantidad mayor <a href="%(url_contact)s">contáctenos</a>.',
                            [
                                'numero_contribuyentes' => num($user->config_contribuyentes_autorizados),
                                'url_contact' => url('/contacto')
                            ]
                        )
                    );
            }
        }
        // asignar variables para la vista
        $ImpuestosAdicionales = new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales();
        $impuestos_adicionales = $ImpuestosAdicionales->getListConTasa();
        $impuestos_adicionales_tasa = $ImpuestosAdicionales->getTasas();
        $impuestos_adicionales_todos = $ImpuestosAdicionales->getList();
        $this->set([
            '__view_header' => ['js' => ['/dte/js/dte.js', '/dte/js/contribuyente.js']],
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'impuestos_adicionales' => $impuestos_adicionales,
            'impuestos_adicionales_tasa' => $impuestos_adicionales_tasa,
            'impuestos_adicionales_todos' => $impuestos_adicionales_todos,
            'cuentas' => [],
            'titulo' => 'Registrar nueva empresa',
            'descripcion' => 'Aquí podrá registrar una nueva empresa y ser su administrador. Deberá completar los datos obligatorios de las pestañas "Empresa", "Ambientes" y "Correos". Los datos de la pestaña "Facturación" pueden quedar por defecto.',
            'form_id' => 'registrarContribuyente',
            'boton' => 'Registrar empresa',
        ]);
        // si se envió el formulario se procesa
        if (isset($_POST['submit'])) {
            // crear objeto del contribuyente con el rut y verificar que no esté ya asociada a un usuario
            list($rut, $dv) = explode('-', str_replace('.', '', $_POST['rut']));
            $Contribuyente = libredte()->contribuyente($rut);
            if ($Contribuyente && $Contribuyente->usuario) {
                if ($Contribuyente->usuario == $user->id) {
                    return redirect('/dte/contribuyentes/seleccionar')
                        ->withInfo(
                            __('Ya tiene asociada la empresa a su usuario.')
                        );
                } else {
                    return redirect('/dte/contribuyentes/seleccionar')
                        ->withError(
                            __('La empresa ya está registrada a nombre del usuario %(user_name)s (%(user_email)s). Si cree que esto es un error o bien puede ser alguien suplantando la identidad de su empresa por favor <a href="'.url('/contacto').'" target="_blank">contáctenos</a>.',
                                [
                                    'user_name' => $Contribuyente->getUsuario()->nombre,
                                    'user_email' => $Contribuyente->getUsuario()->email,
                                    'url_contact' => url('/contacto')
                                ]
                            )
                        );
                }
            }
            // rellenar campos de la empresa
            try {
                $this->prepararDatosContribuyente($Contribuyente);
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/registrar')
                    ->withError(
                        __('%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
            $Contribuyente->set($_POST);
            $Contribuyente->rut = $rut;
            $Contribuyente->dv = $dv;
            $Contribuyente->usuario = $user->id;
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            // guardar contribuyente
            try {
                $Contribuyente->save(true);
                // guardar los DTE por defecto que la empresa podrá usar
                $dtes = config('modules.Dte.contribuyentes.documentos');
                foreach ($dtes as $dte) {
                    $ContribuyenteDte = new \website\Dte\Admin\Mantenedores\Model_ContribuyenteDte(
                        $Contribuyente->rut, $dte
                    );
                    try {
                        $ContribuyenteDte->save();
                    } catch (\Exception $e) {
                        // Fallar silenciosamente.
                    }
                }
                // redireccionar
                return redirect('/dte/contribuyentes/seleccionar')
                    ->withSuccess(
                        __('Empresa %(razon_social)s registrada y asociada a su usuario.',
                            [
                                'razon_social' => $Contribuyente->razon_social
                            ]
                        )
                    );
            } catch (\Exception $e) {
                SessionMessage::error('No fue posible registrar la empresa:<br/>'.$e->getMessage());
            }
        }
        // renderizar vista
        return $this->render('Contribuyentes/registrar_modificar');
    }

    /**
     * Método que permite modificar contribuyente previamente registrado.
     */
    public function modificar(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o de soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // asignar variables para editar
        $ImpuestosAdicionales = new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales();
        $impuestos_adicionales = $ImpuestosAdicionales->getListConTasa();
        $impuestos_adicionales_tasa = $ImpuestosAdicionales->getTasas();
        $impuestos_adicionales_todos = $ImpuestosAdicionales->getList();
        $this->set([
            '__view_header' => ['js' => ['/dte/js/contribuyente.js']],
            'Contribuyente' => $Contribuyente,
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'impuestos_adicionales' => $impuestos_adicionales,
            'impuestos_adicionales_tasa' => $impuestos_adicionales_tasa,
            'impuestos_adicionales_todos' => $impuestos_adicionales_todos,
            'titulo' => 'Configuración de la empresa',
            'descripcion' => 'Aquí podrá modificar los datos de la empresa '.$Contribuyente->razon_social.' RUT '.num($Contribuyente->rut).'-'.$Contribuyente->dv.', para la cual usted es el usuario administrador.',
            'form_id' => 'modificarContribuyente',
            'boton' => 'Modificar empresa',
            'tipos_dte' => $Contribuyente->getDocumentosAutorizados(),
            'apps' => $Contribuyente->getApps('apps'),
            'dtepdfs' => $Contribuyente->getApps('dtepdfs'),
        ]);
        // editar contribuyente
        if (isset($_POST['submit'])) {
            try {
                $this->prepararDatosContribuyente($Contribuyente);
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/modificar')
                    ->withError(
                        __('%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
            $Contribuyente->set($_POST);
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            try {
                $Contribuyente->save(true);
                SessionMessage::success('Empresa '.$Contribuyente->razon_social.' ha sido modificada.');
                $ContribuyenteSeleccionado = $this->getContribuyente(false);
                if ($ContribuyenteSeleccionado && $ContribuyenteSeleccionado->rut == $Contribuyente->rut) {
                    return redirect('/dte/contribuyentes/seleccionar/'.$Contribuyente->rut);
                } else {
                    return redirect('/dte/contribuyentes/seleccionar');
                }
            } catch (\Exception $e) {
                SessionMessage::error('No fue posible modificar la empresa:<br/>'.$e->getMessage());
            }
        }
        // renderizar vista
        return $this->render('Contribuyentes/registrar_modificar');
    }

    /**
     * Método que prepara los datos de configuraciones del contribuyente para
     * ser guardados.
     */
    protected function prepararDatosContribuyente(&$Contribuyente)
    {
        // si hay cualquier campo que empiece por 'config_libredte_' se quita ya que son
        // configuraciones reservadas para los administradores de LibreDTE y no pueden
        // ser asignadas por los usuarios (esto evita que envién "a la mala" una
        // configuración del sistema)
        foreach ($_POST as $var => $val) {
            if (strpos($var, 'config_libredte_') === 0) {
                unset($_POST[$var]);
            }
        }
        // crear arreglo con actividades económicas secundarias
        if (!empty($_POST['config_extra_otras_actividades_actividad'])) {
            $n_codigos = count($_POST['config_extra_otras_actividades_actividad']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['config_extra_otras_actividades_actividad'][$i])) {
                    $_POST['config_extra_otras_actividades'][] = [
                        'actividad' => (int)$_POST['config_extra_otras_actividades_actividad'][$i],
                        'giro' => $_POST['config_extra_otras_actividades_giro'][$i],
                    ];
                }
            }
            unset($_POST['config_extra_otras_actividades_actividad']);
            unset($_POST['config_extra_otras_actividades_giro']);
        } else {
            $_POST['config_extra_otras_actividades'] = null;
        }
        // crear arreglo con sucursales
        if (!empty($_POST['config_extra_sucursales_codigo'])) {
            $_POST['config_extra_sucursales'] = [];
            $n_codigos = count($_POST['config_extra_sucursales_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (
                    !empty($_POST['config_extra_sucursales_codigo'][$i])
                    && !empty($_POST['config_extra_sucursales_sucursal'][$i])
                    && !empty($_POST['config_extra_sucursales_direccion'][$i])
                    && !empty($_POST['config_extra_sucursales_comuna'][$i])
                ) {
                    $_POST['config_extra_sucursales'][] = [
                        'codigo' => (int)$_POST['config_extra_sucursales_codigo'][$i],
                        'sucursal' => $_POST['config_extra_sucursales_sucursal'][$i],
                        'direccion' => $_POST['config_extra_sucursales_direccion'][$i],
                        'comuna' => $_POST['config_extra_sucursales_comuna'][$i],
                        'actividad_economica' => $_POST['config_extra_sucursales_actividad_economica'][$i],
                    ];
                }
            }
            unset($_POST['config_extra_sucursales_codigo']);
            unset($_POST['config_extra_sucursales_sucursal']);
            unset($_POST['config_extra_sucursales_direccion']);
            unset($_POST['config_extra_sucursales_comuna']);
            unset($_POST['config_extra_sucursales_actividad_economica']);
        } else {
            $_POST['config_extra_sucursales'] = null;
        }
        // crear arreglo de impuestos adicionales
        if (!empty($_POST['config_extra_impuestos_adicionales_codigo'])) {
            $_POST['config_extra_impuestos_adicionales'] = [];
            $n_codigos = count($_POST['config_extra_impuestos_adicionales_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (
                    !empty($_POST['config_extra_impuestos_adicionales_codigo'][$i])
                    && !empty($_POST['config_extra_impuestos_adicionales_tasa'][$i])
                ) {
                    $_POST['config_extra_impuestos_adicionales'][] = [
                        'codigo' => (int)$_POST['config_extra_impuestos_adicionales_codigo'][$i],
                        'tasa' => $_POST['config_extra_impuestos_adicionales_tasa'][$i],
                    ];
                }
            }
            unset($_POST['config_extra_impuestos_adicionales_codigo']);
            unset($_POST['config_extra_impuestos_adicionales_tasa']);
        } else {
            $_POST['config_extra_impuestos_adicionales'] = null;
        }
        // crear arreglo con observaciones
        if (!empty($_POST['config_emision_observaciones_dte'])) {
            $_POST['config_emision_observaciones'] = [];
            $n_codigos = count($_POST['config_emision_observaciones_dte']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (
                    !empty($_POST['config_emision_observaciones_dte'][$i])
                    && !empty($_POST['config_emision_observaciones_glosa'][$i])
                ) {
                    $dte = (int)$_POST['config_emision_observaciones_dte'][$i];
                    $glosa = $_POST['config_emision_observaciones_glosa'][$i];
                    $_POST['config_emision_observaciones'][$dte] = $glosa;
                }
            }
            unset($_POST['config_emision_observaciones_dte']);
            unset($_POST['config_emision_observaciones_glosa']);
        } else {
            $_POST['config_emision_observaciones'] = null;
        }
        // crear arreglo de impuestos sin crédito (no recuperables)
        if (!empty($_POST['config_extra_impuestos_sin_credito_codigo'])) {
            $_POST['config_extra_impuestos_sin_credito'] = [];
            $n_codigos = count($_POST['config_extra_impuestos_sin_credito_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['config_extra_impuestos_sin_credito_codigo'][$i])) {
                    $_POST['config_extra_impuestos_sin_credito'][] =
                        (int)$_POST['config_extra_impuestos_sin_credito_codigo'][$i]
                    ;
                }
            }
            unset($_POST['config_extra_impuestos_sin_credito_codigo']);
        } else {
            $_POST['config_extra_impuestos_sin_credito'] = null;
        }
        // crear arreglo de mapa de formatos de PDF
        if (!empty($_POST['config_pdf_mapeo_documento'])) {
            $_POST['config_pdf_mapeo'] = [];
            $n_codigos = count($_POST['config_pdf_mapeo_documento']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (
                    !empty($_POST['config_pdf_mapeo_documento'][$i])
                    && !empty($_POST['config_pdf_mapeo_actividad'][$i])
                    && !empty($_POST['config_pdf_mapeo_formato'][$i])
                ) {
                    $_POST['config_pdf_mapeo'][] = [
                        'documento' => $_POST['config_pdf_mapeo_documento'][$i],
                        'actividad' => $_POST['config_pdf_mapeo_actividad'][$i],
                        'sucursal' => !empty($_POST['config_pdf_mapeo_sucursal'][$i])
                            ? $_POST['config_pdf_mapeo_sucursal'][$i]
                            : 0
                        ,
                        'formato' => $_POST['config_pdf_mapeo_formato'][$i],
                        'papel' => !empty($_POST['config_pdf_mapeo_papel'][$i])
                            ? $_POST['config_pdf_mapeo_papel'][$i]
                            : 0
                        ,
                    ];
                }
            }
            unset($_POST['config_pdf_mapeo_documento']);
            unset($_POST['config_pdf_mapeo_actividad']);
            unset($_POST['config_pdf_mapeo_sucursal']);
            unset($_POST['config_pdf_mapeo_formato']);
            unset($_POST['config_pdf_mapeo_papel']);
        } else {
            $_POST['config_pdf_mapeo'] = null;
        }
        // subir archivo de plantilla de correo de envío de dte
        if (!empty($_FILES['template_email_dte']) && !$_FILES['template_email_dte']['error']) {
            $dir = DIR_STATIC . '/contribuyentes/' . (int)$Contribuyente->rut.'/email';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if ($_FILES['template_email_dte']['size']) {
                move_uploaded_file($_FILES['template_email_dte']['tmp_name'], $dir.'/dte.html');
            } else {
                unlink($dir.'/dte.html');
            }
        }
        // guardar datos de la API
        if (!empty($_POST['config_api_codigo'])) {
            $config_api_servicios = [];
            $n_api_servicios = count($_POST['config_api_codigo']);
            for ($i=0; $i<$n_api_servicios; $i++) {
                if (empty($_POST['config_api_url'][$i])) {
                    continue;
                }
                $config_api_servicios[$_POST['config_api_codigo'][$i]] = [
                    'url' => $_POST['config_api_url'][$i],
                ];
                if (!empty($_POST['config_api_credenciales'][$i])) {
                    $config_api_servicios[$_POST['config_api_codigo'][$i]]['auth'] = $_POST['config_api_auth'][$i];
                    $config_api_servicios[$_POST['config_api_codigo'][$i]]['credenciales'] = $_POST['config_api_credenciales'][$i];
                }
            }
            $_POST['config_api_servicios'] = $config_api_servicios ? $config_api_servicios : null;
            unset($_POST['config_api_codigo']);
            unset($_POST['config_api_url']);
            unset($_POST['config_api_auth']);
            unset($_POST['config_api_credenciales']);
        } else {
            $_POST['config_api_servicios'] = null;
        }
        // guardar enlaces personalizados
        if (!empty($_POST['config_extra_links_nombre'])) {
            $config_extra_links = [];
            $n_links = count($_POST['config_extra_links_nombre']);
            for ($i=0; $i<$n_links; $i++) {
                if (empty($_POST['config_extra_links_nombre'][$i])) {
                    continue;
                }
                $link = [
                    'nombre' => strip_tags($_POST['config_extra_links_nombre'][$i]),
                ];
                if (!empty($_POST['config_extra_links_enlace'][$i])) {
                    $link['enlace'] = str_replace(
                        url(),
                        '',
                        strip_tags($_POST['config_extra_links_enlace'][$i])
                    );
                }
                if (!empty($_POST['config_extra_links_icono'][$i])) {
                    $link['icono'] = strip_tags($_POST['config_extra_links_icono'][$i]);
                }
                $config_extra_links[] = $link;
            }
            $_POST['config_extra_links'] = $config_extra_links ? $config_extra_links : null;
            unset($_POST['config_extra_links_nombre']);
            unset($_POST['config_extra_links_enlace']);
        } else {
            $_POST['config_extra_links'] = null;
        }
        // procesar configuración de apps
        $apps = $Contribuyente->getApps('apps');
        foreach ($apps as $App) {
            $App->setConfigPOST();
        }
        // procesar configuración de apps
        $apps = $Contribuyente->getApps('dtepdfs');
        foreach ($apps as $App) {
            $App->setConfigPOST();
        }
        // poner valores por defecto
        foreach (Model_Contribuyente::$defaultConfig as $key => $value) {
            if (!isset($_POST['config_'.$key])) {
                $Contribuyente->{'config_'.$key} = $value;
            }
        }
    }

    /**
     * Método que permite cambiar el ambiente durante la sesión del usuario.
     */
    public function ambiente(Request $request, $ambiente)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // verificar ambiente solicitado
        $ambientes = [
            'produccion' => [
                'codigo' => 0,
                'glosa' => 'producción',
            ],
            'certificacion' => [
                'codigo' => 1,
                'glosa' => 'certificación',
            ],
        ];
        if (!isset($ambientes[$ambiente])) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Ambiente solicitado no es válido.')
                );
        }
        // asignar ambiente
        session(['dte.certificacion' => (bool)$ambientes[$ambiente]['codigo']]);
        return redirect('/dte')
            ->withSuccess(
                __('Se cambió el ambiente de la sesión a %(ambiente_glosa)s.',
                    [
                        'ambiente_glosa' => $ambientes[$ambiente]['glosa']
                    ]
                )
            );
    }

    /**
     * Método que permite editar los usuarios autorizados de un contribuyente.
     */
    public function usuarios(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // asignar variables para editar
        $permisos_usuarios = config('modules.Dte.contribuyentes.permisos');
        $this->set([
            'Contribuyente' => $Contribuyente,
            'permisos_usuarios' => $permisos_usuarios,
            'transferir_contribuyente' => (bool)config('modules.Dte.contribuyentes.transferir'),
        ]);
        // editar usuarios autorizados
        if (isset($_POST['submit'])) {
            $usuarios = [];
            if (isset($_POST['usuario'])) {
                $n_usuarios = count($_POST['usuario']);
                for ($i=0; $i<$n_usuarios; $i++) {
                    if (!empty($_POST['usuario'][$i])) {
                        if (!isset($usuarios[$_POST['usuario'][$i]])) {
                            $usuarios[$_POST['usuario'][$i]] = [];
                        }
                        foreach ($permisos_usuarios as $permiso => $info) {
                            if (!empty($_POST['permiso_'.$permiso][$i])) {
                                $usuarios[$_POST['usuario'][$i]][] = $permiso;
                            }
                        }
                        if (!$usuarios[$_POST['usuario'][$i]]) {
                            unset($usuarios[$_POST['usuario'][$i]]);
                        }
                    }
                }
                if (!$usuarios) {
                    SessionMessage::warning(
                        'No indicaron permisos para ningún usuario.'
                    );
                    return;
                }
            }
            try {
                $Contribuyente->setUsuarios($usuarios);
                return redirect('/dte/contribuyentes/usuarios')
                    ->withSuccess(
                        __('Se editaron los usuarios autorizados de la empresa.')
                    );
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/usuarios')
                    ->withError(
                        __('No fue posible editar los usuarios autorizados<br/>%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
        }
    }

    /**
     * Método que permite editar los documentos autorizados por usuario.
     */
    public function usuarios_dtes(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // editar documentos de usuario
        if (isset($_POST['submit'])) {
            $documentos_autorizados = $Contribuyente->getDocumentosAutorizados();
            $usuarios = [];
            if (isset($_POST['usuario'])) {
                $n_usuarios = count($_POST['usuario']);
                for ($i=0; $i<$n_usuarios; $i++) {
                    if (!empty($_POST['usuario'][$i])) {
                        if (!isset($usuarios[$_POST['usuario'][$i]])) {
                            $usuarios[$_POST['usuario'][$i]] = [];
                        }
                        foreach ($documentos_autorizados as $dte) {
                            if (!empty($_POST['dte_'.$dte['codigo']][$i])) {
                                $usuarios[$_POST['usuario'][$i]][] = $dte['codigo'];
                            }
                        }
                        if (!$usuarios[$_POST['usuario'][$i]]) {
                            unset($usuarios[$_POST['usuario'][$i]]);
                        }
                    }
                }
            }
            try {
                $Contribuyente->setDocumentosAutorizadosPorUsuario($usuarios);
                return redirect('/dte/contribuyentes/usuarios#dtes')
                    ->withSuccess(
                        __('Se editaron los documentos autorizados por usuario de la empresa.')
                    );
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/usuarios#dtes')
                    ->withError(
                        __('No fue posible editar los usuarios autorizados<br/>%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
        } else {
            return redirect('/dte/contribuyentes/usuarios#dtes')
                ->withError(
                    __('No puede acceder directamente a la página %(uri_decoded)s',
                        [
                            'uri_decoded' => $this->request->getRequestUriDecoded()
                        ]
                    )
                );
        }
    }

    /**
     * Método que permite editar los documentos autorizados por usuario.
     */
    public function usuarios_sucursales(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // editar sucursales por defecto
        if (isset($_POST['submit'])) {
            $usuarios = [];
            if (isset($_POST['usuario'])) {
                $n_usuarios = count($_POST['usuario']);
                for ($i=0; $i<$n_usuarios; $i++) {
                    if (!empty($_POST['usuario'][$i]) && !empty($_POST['sucursal'][$i])) {
                        $usuarios[$_POST['usuario'][$i]] = $_POST['sucursal'][$i];
                    }
                }
            }
            try {
                $Contribuyente->setSucursalesPorUsuario($usuarios);
                return redirect('/dte/contribuyentes/usuarios#sucursales')
                    ->withSuccess(
                        __('Se editaron las sucursales por defecto de los usuarios de la empresa.')
                    );
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/usuarios#sucursales')
                    ->withError(
                        __('No fue posible editar las sucursales por defecto de los usuarios<br/>%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
        } else {
            return redirect('/dte/contribuyentes/usuarios#sucursales')
                ->withError(
                    __('No puede acceder directamente a la página %(uri_decoded)s',
                        [
                            'uri_decoded' => $this->request->getRequestUriDecoded()
                        ]
                    )
                );
        }
    }

    /**
     * Método que permite modificar la configuración general de usuarios de la empresa.
     */
    public function usuarios_general(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/contribuyentes/seleccionar')
                ->withError(
                    __('Usted no es el administrador de la empresa solicitada.')
                );
        }
        // editar configuración de usuarios
        if (isset($_POST['submit'])) {
            // Si hay cualquier campo que empiece por 'config_libredte_' se quita ya que son
            // configuraciones reservadas para los administradores de LibreDTE y no pueden
            // ser asignadas por los usuarios (esto evita que envién "a la mala" una
            // configuración del sistema).
            foreach ($_POST as $var => $val) {
                if (strpos($var, 'config_libredte_') === 0) {
                    unset($_POST[$var]);
                }
            }
            // guardar configuración
            $Contribuyente->set($_POST);
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            try {
                $Contribuyente->save();
                return redirect('/dte/contribuyentes/usuarios#general')
                    ->withSuccess(
                        __('Configuración general de usuarios de la empresa ha sido modificada. ')
                    );
            } catch (\Exception $e) {
                return redirect('/dte/contribuyentes/usuarios#general')
                    ->withError(
                        __('No fue posible modificar la configuración de usuarios de la empresa:<br/>%(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
        } else {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('No puede acceder directamente a la página %(uri_decoded)s',
                        [
                            'uri_decoded' => $this->request->getRequestUriDecoded()
                        ]
                    )
                );
        }
    }

    /**
     * Método que permite transferir una empresa a un nuevo usuario administrador.
     */
    public function transferir(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar si es posible transferir la empresa
        if (!(bool)config('modules.Dte.contribuyentes.transferir')) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('No es posible que usted transfiera la empresa, contacte a soporte para realizar esta acción.')
                );
        }
        // debe venir usuario
        if (empty($_POST['usuario'])) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('Debe especificar el nuevo usuario administrador.')
                );
        }
        // verificar que el usuario sea el administrador
        if ($Contribuyente->usuario != $user->id) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('Solo el usuario que tiene la empresa registrada puede cambiar el administrador.')
                );
        }
        // transferir al nuevo usuario administrador
        $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($_POST['usuario']);
        if (!$Usuario->exists()) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('Usuario %(user)s no existe',
                        [
                            'user' => $_POST['usuario']
                        ]
                    )
                );
        }
        if ($Contribuyente->usuario == $Usuario->id) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('El usuario administrador ya es %(user)s',
                        [
                            'user' => $_POST['usuario']
                        ]
                    )
                );
        }
        $Contribuyente->usuario = $Usuario->id;
        if ($Contribuyente->save()) {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withSuccess(
                    __('Se actualizó el usuario administrador a %(usuario)s.',
                        [
                            'usuario' => $_POST['usuario']
                        ]
                    )
                );
        } else {
            return redirect('/dte/contribuyentes/usuarios#general')
                ->withError(
                    __('No fue posible cambiar el administrador de la empresa.')
                );
        }
    }

    /**
     * Acción que entrega el logo del contribuyente.
     */
    public function logo($rut)
    {
        $Contribuyente = new Model_Contribuyente(substr($rut, 0, -4));
        $logo = DIR_STATIC.'/contribuyentes/'.$Contribuyente->rut.'/logo.png';
        if (!is_readable($logo)) {
            $logo = app('layers')->getFilePath('/webroot/img/logo.png');
        }
        $filename = \sowerphp\core\Utility_String::normalize($Contribuyente->getNombre()).'.png';
        $this->response->type('image/png');
        $this->response->header('Content-Length', filesize($logo));
        $this->response->header('Content-Disposition', 'inline; filename="'.$filename.'"');
        $this->response->sendAndExit(file_get_contents($logo));
    }

    /**
     * Acción que permite probar la configuración de los correos electrónicos.
     */
    public function config_email_test(Request $request, $email, $protocol = 'smtp')
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que el usuario sea el administrador o de soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($user, 'admin')) {
            $this->response->sendAndExit('Usted no es el administrador de la empresa solicitada.');
        }
        // verificar protocolo
        if (!in_array($protocol, ['smtp', 'imap'])) {
            $this->response->sendAndExit('El protocolo debe ser "smtp" o "imap"');
        }
        // datos pasados por GET al servicio web
        extract($this->request->getValidatedData([
            'debug' => 3,
        ]));
        // hacer test SMTP
        if ($protocol == 'smtp') {
            try {
                $Email = $Contribuyente->getEmailSender($email, $debug);
            } catch (\Exception $e) {
                $this->response->sendAndExit($e->getMessage());
            }
            if ($Contribuyente->{'config_email_'.$email.'_replyto'}) {
                $Email->replyTo($Contribuyente->{'config_email_'.$email.'_replyto'});
            }
            $Email->to($user->email);
            $Email->subject('[LibreDTE] Mensaje de prueba '.date('YmdHis'));
            try {
                $status = $Email->send('Esto es un mensaje de prueba desde LibreDTE');
            } catch (\Exception $e) {
                $this->response->sendAndExit($e->getMessage());
            }
            if ($status === true) {
                $this->response->sendAndExit('Mensaje enviado.');
            } else {
                $this->response->sendAndExit($status['message']);
            }
        }
        // hacer test IMAP
        else if ($protocol == 'imap') {
            try {
                $Email = $Contribuyente->getEmailReceiver($email);
            } catch (\Exception $e) {
                $this->response->sendAndExit($e->getMessage());
            }
            if (!$Email) {
                $this->response->sendAndExit('No se logró la conexión al proveedor de entrada.');
            }
            $this->response->sendAndExit('La casilla tiene '.num($Email->countUnreadMessages()).' mensajes sin leer de un total de '.num($Email->countMessages()).' mensajes.');
        }
    }

    /**
     * Método de la API que permite obtener los datos de un contribuyente.
     */
    public function _api_info_GET($rut, $emisor = null)
    {
        // verificar autenticación
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // datos pasados por GET al servicio web
        extract($this->request->getValidatedData([
            'tipo' => 'contribuyente',
        ]));
        if (!in_array($tipo, ['contribuyente', 'emisor', 'receptor'])) {
            return response()->json(
                __('Búsqueda de tipo "%($tipo)s" no es válida. Posibles tipos: contribuyente, emisor o receptor.',
                    [
                        'tipo' => $tipo
                    ]
                ),
                400
            );
        }
        // obtener objeto del contribuyente
        // se puede obtener por RUT o por correo electrónico asociado al contribuyente
        if (strpos($rut, '@')) {
            try {
                $Contribuyente = (new Model_Contribuyentes())->getByEmail($rut, true);
            } catch (\Exception $e) {
                return response()->json(
                    __('Error al obtener el contribuyente: %(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    ),
                    500
                );
            }
        } else {
            $Contribuyente = (new Model_Contribuyentes())->get($rut);
        }
        // si el contribuyente no existe error
        if (!$Contribuyente || !$Contribuyente->exists()) {
            $this->Api->send('Contribuyente solicitado no existe.', 404);
        }
        // asignar ciertos valores de la configuración al objeto del contribuyente
        // se hace un "touch" para que el atributo sea cargado desde la configuración
        $Contribuyente->config_ambiente_produccion_fecha;
        $Contribuyente->config_ambiente_produccion_numero;
        $Contribuyente->config_email_intercambio_user;
        $Contribuyente->config_extra_web;
        // se crea el arreglo con datos básicos del contribuyente
        $datos = array_merge(get_object_vars($Contribuyente), [
            'comuna_glosa' => $Contribuyente->getComuna()->comuna,
        ]);
        // acciones si no hay emisor indicado (si fuesen necesarias)
        if (!$emisor) {
            // si no hay emisor y es búsqueda emisor se copia el rut como emisor
            if ($tipo == 'emisor') {
                $emisor = $rut;
            }
            // si no hay emisor con búsqueda de receptor error
            else if ($tipo == 'receptor') {
                return response()->json(
                    __('Debe indicar emisor para hacer una búsqueda de tipo receptor.'),
                    400
                );
            }
        } else {
            if ($tipo == 'emisor' && $emisor != $rut) {
                return response()->json(
                    __('Debe indicar el mismo emisor y rut para una búsqueda de tipo emisor (o dejar el emisor en blanco).'),
                    400
                );
            }
        }
        // se agregan datos vía trigger del contribuyente solo si existe un emisor
        // esto indica que se está buscando uno receptor (cliente) o emisor (proveedor)
        if ($emisor) {
            $Emisor = (new Model_Contribuyentes())->get($emisor);
            if (!$Emisor->usuarioAutorizado($User)) {
                return response()->json(
                    __('No está autorizado a operar con el emisor seleccionado para el tipo de búsqueda %(tipo)s.',
                        [
                            'tipo' => $tipo
                        ]
                    ),
                    404
                );
            }
            $datos_event = event(
                'contribuyente_info',
                [$Contribuyente, $tipo, $Emisor, $User, $datos],
                true
            );
            if (!empty($datos_event)) {
                $datos = $datos_event;
            }
        }
        // se quita el usuario de los atributos (por seguridad)
        unset($datos['usuario']);
        // se entregan los datos del contribuyente
        return response()->json(
            $datos,
            200
        );
    }

    /**
     * Método de la API que permite obtener la configuración de un contribuyente.
     */
    public function _api_config_GET($rut)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Contribuyente = new Model_Contribuyente($rut);
        if (!$Contribuyente->exists()) {
            $this->Api->send('Contribuyente solicitado no existe.', 404);
        }
        if (!$Contribuyente->usuarioAutorizado($User, 'admin')) {
            $this->Api->send('Usted no es el administrador de la empresa solicitada.', 401);
        }
        $config = [
            'ambiente_en_certificacion' => $Contribuyente->enCertificacion(),
            'documentos_autorizados' => $Contribuyente->getDocumentosAutorizados(),
        ];
        return response()->json(
            $config,
            200
        );
    }

}
