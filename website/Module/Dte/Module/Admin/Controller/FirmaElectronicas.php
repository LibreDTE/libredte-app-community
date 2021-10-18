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
namespace website\Dte\Admin;

/**
 * Clase para el controlador asociado a la tabla firma_electronica de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla firma_electronica
 * @author SowerPHP Code Generator
 * @version 2015-09-22 19:27:29
 */
class Controller_FirmaElectronicas extends \Controller_App
{

    /**
     * Acción que muestra el mantenedor de firmas electrónicas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'firmas' => $Emisor->getFirmas(),
        ]);
    }

    /**
     * Acción que permite al usuario agregar una nueva firma electrónica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-16
     */
    public function agregar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con la firma
            if (!isset($_FILES['firma']) or $_FILES['firma']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al subir la firma', 'error'
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
                if (!$Firma->isActive()) {
                    throw new \sowerphp\core\Exception(
                        'Firma vencida, por favor, seleccione una firma vigente. Si no posee una, debe adquirirla con un proveedor autorizado.'
                    );
                }
            } catch (\sowerphp\core\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    $e->getMessage(), 'error'
                );
                return;
            }
            // validar que vengan los datos de la firma
            if (!trim($Firma->getID())) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible obtener el RUN de la firma electrónica (verificar contraseña)', 'error'
                );
                return;
            }
            // verificar que la firma no esté cargada en otro usuario
            $FirmaElectronica = new Model_FirmaElectronica(trim($Firma->getID()));
            if ($FirmaElectronica->usuario and $FirmaElectronica->usuario != $this->Auth->User->id) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'La firma de '.$Firma->getID().' ya está cargada en el usuario '.$FirmaElectronica->getUsuario()->usuario.', no es posible asignar a '.$this->Auth->User->usuario, 'error'
                );
                return;
            }
            // si el usuario tiene una firma asociada se borra antes de agregar la nueva
            // esto es necesario porque la PK de la firma es el RUN de la misma y no el ID
            // del usuario, además un usuario puede tener sólo una firma. Entonces si un
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
            $FirmaElectronica->contrasenia = \website\Dte\Utility_Data::encrypt($_POST['contrasenia']);
            try {
                $FirmaElectronica->save();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se asoció la firma electrónica de '.$Firma->getName().' ('.$Firma->getID().') al usuario '.$this->Auth->User->usuario, 'ok'
                );
                $this->redirect('/dte/admin/firma_electronicas');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Ocurrió un error al guardar la firma.<br/>'.$e->getMessage(), 'error'
                );
                return;
            }
        }
    }

    /**
     * Acción que permite eliminar la firma electrónica de un usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function eliminar()
    {
        $FirmaElectronica = (new Model_FirmaElectronicas())->getByUser($this->Auth->User->id);
        // si el usuario no tiene firma electrónica no se elimina :-)
        if (!$FirmaElectronica) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Usted no tiene una firma electrónica registrada en el sistema, no fue necesario eliminar'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // eliminar firma
        try {
            $FirmaElectronica->delete();
            \sowerphp\core\Model_Datasource_Session::message(
                'Se eliminó la firma electrónica asociada a su usuario', 'ok'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar la firma electrónica:<br/>'.$e->getMessage(), 'error'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
    }

    /**
     * Acción que descarga la firma electrónica de un usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function descargar()
    {
        $FirmaElectronica = (new Model_FirmaElectronicas())->getByUser($this->Auth->User->id);
        // si el usuario no tiene firma electrónica no hay algo que descargar
        if (!$FirmaElectronica) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Usted no tiene una firma electrónica registrada en el sistema, no es posible descargar'
            );
            $this->redirect('/dte/admin/firma_electronicas');
        }
        // descargar la firma
        $file = $FirmaElectronica->run.'.p12';
        $firma = base64_decode($FirmaElectronica->archivo);
        $this->response->type('application/x-pkcs12');
        $this->response->header('Content-Length', strlen($firma));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($firma);
    }

}
