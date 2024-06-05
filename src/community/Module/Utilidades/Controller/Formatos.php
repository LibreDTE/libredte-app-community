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
namespace website\Utilidades;

/**
 * Controlador para utilidades que permiten convertir de los formatos soportados
 * por LibreDTE a JSON.
 */
class Controller_Formatos extends \Controller_App
{

    /**
     * Acción que convierte los datos en un formato de entrada soportado y crea
     * un archivo JSON.
     */
    public function index($formato = null)
    {
        $formatos = $this->getFormatos();
        // si no es formato soportado error
        if ($formato && !in_array($formato, array_keys($formatos))) {
            \sowerphp\core\Facade_Session_Message::write('Formato '.$formato.' no está soportado.', 'error');
            $this->redirect('/utilidades/formatos');
        }
        // variables para la vista
        $this->set([
            'formatos' => $formatos,
            'formato' => $formato,
        ]);
        // procesar archivo de entrada y descargar
        if (isset($_POST['submit']) && !$_FILES['archivo']['error']) {
            // convertir datos de entrada a JSON
            try {
                $json = \sasco\LibreDTE\Sii\Dte\Formatos::toJSON(
                    $_POST['formato'], file_get_contents($_FILES['archivo']['tmp_name'])
                );
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
                $this->redirect($this->request->getRequestUriDecoded());
            }
            // descargar JSON
            $this->response->type('application/json', 'UTF-8');
            $this->response->header('Content-Length', strlen($json));
            $this->response->header('Content-Disposition', 'attachement; filename="'.$_FILES['archivo']['name'].'.json"');
            $this->response->sendAndExit($json);
        }
    }

    /**
     * Método que entrega los formatos soportados oficialmente (hay parser).
     */
    private function getFormatos()
    {
        $aux = \sasco\LibreDTE\Sii\Dte\Formatos::getFormatos();
        $formatos = [];
        foreach ($aux as $f) {
            $formatos[$f] = str_replace('.', ': ', $f);
        }
        return $formatos;
    }

}
