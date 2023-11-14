<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
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
namespace website\Honorarios;

/**
 * Clase para el controlador asociado a la tabla boleta_honorario de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla boleta_honorario
 * @author SowerPHP Code Generator
 * @version 2019-08-09 15:00:08
 */
class Controller_BoletaHonorarios extends \Controller_App
{

    /**
     * Acción que muestra un resumen por período donde hayan boletas recibidas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function index()
    {
        $Receptor = $this->getContribuyente();
        $periodos = (new Model_BoletaHonorarios())->setContribuyente($Receptor)->getPeriodos();
        $this->set('periodos', $periodos);
    }

    /**
     * Acción para el buscador de boletas de honorario electróncias
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-10
     */
    public function buscar()
    {
        $Receptor = $this->getContribuyente();
        if (isset($_POST['submit'])) {
            unset($_POST['submit']);
            // obtener PDF desde servicio web
            $r = $this->consume('/api/honorarios/boleta_honorarios/buscar/'.$Receptor->rut, $_POST);
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($r['body'], 'error');
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Model_Datasource_Session::message('No se encontraron boletas para la búsqueda solicitada', 'warning');
            }
            $this->set('boletas', $r['body']);
        }
    }

    /**
     * API que permite buscar boletas de honorario electrónicas recibidas en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-29
     */
    public function _api_buscar_POST($receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new \website\Dte\Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send('Receptor no existe', 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/honorarios/boleta_honorarios/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener boletas
        $filtros = [];
        foreach ((array)$this->Api->data as $key => $val) {
            if (!empty($val)) {
                $filtros[$key] = $val;
            }
        }
        if (empty($filtros)) {
            $this->Api->send('Debe definir a lo menos un filtro para la búsqueda', 400);
        }
        $boletas = (new Model_BoletaHonorarios())->setContribuyente($Receptor)->buscar($filtros, 'DESC');
        $this->Api->send($boletas, 200);
    }

    /**
     * Acción que permite descargar el PDF de una boleta de honorarios electrónica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-10
     */
    public function pdf($emisor, $numero)
    {
        $Receptor = $this->getContribuyente();
        $BoletaHonorario = new Model_BoletaHonorario($emisor, $numero);
        if (!$BoletaHonorario->exists() or $BoletaHonorario->receptor!=$Receptor->rut) {
            \sowerphp\core\Model_Datasource_Session::message('No existe la boleta solicitada', 'error');
            $this->redirect('/honorarios/boleta_honorarios');
        }
        // obtener PDF desde servicio web
        $r = $this->consume('/api/honorarios/boleta_honorarios/pdf/'.$BoletaHonorario->emisor.'/'.$BoletaHonorario->numero.'/'.$Receptor->rut);
        if ($r['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($r['body'], 'error');
            $this->redirect('/honorarios/boleta_honorarios');
        }
        $this->Api->response()->type('application/pdf');
        $this->Api->response()->header('Content-Disposition', 'attachment; filename=bhe_'.$BoletaHonorario->emisor.'_'.$BoletaHonorario->numero.'.pdf');
        $this->Api->response()->header('Pragma', 'no-cache');
        $this->Api->response()->header('Expires', 0);
        $this->Api->send($r['body']);
    }

    /**
     * API que permite descargar el PDF de una boleta de honorarios electrónica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-10
     */
    public function _api_pdf_GET($emisor, $numero, $receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new \website\Dte\Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            $this->Api->send('Receptor no existe', 404);
        }
        if (!$Receptor->usuarioAutorizado($User, '/honorarios/boleta_honorarios/pdf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener boleta
        $BoletaHonorario = new Model_BoletaHonorario($emisor, $numero);
        if (!$BoletaHonorario->exists() or $BoletaHonorario->receptor!=$Receptor->rut) {
            $this->Api->send('No existe la boleta solicitada', 404);
        }
        // obtener pdf
        try {
            $pdf = $BoletaHonorario->getPDF();
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
        // entregar boleta
        $this->Api->response()->type('application/pdf');
        $this->Api->response()->header('Content-Disposition', 'attachment; filename=bhe_'.$BoletaHonorario->emisor.'_'.$BoletaHonorario->numero.'.pdf');
        $this->Api->response()->header('Pragma', 'no-cache');
        $this->Api->response()->header('Expires', 0);
        $this->Api->send($pdf);
    }

    /**
     * Acción para ver boletas de un período en particular
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function ver($periodo)
    {
        $Receptor = $this->getContribuyente();
        $boletas = (new Model_BoletaHonorarios())->setContribuyente($Receptor)->buscar(['periodo'=>$periodo]);
        if (empty($boletas)) {
            \sowerphp\core\Model_Datasource_Session::message('No existen boletas para el período solicitado', 'error');
            $this->redirect('/honorarios/boleta_honorarios');
        }
        $this->set([
            'Receptor' => $Receptor,
            'periodo' => $periodo,
            'boletas' => $boletas,
        ]);
    }

    /**
     * Acción para descargar el CSV con las boletas de un periodo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-09
     */
    public function csv($periodo)
    {
        $Receptor = $this->getContribuyente();
        $boletas = (new Model_BoletaHonorarios())->setContribuyente($Receptor)->buscar(['periodo'=>$periodo]);
        if (empty($boletas)) {
            \sowerphp\core\Model_Datasource_Session::message('No existen boletas para el período solicitado', 'error');
            $this->redirect('/honorarios/boleta_honorarios');
        }
        foreach ($boletas as &$b) {
            unset($b['codigo']);
        }
        array_unshift($boletas, array_keys($boletas[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($boletas);
        $this->response->sendContent($csv, $Receptor->rut.'-'.$Receptor->dv.'_bhe_'.(int)$periodo.'.csv');
    }

    /**
     * Acción para actualizar el listado de boletas desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-08-13
     */
    public function actualizar()
    {
        $meses = 2;
        $Receptor = $this->getContribuyente();
        try {
            (new Model_BoletaHonorarios())->setContribuyente($Receptor)->sincronizar($meses);
            \sowerphp\core\Model_Datasource_Session::message('Boletas actualizadas', 'ok');
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
        }
        $this->redirect('/honorarios/boleta_honorarios');
    }

}
