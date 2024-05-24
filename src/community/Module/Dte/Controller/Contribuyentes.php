<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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

// namespace del controlador
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla contribuyente de la base de
 * datos.
 */
class Controller_Contribuyentes extends \Controller_App
{

    /**
     * Método que selecciona la empresa con la que se trabajará en el módulo DTE.
     * @param rut Si se pasa un RUT se tratará de seleccionar.
     * @param url URL a la que redirigir después de seleccionar el contribuyente.
     */
    public function seleccionar($rut = null, $url = null)
    {
        $redirect = \sowerphp\core\Model_Datasource_Session::read('referer');
        // si se está pidiendo una empresa en particular se tratará de usar
        if ($rut) {
            // se crea el emisor y se busca si está registrado (con usuario asociado)
            $Emisor = new $this->Contribuyente_class($rut);
            if (!$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message('Empresa solicitada no está registrada.', 'error');
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            // verificar que el usuario tenga acceso al emisor solicitado
            if (!$Emisor->usuarioAutorizado($this->Auth->User)) {
                \sowerphp\core\Model_Datasource_Session::message('No está autorizado a operar con la empresa solicitada.', 'error');
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            // verificar si se requiere auth2 en el usuario para poder usar la empresa
            if ($Emisor->config_usuarios_auth2) {
                $auth2_enabled = (bool)$this->Auth->User->getAuth2();
                if (!$auth2_enabled) {
                    $auth2_required = (
                        // todos obligados a usar auth2
                        $Emisor->config_usuarios_auth2 == 2
                        // admin obligados a usar auth2
                        || (
                            $Emisor->config_usuarios_auth2 == 1
                            && $Emisor->usuarioAutorizado($this->Auth->User, 'admin')
                        )

                    );
                    if ($auth2_required) {
                        \sowerphp\core\Model_Datasource_Session::message(__('Debe [habilitar el mecanismo de autenticación secundaria (2FA) en su perfil de usuario](%s) antes de poder ingresar a esta empresa.', url('/usuarios/perfil#auth:2fa')), 'error');
                        $this->redirect('/dte/contribuyentes/seleccionar');
                    }
                }
            }
            // si se guarda el emisor en la sesión
            $this->setContribuyente($Emisor);
            $Emisor->setPermisos($this->Auth->User);
            $this->Auth->saveCache();
            // determinar página de redirección y mensaje si corresponde
            if (!$url) {
                \sowerphp\core\Model_Datasource_Session::message('Desde ahora estará operando con '.$Emisor->razon_social.'.');
            }
            if ($redirect) {
                \sowerphp\core\Model_Datasource_Session::delete('referer');
            }
            else if ($url) {
                $redirect = base64_decode($url);
            }
            else {
                $trigger_referer = \sowerphp\core\Trigger::run(
                    'contribuyente_seleccionar_redirect', $Emisor, $redirect, $this->Auth
                );
                if ($trigger_referer) {
                    $redirect = $trigger_referer;
                } else {
                    $redirect = $this->Auth->check('/dte') ? '/dte' : '/';
                }
            }
            // redireccionar
            $this->redirect($redirect);
        }
        // asignar variables para la vista
        $this->set([
            'empresas' => (new Model_Contribuyentes())->getByUsuario($this->Auth->User->id),
            'registrar_empresa' => $this->Auth->check('/dte/contribuyentes/registrar'),
            'soporte' => $this->Auth->User->inGroup(['soporte']),
        ]);
    }

    /**
     * Método que permite registrar un nuevo contribuyente y asociarlo a un usuario.
     */
    public function registrar()
    {
        // verificar si el usuario puede registrar más empresas (solo si está definido el valor
        if ($this->Auth->User->config_contribuyentes_autorizados !== null) {
            $n_empresas = count((new Model_Contribuyentes())->getByUsuario($this->Auth->User->id));
            if ($n_empresas >= $this->Auth->User->config_contribuyentes_autorizados) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ha llegado al límite de empresas que puede registrar ('.num($this->Auth->User->config_contribuyentes_autorizados).'). Si requiere una cantidad mayor <a href="'.$this->request->base.'/contacto">contáctenos</a>.', 'error'
                );
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
        }
        // asignar variables para la vista
        $ImpuestosAdicionales = new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales();
        $impuestos_adicionales = $ImpuestosAdicionales->getListConTasa();
        $impuestos_adicionales_tasa = $ImpuestosAdicionales->getTasas();
        $impuestos_adicionales_todos = $ImpuestosAdicionales->getList();
        $this->set([
            '_header_extra' => ['js' => ['/dte/js/dte.js', '/dte/js/contribuyente.js']],
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
            $class = $this->Contribuyente_class;
            $Contribuyente = new $class($rut);
            if ($Contribuyente->usuario) {
                if ($Contribuyente->usuario == $this->Auth->User->id) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Ya tiene asociada la empresa a su usuario.'
                    );
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'La empresa ya está registrada a nombre del usuario '.$Contribuyente->getUsuario()->nombre.' ('.$Contribuyente->getUsuario()->email.'). Si cree que esto es un error o bien puede ser alguien suplantando la identidad de su empresa por favor <a href="'.$this->request->base.'/contacto" target="_blank">contáctenos</a>.', 'error'
                    );
                }
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            // rellenar campos de la empresa
            try {
                $this->prepararDatosContribuyente($Contribuyente);
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
                $this->redirect('/dte/contribuyentes/registrar');
            }
            $Contribuyente->set($_POST);
            $Contribuyente->rut = $rut;
            $Contribuyente->dv = $dv;
            $Contribuyente->usuario = $this->Auth->User->id;
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            // guardar contribuyente
            try {
                $Contribuyente->save(true);
                // guardar los DTE por defecto que la empresa podrá usar
                $dtes = config('dte.dtes');
                foreach ($dtes as $dte) {
                    $ContribuyenteDte = new \website\Dte\Admin\Mantenedores\Model_ContribuyenteDte(
                        $Contribuyente->rut, $dte
                    );
                    try {
                        $ContribuyenteDte->save();
                    } catch (\sowerphp\core\Exception_Model_Datasource_Database $e){}
                }
                // redireccionar
                \sowerphp\core\Model_Datasource_Session::message('Empresa '.$Contribuyente->razon_social.' registrada y asociada a su usuario.', 'ok');
                $this->redirect('/dte/contribuyentes/seleccionar');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible registrar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
        // renderizar vista
        $this->autoRender = false;
        $this->render('Contribuyentes/registrar_modificar');
    }

    /**
     * Método que permite modificar contribuyente previamente registrado.
     */
    public function modificar()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o de soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // asignar variables para editar
        $ImpuestosAdicionales = new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales();
        $impuestos_adicionales = $ImpuestosAdicionales->getListConTasa();
        $impuestos_adicionales_tasa = $ImpuestosAdicionales->getTasas();
        $impuestos_adicionales_todos = $ImpuestosAdicionales->getList();
        $this->set([
            '_header_extra' => ['js' => ['/dte/js/contribuyente.js']],
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
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
                $this->redirect('/dte/contribuyentes/modificar');
            }
            $Contribuyente->set($_POST);
            $Contribuyente->modificado = date('Y-m-d H:i:s');
            try {
                $Contribuyente->save(true);
                \sowerphp\core\Model_Datasource_Session::message('Empresa '.$Contribuyente->razon_social.' ha sido modificada.', 'ok');
                $ContribuyenteSeleccionado = $this->getContribuyente(false);
                if ($ContribuyenteSeleccionado && $ContribuyenteSeleccionado->rut == $Contribuyente->rut) {
                    $this->redirect('/dte/contribuyentes/seleccionar/'.$Contribuyente->rut);
                } else {
                    $this->redirect('/dte/contribuyentes/seleccionar');
                }
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible modificar la empresa:<br/>'.$e->getMessage(), 'error');
            }
        }
        // renderizar vista
        $this->autoRender = false;
        $this->render('Contribuyentes/registrar_modificar');
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
            $dir = DIR_PROJECT.'/data/static/contribuyentes/'.(int)$Contribuyente->rut.'/email';
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
                    $link['enlace'] = str_replace($this->request->url, '', strip_tags($_POST['config_extra_links_enlace'][$i]));
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
    public function ambiente($ambiente)
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
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
            \sowerphp\core\Model_Datasource_Session::message('Ambiente solicitado no es válido.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // asignar ambiente
        \sowerphp\core\Model_Datasource_Session::write('dte.certificacion', (bool)$ambientes[$ambiente]['codigo']);
        \sowerphp\core\Model_Datasource_Session::message('Se cambió el ambiente de la sesión a '.$ambientes[$ambiente]['glosa'].'.', 'ok');
        $this->redirect('/dte');
    }

    /**
     * Método que permite editar los usuarios autorizados de un contribuyente.
     */
    public function usuarios()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
        }
        // asignar variables para editar
        $permisos_usuarios = config('empresa.permisos');
        $this->set([
            'Contribuyente' => $Contribuyente,
            'permisos_usuarios' => $permisos_usuarios,
            'transferir_contribuyente' => (bool)config('dte.transferir_contribuyente'),
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
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No indicaron permisos para ningún usuario.', 'warning'
                    );
                    return;
                }
            }
            try {
                $Contribuyente->setUsuarios($usuarios);
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se editaron los usuarios autorizados de la empresa.', 'ok'
                );
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible editar los usuarios autorizados<br/>'.$e->getMessage(), 'error'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
            }
            $this->redirect('/dte/contribuyentes/usuarios');
        }
    }

    /**
     * Método que permite editar los documentos autorizados por usuario.
     */
    public function usuarios_dtes()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
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
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se editaron los documentos autorizados por usuario de la empresa.', 'ok'
                );
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible editar los usuarios autorizados<br/>'.$e->getMessage(), 'error'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
            }
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder directamente a la página '.$this->request->request, 'error'
            );
        }
        $this->redirect('/dte/contribuyentes/usuarios#dtes');
    }

    /**
     * Método que permite editar los documentos autorizados por usuario.
     */
    public function usuarios_sucursales()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
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
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se editaron las sucursales por defecto de los usuarios de la empresa.', 'ok'
                );
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible editar las sucursales por defecto de los usuarios<br/>'.$e->getMessage(), 'error'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
            }
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder directamente a la página '.$this->request->request, 'error'
            );
        }
        $this->redirect('/dte/contribuyentes/usuarios#sucursales');
    }

    /**
     * Método que permite modificar la configuración general de usuarios de la empresa.
     */
    public function usuarios_general()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o sea soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Usted no es el administrador de la empresa solicitada.', 'error');
            $this->redirect('/dte/contribuyentes/seleccionar');
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
                \sowerphp\core\Model_Datasource_Session::message('Configuración general de usuarios de la empresa ha sido modificada. ', 'ok');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible modificar la configuración de usuarios de la empresa:<br/>'.$e->getMessage(), 'error');
            }
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede acceder directamente a la página '.$this->request->request, 'error'
            );
        }
        $this->redirect('/dte/contribuyentes/usuarios#general');
    }

    /**
     * Método que permite transferir una empresa a un nuevo usuario administrador.
     */
    public function transferir()
    {
        $Contribuyente = $this->getContribuyente();
        // verificar si es posible transferir la empresa
        if (!(bool)config('dte.transferir_contribuyente')) {
            \sowerphp\core\Model_Datasource_Session::message('No es posible que usted transfiera la empresa, contacte a soporte para realizar esta acción.', 'error');
            $this->redirect('/dte/contribuyentes/usuarios#general');
        }
        // debe venir usuario
        if (empty($_POST['usuario'])) {
            \sowerphp\core\Model_Datasource_Session::message('Debe especificar el nuevo usuario administrador.', 'error');
            $this->redirect('/dte/contribuyentes/usuarios#general');
        }
        // verificar que el usuario sea el administrador
        if ($Contribuyente->usuario != $this->Auth->User->id) {
            \sowerphp\core\Model_Datasource_Session::message('Solo el usuario que tiene la empresa registrada puede cambiar el administrador.', 'error');
            $this->redirect('/dte/contribuyentes/usuarios#general');
        }
        // transferir al nuevo usuario administrador
        $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($_POST['usuario']);
        if (!$Usuario->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('Usuario '.$_POST['usuario'].' no existe', 'error');
            $this->redirect('/dte/contribuyentes/usuarios#general');
        }
        if ($Contribuyente->usuario == $Usuario->id) {
            \sowerphp\core\Model_Datasource_Session::message('El usuario administrador ya es '.$_POST['usuario'], 'warning');
            $this->redirect('/dte/contribuyentes/usuarios#general');
        }
        $Contribuyente->usuario = $Usuario->id;
        if ($Contribuyente->save()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Se actualizó el usuario administrador a '.$_POST['usuario'].'.', 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible cambiar el administrador de la empresa.', 'error'
            );
        }
        $this->redirect('/dte/contribuyentes/usuarios#general');
    }

    /**
     * Acción que entrega el logo del contribuyente.
     */
    public function logo($rut)
    {
        $Contribuyente = new Model_Contribuyente(substr($rut, 0, -4));
        $logo = DIR_STATIC.'/contribuyentes/'.$Contribuyente->rut.'/logo.png';
        if (!is_readable($logo)) {
            $logo = DIR_WEBSITE.'/webroot/img/logo.png';
        }
        $filename = \sowerphp\core\Utility_String::normalize($Contribuyente->getNombre()).'.png';
        $this->response->type('image/png');
        $this->response->header('Content-Length', filesize($logo));
        $this->response->header('Content-Disposition', 'inline; filename="'.$filename.'"');
        $this->response->send(file_get_contents($logo));
    }

    /**
     * Acción que permite probar la configuración de los correos electrónicos.
     */
    public function config_email_test($email, $protocol = 'smtp')
    {
        $Contribuyente = $this->getContribuyente();
        // verificar que el usuario sea el administrador o de soporte autorizado
        if (!$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            $this->response->send('Usted no es el administrador de la empresa solicitada.');
        }
        // verificar protocolo
        if (!in_array($protocol, ['smtp', 'imap'])) {
            $this->response->send('El protocolo debe ser "smtp" o "imap"');
        }
        // datos pasados por GET al servicio web
        extract($this->getQuery([
            'debug' => 3,
        ]));
        // hacer test SMTP
        if ($protocol == 'smtp') {
            try {
                $Email = $Contribuyente->getEmailSender($email, $debug);
            } catch (\Exception $e) {
                $this->response->send($e->getMessage());
            }
            if ($Contribuyente->{'config_email_'.$email.'_replyto'}) {
                $Email->replyTo($Contribuyente->{'config_email_'.$email.'_replyto'});
            }
            $Email->to($this->Auth->User->email);
            $Email->subject('[LibreDTE] Mensaje de prueba '.date('YmdHis'));
            try {
                $status = $Email->send('Esto es un mensaje de prueba desde LibreDTE');
            } catch (\Exception $e) {
                $this->response->send($e->getMessage());
            }
            if ($status === true) {
                $this->response->send('Mensaje enviado.');
            } else {
                $this->response->send($status['message']);
            }
        }
        // hacer test IMAP
        else if ($protocol == 'imap') {
            try {
                $Email = $Contribuyente->getEmailReceiver($email);
            } catch (\Exception $e) {
                $this->response->send($e->getMessage());
            }
            if (!$Email) {
                $this->response->send('No se logró la conexión al proveedor de entrada.');
            }
            $this->response->send('La casilla tiene '.num($Email->countUnreadMessages()).' mensajes sin leer de un total de '.num($Email->countMessages()).' mensajes.');
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
        extract($this->getQuery([
            'tipo' => 'contribuyente',
        ]));
        if (!in_array($tipo, ['contribuyente', 'emisor', 'receptor'])) {
            $this->Api->send('Búsqueda de tipo "'.$tipo.'" no es válida. Posibles tipos: contribuyente, emisor o receptor.', 400);
        }
        // obtener objeto del contribuyente
        // se puede obtener por RUT o por correo electrónico asociado al contribuyente
        if (strpos($rut, '@')) {
            try {
                $Contribuyente = (new Model_Contribuyentes())->getByEmail($rut, true);
            } catch (\Exception $e) {
                $this->Api->send('Error al obtener el contribuyente: '.$e->getMessage(), 500);
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
                $this->Api->send('Debe indicar emisor para hacer una búsqueda de tipo receptor.', 400);
            }
        } else {
            if ($tipo == 'emisor' && $emisor != $rut) {
                $this->Api->send('Debe indicar el mismo emisor y rut para una búsqueda de tipo emisor (o dejar el emisor en blanco).', 400);
            }
        }
        // se agregan datos vía trigger del contribuyente solo si existe un emisor
        // esto indica que se está buscando uno receptor (cliente) o emisor (proveedor)
        if ($emisor) {
            $Emisor = (new Model_Contribuyentes())->get($emisor);
            if (!$Emisor->usuarioAutorizado($User)) {
                $this->Api->send('No está autorizado a operar con el emisor seleccionado para el tipo de búsqueda '.$tipo.'.', 404);
            }
            $datos_trigger = \sowerphp\core\Trigger::run(
                'contribuyente_info', $Contribuyente, $tipo, $Emisor, $User, $datos
            );
            if (!empty($datos_trigger)) {
                $datos = $datos_trigger;
            }
        }
        // se quita el usuario de los atributos (por seguridad)
        unset($datos['usuario']);
        // se entregan los datos del contribuyente
        $this->Api->send($datos, 200);
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
        $this->Api->send($config, 200);
    }

}
