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
 * Clase para el controlador asociado a la tabla firma_electronica de la base de
 * datos.
 */
class Controller_FirmaElectronicas extends \sowerphp\autoload\Controller
{

    /**
     * Acción que muestra el mantenedor de firmas electrónicas.
     */
    public function index()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Renderizar la vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'firmas' => $Emisor->getFirmas(),
        ]);
    }

    /**
     * Acción que permite al usuario agregar una nueva firma electrónica.
     */
    public function agregar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con la firma
            if (!isset($_FILES['firma']) || $_FILES['firma']['error']) {
                \sowerphp\core\Facade_Session_Message::write(
                    'Ocurrió un error al subir la firma.', 'error'
                );
                return;
            }
            // cargar firma
            $data = file_get_contents($_FILES['firma']['tmp_name']);
            try {
                $Firma = new \sasco\LibreDTE\FirmaElectronica([
                    'data' => $data,
                    'pass' => $_POST['contrasenia'],
                ]);
                $Firma->check();
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write(
                    $e->getMessage(), 'error'
                );
                return;
            }
            // verificar que la firma no esté cargada en otro usuario
            $FirmaElectronica = new Model_FirmaElectronica(trim($Firma->getID()));
            if ($FirmaElectronica->usuario && $FirmaElectronica->usuario != $this->Auth->User->id) {
                \sowerphp\core\Facade_Session_Message::write(
                    'La firma electrónica de '.$Firma->getID().' ya está asociada al usuario '.$FirmaElectronica->getUsuario()->usuario.', no es posible asignarla a su usuario '.$this->Auth->User->usuario.'. Si 2 empresas usan la misma firma, deberán tener ambas el mismo administrador principal en LibreDTE. En el caso que no desee tener el mismo administrador principal, deberá subir la firma de un usuario diferente, y que esté autorizada en SII.', 'error'
                );
                return;
            }
            // si el usuario tiene una firma asociada se borra antes de agregar la nueva
            // esto es necesario porque la PK de la firma es el RUN de la misma y no el ID
            // del usuario, además un usuario puede tener solo una firma. Entonces si un
            // usuario ya tiene firma y trata de subir una nueva con un RUN diferente el
            // guardado de la firma falla. Para evitar este problema, se borra si existe una
            $FirmaElectronicaAntigua = (new Model_FirmaElectronicas())->getByUser($this->Auth->User->id);
            if ($FirmaElectronicaAntigua) {
                $FirmaElectronicaAntigua->delete();
            }
            // si todo fue ok se crea el objeto firma para la bd y se guarda
            $FirmaElectronica->nombre = $Firma->getName();
            $FirmaElectronica->email = $Firma->getEmail();
            $FirmaElectronica->desde = $Firma->getFrom();
            $FirmaElectronica->hasta = $Firma->getTo();
            $FirmaElectronica->emisor = $Firma->getIssuer();
            $FirmaElectronica->usuario = $this->Auth->User->id;
            $FirmaElectronica->archivo = base64_encode($data);
            $FirmaElectronica->contrasenia = encrypt($_POST['contrasenia']);
            try {
                $FirmaElectronica->save();
                \sowerphp\core\Facade_Session_Message::write(
                    'Se asoció la firma electrónica de '.$Firma->getName().' ('.$Firma->getID().') al usuario '.$this->Auth->User->usuario.'.', 'ok'
                );
                return redirect('/dte/admin/firma_electronicas');
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write(
                    'Ocurrió un error al guardar la firma.<br/>'.$e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que permite eliminar la firma electrónica de un usuario.
     */
    public function eliminar()
    {
        $FirmaElectronica = (new Model_FirmaElectronicas())->getByUser($this->Auth->User->id);
        // Si el usuario no tiene firma electrónica no se elimina,
        if (!$FirmaElectronica) {
            return redirect('/dte/admin/firma_electronicas')->withInfo(
                'Usted no tiene una firma electrónica registrada en el sistema. Solo puede eliminar su firma previamente cargada.'
            );
        }
        // Eliminar firma.
        try {
            $FirmaElectronica->delete();
            return redirect('/dte/admin/firma_electronicas')->withSuccess(
                'Se eliminó la firma electrónica asociada a su usuario.'
            );
        } catch (\Exception $e) {
            return redirect('/dte/admin/firma_electronicas')->withError(
                'No fue posible eliminar la firma electrónica:<br/>'.$e->getMessage()
            );
        }
    }

    /**
     * Acción que descarga la firma electrónica de un usuario.
     */
    public function descargar()
    {
        $FirmaElectronica = (new Model_FirmaElectronicas())->getByUser($this->Auth->User->id);
        // si el usuario no tiene firma electrónica no hay algo que descargar
        if (!$FirmaElectronica) {
            return redirect('/dte/admin/firma_electronicas')->withInfo(
                'Usted no tiene una firma electrónica registrada en el sistema, solo puede descargar su firma previamente cargada.'
            );
        }
        // descargar la firma
        $file = $FirmaElectronica->run.'.p12';
        $firma = base64_decode($FirmaElectronica->archivo);
        $this->response->type('application/x-pkcs12');
        $this->response->header('Content-Length', strlen($firma));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($firma);
    }

}
