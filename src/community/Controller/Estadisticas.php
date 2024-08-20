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
 * Controlador para mostrar estadísticas públicas del sitio.
 */
class Controller_Estadisticas extends \sowerphp\autoload\Controller
{

    /**
     * Inicialización del controlador.
     */
    public function boot(): void
    {
        app('auth')->allowActionsWithoutLogin(
            'index',
            'produccion',
            'certificacion',
            '_api_produccion_GET',
            '_api_certificacion_GET',
            '_api_version_GET',
        );
        parent::boot();
    }

    /**
     * Acción que muestra la página principal de estadísticas.
     *
     * @param boot $certificacion =true se generan estadísticas para el
     * ambiente de certificación.
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    public function index(bool $certificacion = false, int $desde = 1, int $hasta = 0)
    {
        $ambiente = $certificacion ? 'certificacion' : 'produccion';
        $url = '/api/estadisticas/' . $ambiente;
        $response = $this->consume($url);
        if ($response['status']['code'] != 200) {
            return redirect('/')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        return $this->render('Estadisticas/index', $response['body']);
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * producción.
     *
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    public function produccion(int $desde = 1, $hasta = 0)
    {
        return $this->index(false, $desde, $hasta);
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * certificación.
     *
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    public function certificacion(int $desde = 1, int $hasta = 0)
    {
        return $this->index(true, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * producción.
     *
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    public function _api_produccion_GET(int $desde = 1, int $hasta = 0)
    {
        return $this->getEstadistica(false, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * certificación.
     *
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    public function _api_certificacion_GET(int $desde = 1, int $hasta = 0)
    {
        return $this->getEstadistica(true, $desde, $hasta);
    }

    /**
     * Método que genera la estadística para las API de producción y
     * certificación.
     *
     * @param bool $certificacion =true se generan estadísticas para el
     * ambiente de certificación.
     * @param int $desde Desde cuando considerar actividad de los contribuyentes.
     * @param int $hasta Hasta cuando considerar actividad de los contribuyentes.
     */
    protected function getEstadistica(bool $certificacion, int $desde, int $hasta)
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
        // Agregar contribuyentes activos solo si se pide.
        extract($this->request->getValidatedData([
            'contribuyentes_activos' => null,
        ]));
        $enterprise = libredte()->isEnterpriseEdition();
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
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }
        // Entregar resultados.
        return response()->json([
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
        ]);
    }

    /**
     * Acción que entrega la versión de LibreDTE que se está ejecutando.
     */
    public function _api_version_GET()
    {
        return response()->json($this->getVersion());
    }

    /**
     * Método que determina la versión de LibreDTE que se está ejecutando.
     */
    protected function getVersion(): array
    {
        $enterprise = libredte()->isEnterpriseEdition();
        return [
            'linux' => (!$enterprise and PHP_OS == 'Linux')
                ? $this->getLinuxInfo()['PRETTY_NAME']
                : null
            ,
            'php' => !$enterprise ? phpversion() : null,
            'libredte' => $this->getVersionLibreDTE(),
        ];
    }

    /**
     * Determina la información sobre la versión de Linux del sistema.
     * @author https://stackoverflow.com/a/26863768
     */
    protected function getLinuxInfo(): array
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
    protected function getVersionLibreDTE(): array
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
