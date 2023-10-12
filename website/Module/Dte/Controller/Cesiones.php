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

// namespace del controlador
namespace website\Dte;

/**
 * Controlador de cesiones
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-07-06
 */
class Controller_Cesiones extends \Controller_App
{

    /**
     * Acción que permite mostrar las cesiones de documentos emitidos por el
     * contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-06
     */
    public function listar($pagina = 1)
    {
        if (!is_numeric($pagina)) {
            $this->redirect('/dte/'.$this->request->params['controller'].'/listar');
        }
        $Emisor = $this->getContribuyente();
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search'])?('?search='.$_GET['search']):'';
        $paginas = 1;
        try {
            $filtros['cedido'] = true;
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
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
     * Acción que permite buscar en las cesiones de documentos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-10-06
     */
    public function buscar($consulta = null)
    {
        if (!in_array($consulta, ['deudor', 'cedente', 'cesionario'])) {
            \sowerphp\core\Model_Datasource_Session::message('Búsqueda por "'.$consulta.'" no existe', 'error');
            $this->redirect('/dte/cesiones/listar');
        }
        $Contribuyente = $this->getContribuyente();
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
                $response = libredte_api_consume(
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
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
                return;
            }
            if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
                return;
            }
            if (empty($response['body'])) {
                \sowerphp\core\Model_Datasource_Session::message('No se encontraron documentos cedidos en el período de búsqueda', 'info');
                return;
            }
            $this->set([
                'Contribuyente' => $Contribuyente,
                'cesiones' => $response['body'],
            ]);
        }
    }

}
