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

namespace website\Dte\Admin;

/**
 * Clase para el controlador asociado a la tabla item_clasificacion de la base de
 * datos.
 */
class Controller_ItemClasificaciones extends \sowerphp\autoload\Controller_Model
{

    protected $columnsView = [
        'listar' => ['codigo', 'clasificacion', 'superior', 'activa']
    ]; ///< Columnas que se deben mostrar en las vistas

    /**
     * Acción para listar las clasificaciones de items del contribuyente.
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Llamar al método padre.
        $this->forceSearch(['contribuyente' => $Contribuyente->rut]);
        return parent::listar($page, $orderby, $order);
    }

    /**
     * Acción para crear una clasificación de items.
     */
    public function crear()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Asignar variables para la vista.
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'clasificaciones' => (new Model_ItemClasificaciones())
                ->setContribuyente($Contribuyente)
                ->getList()
            ,
        ]);
        // Llamar al método padre.
        return parent::crear();
    }

    /**
     * Acción para editar una clasificación de items.
     */
    public function editar($codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Asignar variables para la vista.
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'clasificaciones' => (new Model_ItemClasificaciones())
                ->setContribuyente($Contribuyente)
                ->getList()
            ,
        ]);
        // Llamar al método padre.
        return parent::editar($Contribuyente->rut, $codigo);
    }

    /**
     * Acción para eliminar una clasificacion de items.
     */
    public function eliminar($codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar clasificación.
        $Clasificacion = new Model_ItemClasificacion($Contribuyente->rut, $codigo);
        if ($Clasificacion->enUso()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No es posible eliminar la clasificacion '.$Clasificacion->clasificacion.' ya que existen items que la usan.', 'error'
            );
            $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
            return redirect('/dte/admin/item_clasificaciones/listar' . $filterListar);
        }
        // Llamar al método padre.
        return parent::eliminar($Contribuyente->rut, $codigo);
    }

    /**
     * Acción que permite importar las casificaciones de items desde un archivo
     * CSV.
     */
    public function importar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\Facade_Session_Message::write(
                    'Ocurrió un error al subir el listado de clasificaciones de items.', 'error'
                );
                return;
            }
            // agregar cada clasificación
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
                } catch (\Exception $e) {
                    $resumen['error'][] = $Clasificacion->codigo;
                }
            }
            // mostrar errores o redireccionar
            if (!empty($resumen['error'])) {
                \sowerphp\core\Facade_Session_Message::write(
                    'No se pudieron guardar todas las clasificaciones:<br/>- nuevas: '.implode(', ', $resumen['nuevas']).
                        '<br/>- editadas: '.implode(', ', $resumen['editadas']).
                        '<br/>- con error: '.implode(', ', $resumen['error']),
                    ((empty($resumen['nuevas']) && empty($resumen['editadas'])) ? 'error' : 'warning')
                );
            } else {
                \sowerphp\core\Facade_Session_Message::write(
                    'Se importó el archivo de clasificaciones de items.', 'ok'
                );
                return redirect('/dte/admin/item_clasificaciones/listar');
            }
        }
    }

    /**
     * Acción que permite exportar todas las clasificaciones de items a un archivo CSV.
     */
    public function exportar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar clasificaciones.
        $clasificaciones = (new Model_ItemClasificaciones())
            ->setContribuyente($Contribuyente)
            ->exportar()
        ;
        if (!$clasificaciones) {
            return redirect('/dte/admin/item_clasificaciones/listar')->withWarning(
                'No hay clasificaciones de items que exportar.'
            );
        }
        array_unshift($clasificaciones, array_keys($clasificaciones[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($clasificaciones);
        $this->response->sendAndExit($csv, 'item_clasificaciones_'.$Contribuyente->rut.'.csv');
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
