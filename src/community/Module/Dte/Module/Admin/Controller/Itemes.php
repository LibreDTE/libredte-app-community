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

use \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales;

/**
 * Clase para las acciones asociadas a items.
 */
class Controller_Itemes extends \Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar' => ['codigo', 'item', 'precio', 'moneda', 'bruto', 'clasificacion', 'activo']
    ]; ///< Columnas que se deben mostrar en las vistas

    /**
     * Acción para listar los items del contribuyente.
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        $Contribuyente = $this->getContribuyente();
        $this->forceSearch(['contribuyente' => $Contribuyente->rut]);
        parent::listar($page, $orderby, $order);
    }

    /**
     * Acción para crear un nuevo item.
     */
    public function crear()
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'Contribuyente' => $Contribuyente,
            'clasificaciones' => (new Model_ItemClasificaciones())->setContribuyente($Contribuyente)->getList(),
            'impuesto_adicionales' => (new Model_ImpuestoAdicionales())
                ->getListContribuyente($Contribuyente->config_extra_impuestos_adicionales)
            ,
        ]);
        parent::crear();
    }

    /**
     * Acción para editar un item.
     */
    public function editar($codigo, $tipo = 'INT1')
    {
        $Contribuyente = $this->getContribuyente();
        $_POST['contribuyente'] = $Contribuyente->rut;
        $this->set([
            'Contribuyente' => $Contribuyente,
            'clasificaciones' => (new Model_ItemClasificaciones())->setContribuyente($Contribuyente)->getList(),
            'impuesto_adicionales' => (new Model_ImpuestoAdicionales())
                ->getListContribuyente($Contribuyente->config_extra_impuestos_adicionales)
            ,
        ]);
        parent::editar($Contribuyente->rut, urldecode($tipo), urldecode($codigo));
    }

    /**
     * Acción para eliminar un item.
     */
    public function eliminar($codigo, $tipo = 'INT1')
    {
        $Contribuyente = $this->getContribuyente();
        parent::eliminar($Contribuyente->rut, urldecode($tipo), urldecode($codigo));
    }

    /**
     * Recurso de la API que permite obtener los datos de un item a partir de su
     * código (puede ser el código de 'libredte', el que se usa en el mantenedor de productos)
     * o bien puede ser por 'sku', 'upc' o 'ean'.
     */
    public function _api_info_GET($empresa, $codigo)
    {
        $options = $this->getQuery([
            'tipo' => null,
            'bruto' => false,
            'moneda' => 'CLP',
            'decimales' => null,
            'campo' => 'libredte',
            'fecha' => date('Y-m-d'),
            'sucursal' => 0,
            'receptor_rut' => null,
            'receptor_codigo' => null,
            'lista' => null,
            'cantidad' => 1,
        ]);
        extract($options);
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
        // consultar item en servicio web del contribuyente
        $ApiDteItemsClient = $Empresa->getApiClient('dte_items');
        if ($ApiDteItemsClient) {
            $response = $ApiDteItemsClient->get($ApiDteItemsClient->url.$codigo);
            $this->Api->send($response['body'], $response['status']['code']);
        }
        // consultar item en base de datos local de LibreDTE
        else {
            if ($campo == 'libredte') {
                $Item = (new Model_Itemes())->get($Empresa->rut, $codigo, $tipo);
            } else if (is_libredte_enterprise()) {
                $Item = (new \libredte\enterprise\Inventario\Model_InventarioItemes())
                    ->setContribuyente($Empresa)
                    ->getItemFacturacion($codigo, $tipo, $campo)
                ;
            } else {
                $Item = null;
            }
            if (!$Item || !$Item->exists() || !$Item->activo) {
                $this->Api->send('Item solicitado no existe o está inactivo.', 404);
            }
            try {
                $datos_trigger = (array)\sowerphp\core\Trigger::run('dte_item_info', $Item, $options);
            } catch (\Exception $e) {
                $this->Api->send($e->getMessage(), $e->getCode() ? $e->getCode() : 500);
            }
            if ($decimales === null) {
                $decimales = (int)$Empresa->config_items_decimales;
            }
            $this->Api->send(array_merge([
                'TpoCodigo' => $Item->codigo_tipo,
                'VlrCodigo' => $Item->codigo,
                'NmbItem' => $Item->item,
                'DscItem' => $Item->descripcion,
                'IndExe' => $Item->exento,
                'UnmdItem' => $Item->unidad,
                'PrcItem' => $Item->getPrecio($fecha, $bruto, $moneda, $decimales),
                'Moneda' => $moneda,
                'MntBruto' => (bool)$bruto,
                'ValorDR' => $Item->getDescuento($fecha, $bruto, $moneda, $decimales),
                'TpoValor' => $Item->descuento_tipo,
                'CodImpAdic' => $Item->impuesto_adicional,
                'TasaImp' => $Item->impuesto_adicional
                    ? \sasco\LibreDTE\Sii\ImpuestosAdicionales::getTasa($Item->impuesto_adicional)
                    : 0
                ,
            ], $datos_trigger), 200);
        }
    }

    /**
     * Recurso de la API que permite obtener el listado de items completo con, todos sus datos.
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
        return (new Model_Itemes())
            ->setWhereStatement(
                ['contribuyente = :contribuyente'],
                [':contribuyente' => $Empresa->rut]
            )
            ->setOrderByStatement('item')
            ->getTable()
        ;
    }

    /**
     * Acción que permite importar los items desde un archivo CSV.
     */
    public function importar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\SessionMessage::write(
                    'Ocurrió un error al subir el archivo con los items.', 'error'
                );
                return;
            }
            // procesar cada item
            $Contribuyente = $this->getContribuyente();
            try {
                $items = \sowerphp\general\Utility_Spreadsheet::read($_FILES['archivo']);
            } catch (\Exception $e) {
                \sowerphp\core\SessionMessage::write($e->getMessage(), 'error');
                return;
            }
            array_shift($items);
            $resumen = ['nuevos' => 0, 'editados' => 0, 'error' => 0];
            $cols = ['codigo_tipo', 'codigo', 'item', 'descripcion', 'clasificacion', 'unidad', 'precio', 'moneda', 'exento', 'descuento', 'descuento_tipo', 'impuesto_adicional', 'activo', 'bruto'];
            $n_cols = count($cols);
            $Clasificaciones = new Model_ItemClasificaciones();
            foreach ($items as &$item) {
                // crear objeto
                $Item = new Model_Item();
                $Item->contribuyente = $Contribuyente->rut;
                for ($i=0; $i<$n_cols; $i++) {
                    if (isset($item[$i])) {
                        $Item->{$cols[$i]} = $item[$i];
                    }
                }
                // verificar codificación del nombre y descripción del item
                if (
                    mb_detect_encoding($Item->item, 'UTF-8', true) === false
                    || mb_detect_encoding($Item->descripcion, 'UTF-8', true) === false
                ) {
                    $resumen['error']++;
                    $item[] = 'No';
                    $item[] = 'Codificación del nombre o descripción del item no es UTF-8.';
                    continue;
                }
                // verificar que exista la clasificación solicitada
                $ItemClasificacion = $Clasificaciones->get(
                    $Contribuyente->rut,
                    $Item->clasificacion
                );
                if (empty($ItemClasificacion->clasificacion)) {
                    $resumen['error']++;
                    $item[] = 'No';
                    $item[] = 'Código de clasificación '.$ItemClasificacion->codigo.' no existe.';
                    continue;
                }
                // verificar que el precio sea mayor a 0
                if (empty($Item->precio) || $Item->precio <= 0) {
                    $resumen['error']++;
                    $item[] = 'No';
                    $item[] = 'Precio del item debe ser mayor a 0.';
                    continue;
                }
                // guardar
                try {
                    $existia = $Item->exists(); // ver si existe antes de guardar
                    if ($Item->save()) {
                        $item[] = 'Si';
                        $item[] = '';
                        if ($existia) {
                            $resumen['editados']++;
                        } else {
                            $resumen['nuevos']++;
                        }
                    } else {
                        $resumen['error']++;
                        $item[] = 'No';
                        $item[] = 'Error al guardar.';
                    }
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    $resumen['error']++;
                    $item[] = 'No';
                    $item[] = $e->getMessage();
                }
            }
            // asignar mensajes de sesión
            if ($resumen['nuevos']) {
                $msg = $resumen['nuevos'] == 1
                    ? __('Se agregó un item.')
                    : __('Se agregaron %s items.', $resumen['nuevos'])
                ;
                \sowerphp\core\SessionMessage::write($msg, 'ok');
            }
            if ($resumen['editados']) {
                $msg = $resumen['editados'] == 1
                    ? __('Se editó un item.')
                    : __('Se editaron %s items.', $resumen['editados'])
                ;
                \sowerphp\core\SessionMessage::write($msg, 'ok');
            }
            if ($resumen['error']) {
                $msg = $resumen['error'] == 1
                    ? __('Se encontró un item con error (detalle en tabla de items).')
                    : __('Se encontraron %s items con error (detalle en tabla de items).', $resumen['error'])
                ;
                \sowerphp\core\SessionMessage::write($msg, 'error');
            }
            // mostrar resultado de lo realizado
            $cols[] = 'Guardado';
            $cols[] = 'Observación';
            array_unshift($items, $cols);
            $this->set([
                'resumen' => $resumen,
                'items' => $items
            ]);
        }
    }

    /**
     * Acción que permite exportar todos los items a un archivo CSV.
     */
    public function exportar()
    {
        $Contribuyente = $this->getContribuyente();
        $items = (new Model_Itemes())->setContribuyente($Contribuyente)->exportar();
        if (!$items) {
            \sowerphp\core\SessionMessage::write(
                'No hay items que exportar.', 'warning'
            );
            $this->redirect('/dte/admin/itemes/listar');
        }
        array_unshift($items, array_keys($items[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($items);
        $this->response->sendAndExit($csv, 'items_'.$Contribuyente->rut.'.csv');
    }

}
