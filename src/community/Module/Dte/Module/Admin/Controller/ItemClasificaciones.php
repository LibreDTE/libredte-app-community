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
namespace website\Dte\Admin;

/**
 * Clase para el controlador asociado a la tabla item_clasificacion de la base de
 * datos.
 */
class Controller_ItemClasificaciones extends \Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar' => ['codigo', 'clasificacion', 'superior', 'activa']
    ]; ///< Columnas que se deben mostrar en las vistas

    /**
     * Acción para listar las clasificaciones de items del contribuyente.
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        $Contribuyente = $this->getContribuyente();
        $this->forceSearch(['contribuyente' => $Contribuyente->rut]);
        parent::listar($page, $orderby, $order);
    }

    /**
     * Acción para crear una clasificación de items.
     */
    public function crear()
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'clasificaciones' => (new Model_ItemClasificaciones())
                ->setContribuyente($Contribuyente)
                ->getList()
            ,
        ]);
        parent::crear();
    }

    /**
     * Acción para editar una clasificación de items.
     */
    public function editar($codigo)
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'clasificaciones' => (new Model_ItemClasificaciones())
                ->setContribuyente($Contribuyente)
                ->getList()
            ,
        ]);
        parent::editar($Contribuyente->rut, $codigo);
    }

    /**
     * Acción para eliminar una clasificacion de items.
     */
    public function eliminar($codigo)
    {
        $Contribuyente = $this->getContribuyente();
        $Clasificacion = new Model_ItemClasificacion($Contribuyente->rut, $codigo);
        if ($Clasificacion->enUso()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No es posible eliminar la clasificacion '.$Clasificacion->clasificacion.' ya que existen items que la usan.', 'error'
            );
            $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
            $this->redirect(
                $this->module_url.$this->request->params['controller'].'/listar'.$filterListar
            );
        }
        parent::eliminar($Contribuyente->rut, $codigo);
    }

    /**
     * Acción que permite importar las casificaciones de items desde un archivo
     * CSV.
     */
    public function importar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir el listado de clasificaciones de items.', 'error'
                );
                return;
            }
            // agregar cada clasificación
            $Contribuyente = $this->getContribuyente();
            $clasificaciones = \sowerphp\general\Utility_Spreadsheet::read($_FILES['archivo']);
            array_shift($clasificaciones);
            $resumen = ['nuevas' => [], 'editadas' => [], 'error' => []];
            $cols = ['codigo', 'clasificacion', 'superior', 'activa'];
            $n_cols = count($cols);
            foreach ($clasificaciones as $c) {
                // crear objeto
                $Clasificacion = new Model_ItemClasificacion();
                $Clasificacion->contribuyente = $Contribuyente->rut;
                for ($i=0; $i<$n_cols; $i++) {
                    $Clasificacion->{$cols[$i]} = $c[$i];
                }
                // guardar
                try {
                    $existia = $Clasificacion->exists();
                    if ($Clasificacion->save()) {
                        if ($existia) {
                            $resumen['editadas'][] = $Clasificacion->codigo;
                        } else {
                            $resumen['nuevas'][] = $Clasificacion->codigo;
                        }
                    } else {
                        $resumen['error'][] = $Clasificacion->codigo;
                    }
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    $resumen['error'][] = $Clasificacion->codigo;
                }
            }
            // mostrar errores o redireccionar
            if (!empty($resumen['error'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No se pudieron guardar todas las clasificaciones:<br/>- nuevas: '.implode(', ', $resumen['nuevas']).
                        '<br/>- editadas: '.implode(', ', $resumen['editadas']).
                        '<br/>- con error: '.implode(', ', $resumen['error']),
                    ((empty($resumen['nuevas']) && empty($resumen['editadas'])) ? 'error' : 'warning')
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se importó el archivo de clasificaciones de items.', 'ok'
                );
                $this->redirect('/dte/admin/item_clasificaciones/listar');
            }
        }
    }

    /**
     * Acción que permite exportar todas las clasificaciones de items a un archivo CSV.
     */
    public function exportar()
    {
        $Contribuyente = $this->getContribuyente();
        $clasificaciones = (new Model_ItemClasificaciones())
            ->setContribuyente($Contribuyente)
            ->exportar()
        ;
        if (!$clasificaciones) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay clasificaciones de items que exportar.', 'warning'
            );
            $this->redirect('/dte/admin/item_clasificaciones/listar');
        }
        array_unshift($clasificaciones, array_keys($clasificaciones[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($clasificaciones);
        $this->response->sendContent($csv, 'item_clasificaciones_'.$Contribuyente->rut.'.csv');
    }

    /**
     * Recurso de la API que permite obtener el listado de clasificaciones de items completo, con todos sus datos.
     */
    public function _api_raw_GET($empresa)
    {
        // obtener usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear contribuyente y verificar que exista y el usuario esté autorizado
        $Empresa = new \website\Dte\Model_Contribuyente($empresa);
        if (!$Empresa->exists()) {
            $this->Api->send('Empresa solicitada no existe.', 404);
        }
        if (!$Empresa->usuarioAutorizado($User, '/dte/documentos/emitir')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        // entregar datos
        return (new Model_ItemClasificaciones())
            ->setWhereStatement(
                ['contribuyente = :contribuyente'],
                [':contribuyente' => $Empresa->rut]
            )
            ->setOrderByStatement('clasificacion')
            ->getTable()
        ;
    }

}
