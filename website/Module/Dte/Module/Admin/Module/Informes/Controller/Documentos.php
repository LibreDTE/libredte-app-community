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
namespace website\Dte\Admin\Informes;

/**
 * Clase para informe asociado a la emisión o recepción de documentos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-02-06
 */
class Controller_Documentos extends \Controller_App
{

    /**
     * Acción que muestra los contribuyentes con sus documentos emitidos y
     * recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-03
     */
    public function index()
    {
        $this->set([
            'tipos_documentos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(true),
        ]);
        if (isset($_POST['submit'])) {
            $certificacion = $_POST['ambiente']=='ambos' ? null : (bool)$_POST['ambiente'];
            $contribuyentes = (new \website\Dte\Model_Contribuyentes())->getConMovimientos(
                $_POST['desde'], $_POST['hasta'], $certificacion, $_POST['dte'], $_POST['rut']
            );
            if (!empty($contribuyentes)) {
                $this->set([
                    'contribuyentes' => $contribuyentes,
                ]);
            } else {
                \sowerphp\core\Model_Datasource_Session::message('Búsqueda sin resultados', 'info');
            }
        }
    }

    /**
     * Acción que busca los documentos rechazados en un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-07
     */
    public function rechazados()
    {
        if (isset($_POST['submit'])) {
            $this->set([
                'documentos' => (new \website\Dte\Model_DteEmitidos())->getRechazados($_POST['desde'], $_POST['hasta'], $_POST['ambiente']),
            ]);
        }
    }

}
