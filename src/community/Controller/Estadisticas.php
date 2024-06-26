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

// namespace del controlador
namespace website;

/**
 * Controlador para mostrar estadísticas públicas del sitio.
 */
class Controller_Estadisticas extends \Controller_App
{

    protected $allowedActions = [
        'index',
        'produccion',
        'certificacion',
        '_api_produccion_GET',
        '_api_certificacion_GET',
        '_api_version_GET',
    ];

    /**
     * Método para permitir acciones sin estar autenticado.
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Acción que muestra la página principal de estadísticas.
     * @param certificacion =true se generan estadísticas para el ambiente de certificación.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    public function index($certificacion = false, $desde = 1, $hasta = 0)
    {
        $response = $this->consume('/api/estadisticas/'.($certificacion?'certificacion':'produccion'));
        if ($response['status']['code'] != 200) {
            \sowerphp\core\Facade_Session_Message::write($response['body'], 'error');
            $this->redirect('/');
        }
        $this->set($response['body']);
        $this->autoRender = false;
        $this->render('Estadisticas/index');
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * producción.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    public function produccion($desde = 1, $hasta = 0)
    {
        $this->index(false, $desde, $hasta);
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * certificación.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    public function certificacion($desde = 1, $hasta = 0)
    {
        $this->index(true, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * producción.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    public function _api_produccion_GET($desde = 1, $hasta = 0)
    {
        $this->getEstadistica(false, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * certificación.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    public function _api_certificacion_GET($desde = 1, $hasta = 0)
    {
        $this->getEstadistica(true, $desde, $hasta);
    }

    /**
     * Método que genera la estadística para las API de producción y
     * certificación.
     * @param certificacion =true se generan estadísticas para el ambiente de certificación.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     */
    protected function getEstadistica($certificacion, $desde, $hasta)
    {
        $Contribuyentes = new \website\Dte\Model_Contribuyentes();
        $contribuyentes_sii = $Contribuyentes->count();
        $empresas_registradas = $Contribuyentes->countRegistrados($certificacion);
        $DteEmitidos = new \website\Dte\Model_DteEmitidos();
        $DteEmitidos->setWhereStatement(
            ['certificacion = :certificacion'],
            [':certificacion' => (int)$certificacion]
        );
        $Usuarios = new \sowerphp\app\Sistema\Usuarios\Model_Usuarios();
        $Usuarios->setWhereStatement(['activo = true']);
        // agregar contribuyentes activos solo si se pide
        extract($this->request->queries([
            'contribuyentes_activos' => null,
        ]));
        $enterprise = is_libredte_enterprise();
        if (!$enterprise && $contribuyentes_activos) {
            try {
                $contribuyentes_activos = $Contribuyentes->getConMovimientos(
                    $desde, $hasta, $certificacion, false
                );
                foreach($contribuyentes_activos as &$c) {
                    if ($enterprise) {
                        $c['email'] = null;
                    }
                    unset($c['ambiente']);
                }
            } catch (\sowerphp\core\Exception_Database $e) {
                $this->Api->send($e->getMessage(), 500);
            }
        }
        // entregar resultados
        $this->Api->send([
            'version' => $this->getVersion(),
            'certificacion' => (int)$certificacion,
            'contribuyentes_sii' => (int)$contribuyentes_sii,
            'usuarios_registrados' => (int)$Usuarios->count(),
            'empresas_registradas' => (int)$empresas_registradas,
            'documentos_emitidos' => (int)$DteEmitidos->count(),
            'documentos_diarios' => $DteEmitidos->countDiarios($desde, $hasta, $certificacion),
            'usuarios_mensuales' => $Usuarios->getStatsLogin(),
            'contribuyentes_por_comuna' => $Contribuyentes->countByComuna($certificacion),
            'contribuyentes_por_actividad' => $Contribuyentes->countByActividadEconomica($certificacion),
            'contribuyentes_activos' => $contribuyentes_activos,
        ], 200);
    }

    /**
     * Acción que entrega la versión de LibreDTE que se está ejecutando.
     */
    public function _api_version_GET()
    {
        $this->Api->send($this->getVersion(), 200);
    }

    /**
     * Método que determina la versión de LibreDTE que se está ejecutando.
     */
    protected function getVersion()
    {
        $enterprise = is_libredte_enterprise();
        return [
            'linux' => (!$enterprise and PHP_OS == 'Linux') ? $this->getLinuxInfo()['PRETTY_NAME'] : null,
            'php' => !$enterprise ? phpversion() : null,
            'libredte' => $this->getVersionLibreDTE(),
        ];
    }

    /**
     * Método que determina la información sobre la versión de Linux del sistema.
     * @author https://stackoverflow.com/a/26863768
     */
    protected function getLinuxInfo()
    {
        $vars = [];
        $files = glob('/etc/*-release');
        foreach ($files as $file) {
            $lines = array_filter(array_map(function($line) {
                $parts = explode('=', $line);
                if (count($parts) !== 2) {
                    return false;
                }
                $parts[1] = str_replace(array('"', "'"), '', $parts[1]);
                return $parts;
            }, file($file)));
            foreach ($lines as $line) {
                $vars[trim($line[0])] = trim($line[1]);
            }
        }
        return $vars;
    }

    /**
     * Método que determina la versión de LibreDTE a partir del último commit
     * del proyecto.
     */
    protected function getVersionLibreDTE()
    {
        $HEAD = app('layers')->getProjectPath('/.git/logs/HEAD');
        if (!file_exists($HEAD) || !is_readable($HEAD)) {
            return null;
        }
        exec('git log --pretty="%H" -n1 HEAD', $last_commit_id, $last_commit_id_rc);
        exec('git log --pretty="%ci" -n1 HEAD', $last_commit_date, $last_commit_date_rc);
        exec('git describe --tags --abbrev=0 --exact-match', $current_tag_id, $current_tag_id_rc);
        if (!$current_tag_id_rc) {
            exec('git log -1 --format=%ai "'.str_replace('"', '', $current_tag_id[0]).'"', $current_tag_date, $current_tag_date_rc);
        } else {
            $current_tag_date_rc = $current_tag_id_rc;
            $current_tag_date = null;
        }
        return [
            'last_commit' => [
                'id' => !$last_commit_id_rc ? $last_commit_id[0] : null,
                'date' => !$last_commit_date_rc ? $last_commit_date[0] : null,
            ],
            'current_tag' => [
                'id' => !$current_tag_id_rc ? $current_tag_id[0] : null,
                'date' => !$current_tag_date_rc ? $current_tag_date[0] : null,
            ],
        ];
    }

}
