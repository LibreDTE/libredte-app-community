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

namespace website;

/**
 * Controlador base de la aplicación.
 */
abstract class Controller extends \sowerphp\core\Controller
{

    /**
     * Método que fuerza la selección de un contribuyente si estamos en alguno
     * de los módulos que requieren uno para poder funcionar.
     */
    public function boot(): void
    {
        parent::boot();
        // Solo forzar que exista un contribuyente si la acción solicitada no
        // es de la API. Si es de la API, se validará en cada recurso.
        $action = $this->request->getRouteConfig()['action'];
        if ($action != 'api') {
            $controller = $this->request->getRouteConfig()['controller'];
            $module = $this->request->getRouteConfig()['module'];
            $isDteModule = (
                strpos($module, 'Dte') === 0
                && $controller != 'contribuyentes'
                && !app('auth')->isActionAllowedWithoutLogin($action)
            );
            $isOtherModule = false;
            foreach ((array)config('libredte.modulos_empresa') as $modulo) {
                if (strpos($module, $modulo) === 0) {
                    $isOtherModule = true;
                    break;
                }
            }
            if ($isDteModule || $isOtherModule) {
                try {
                    libredte()->getSessionContribuyente();
                } catch (\Exception $e) {
                    redirect('/dte/contribuyentes/seleccionar')
                        ->withError($e->getMessage())
                        ->now()
                    ;
                }
            }
        }
    }

}
