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
 * Controlador de cesiones.
 */
class Controller_Cesiones extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite mostrar las cesiones de documentos emitidos por el
     * contribuyente.
     */
    public function listar($pagina = 1)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar.
        if (!is_numeric($pagina)) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar');
        }
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search']) ? ('?search='.$_GET['search']) : '';
        $paginas = 1;
        try {
            $filtros['cedido'] = true;
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = config('app.ui.pagination.registers');
                $filtros['offset'] = ($pagina - 1) * $filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::error(
                'Error al recuperar los documentos:<br/>'.$e->getMessage()
            );
            $documentos_total = 0;
            $documentos = [];
        }
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => -1, // TODO: sucursal por defecto
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite buscar en las cesiones de documentos.
     */
    public function buscar($consulta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validar el tipo de búsqueda que se desea realizar.
        if (!in_array($consulta, ['deudor', 'cedente', 'cesionario'])) {
            return redirect('/dte/cesiones/listar')
                ->withError(
                    __('Búsqueda por "%(consulta)s" no existe.', 
                        [
                            'consulta' => $consulta
                        ]
                    )
                );
        }
        // Asignar variables a la vista.
        $this->set([
            'Contribuyente' => $Contribuyente,
            'consulta' => $consulta,
            'desde' => date('Y-m-01'),
            'hasta' => date('Y-m-d'),
        ]);
        // procesar formulario
        if (isset($_POST['submit'])) {
            $consulta_codigo = [
                'deudor' => 0,
                'cedente' => 1,
                'cesionario' => 2,
            ][$consulta];
            $certificacion = $Contribuyente->enCertificacion();
            try {
                $response = apigateway(
                    '/sii/rtc/cesiones/documentos/'.$_POST['desde'].'/'.$_POST['hasta'].'/'.$consulta_codigo.'?formato=json&certificacion='.$certificacion,
                    [
                        'auth' => [
                            'pass' => [
                                'rut' => $Contribuyente->rut.'-'.$Contribuyente->dv,
                                'clave' => $Contribuyente->config_sii_pass,
                            ],
                        ],
                    ]
                );
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::error($e->getMessage());
                return;
            }
            if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($response['body']);
                return;
            }
            if (empty($response['body'])) {
                \sowerphp\core\Facade_Session_Message::info('No se encontraron documentos cedidos en el período de búsqueda.');
                return;
            }
            $this->set([
                'Contribuyente' => $Contribuyente,
                'cesiones' => $response['body'],
            ]);
        }
    }

}
