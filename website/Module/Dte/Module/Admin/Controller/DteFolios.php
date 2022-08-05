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
 * Clase para el controlador asociado a la tabla dte_folio de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla dte_folio
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Controller_DteFolios extends \Controller_App
{

    /**
     * Acción que muestra la página principal para mantener los folios de la
     * empresa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'folios' => $Emisor->getFolios(),
        ]);
    }

    /**
     * Acción que agrega mantenedor para un nuevo tipo de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-22
     */
    public function agregar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
        ]);
        // procesar creación del mantenedor
        if (isset($_POST['submit'])) {
            // verificar que esté autorizado a cargar folios del tipo de dte
            if (!$Emisor->documentoAutorizado($_POST['dte'])) {
                \sowerphp\core\Model_Datasource_Session::message('La empresa no tiene habilitado en LibreDTE el documento de tipo '.$_POST['dte'].'. Contacte al área de soporte para que sea habilitado este tipo de documento.', 'error');
                return;
            }
            // crear mantenedor del folio
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], $Emisor->enCertificacion());
            if (!$DteFolio->exists()) {
                $DteFolio->siguiente = 0;
                $DteFolio->disponibles = 0;
                $DteFolio->alerta = $_POST['alerta'];
                try {
                    $DteFolio->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    \sowerphp\core\Model_Datasource_Session::message('No fue posible crear el mantenedor del folio: '.$e->getMessage(), 'error');
                    return;
                }
            }
            // si todo fue bien se redirecciona a la página de carga de CAF
            \sowerphp\core\Model_Datasource_Session::message('Ahora debe subir un archivo CAF para el tipo de documento '.$_POST['dte'].'. [faq:10]');
            $this->redirect('/dte/admin/dte_folios/subir_caf');
        }
    }

    /**
     * Acción que permite subir un caf para un tipo de folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-10-13
     */
    public function subir_caf()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'servidor_sii' => \sasco\LibreDTE\Sii::getServidor(),
        ]);
        // procesar solo si se envió el formulario
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir CAF
            if (!isset($_FILES['caf']) or $_FILES['caf']['error']) {
                \sowerphp\core\Model_Datasource_Session::message('Ocurrió un error al subir el CAF.', 'error');
                return;
            }
            $mimetype = \sowerphp\general\Utility_File::mimetype($_FILES['caf']['tmp_name']);
            if (!in_array($mimetype, ['application/xml', 'text/xml'])) {
                \sowerphp\core\Model_Datasource_Session::message('Formato '.$mimetype.' del archivo '.$_FILES['caf']['name'].' es incorrecto. Debe obtener un XML correcto desde SII para agregar acá. [faq:10]', 'error');
                return;
            }
            $caf = file_get_contents($_FILES['caf']['tmp_name']);
            $Folios = new \sasco\LibreDTE\Sii\Folios($caf);
            // si no hay tipo se asume que el archivo no es válido
            if (!$Folios->getTipo()) {
                \sowerphp\core\Model_Datasource_Session::message('El archivo '.$_FILES['caf']['name'].' no es un XML de un CAF válido. Debe obtener un XML correcto desde SII para agregar acá. [faq:10]', 'error');
                return;
            }
            // buscar el mantenedor de folios del CAF
            $DteFolio = new Model_DteFolio($Emisor->rut, $Folios->getTipo(), (int)$Folios->getCertificacion());
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message('Primero debe crear el mantenedor de los folios de tipo '.$Folios->getTipo().'.', 'error');
                return;
            }
            // guardar el CAF
            try {
                $DteFolio->guardarFolios($caf);
                \sowerphp\core\Model_Datasource_Session::message('El CAF para el documento de tipo '.$Folios->getTipo().' que inicia en '.$Folios->getDesde().' fue cargado. El siguiente folio disponible es '.$DteFolio->siguiente.'.', 'ok');
                $this->redirect('/dte/admin/dte_folios');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
                return;
            }
        }
    }

    /**
     * Acción que permite ver el mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-18
     */
    public function ver($dte)
    {
        $Emisor = $this->getContribuyente();
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el mantenedor de folios solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteFolio' => $DteFolio,
            'hoy' => date('Y-m-d'),
            'cafs' => $DteFolio->getCafs('DESC'),
        ]);
    }

    /**
     * Acción que permite modificar un mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-16
     */
    public function modificar($dte)
    {
        $Emisor = $this->getContribuyente();
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el mantenedor de folios solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $this->set([
            'Emisor' => $Emisor,
            'DteFolio' => $DteFolio,
        ]);
        if (isset($_POST['submit'])) {
            // validar que campos existan y asignar
            foreach (['siguiente', 'alerta'] as $attr) {
                if (empty($_POST[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message('Debe especificar el campo: '.$attr.'.', 'error');
                    return;
                }
                $DteFolio->$attr = $_POST[$attr];
            }
            // verificar CAF vigente
            $Caf = $DteFolio->getCaf();
            if (!$Caf) {
                \sowerphp\core\Model_Datasource_Session::message('No se encontró un CAF que contenga el folio '.$DteFolio->siguiente.'.', 'error');
                return;
            }
            if (!$Caf->vigente()) {
                \sowerphp\core\Model_Datasource_Session::message('El CAF que contiene el folio '.$DteFolio->siguiente.' está vencido, no se puede asignar, debe asignar uno vigente.', 'error');
                return;
            }
            // verificar que el folio siguiente que se está asignando no esté siendo usado actualmente por otro DTE ya emitido
            $DteEmitido = new \website\Dte\Model_DteEmitido($DteFolio->emisor, $DteFolio->dte, $DteFolio->siguiente, (int)$DteFolio->certificacion);
            if ($DteEmitido->exists()) {
                \sowerphp\core\Model_Datasource_Session::message('El folio '.$DteFolio->siguiente.' se encuentra usado en LibreDTE, no se puede asignar como folio siguiente. Debe asignar un folio no usado, ni en LibreDTE, ni en otro sistema.', 'error');
                return;
            }
            // guardar y redireccionar
            try {
                if (!$DteFolio->calcularDisponibles()) {
                    \sowerphp\core\Model_Datasource_Session::message('No fue posible actualizar el mantenedor de folios.', 'error');
                    return;
                }
                \sowerphp\core\Model_Datasource_Session::message('El mantenedor de folios para tipo '.$DteFolio->dte.' ha sido actualizado.', 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible actualizar el mantenedor de folios: '.$e->getMessage(), 'error');
                return;
            }
        }
    }

    /**
     * Acción que permite eliminar un mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-18
     */
    public function eliminar($dte)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo un administrador de la empresa puede eliminar un mantenedor de folios.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el mantenedor de folios solicitado', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $cafs = $DteFolio->getCafs();
        if (!empty($cafs)) {
            \sowerphp\core\Model_Datasource_Session::message('No es posible eliminar el mantenedor de folios, ya que tiene archivos CAF asociados. Debe eliminar primero cada uno de los CAF y luego eliminar el mantenedor de folios.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        $DteFolio->delete();
        \sowerphp\core\Model_Datasource_Session::message('El mantenedor de folios de '.$DteFolio->getTipo()->tipo.' ha sido eliminado.', 'ok');
        $this->redirect('/dte/admin/dte_folios');
    }

    /**
     * Acción que permite descargar el XML del archivo CAF
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function xml($dte, $desde)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo un administrador de la empresa puede descargar los archivos XML de los CAF desde LibreDTE.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        $DteCaf = new Model_DteCaf($Emisor->rut, $dte, $Emisor->enCertificacion(), $desde);
        if (!$DteCaf->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el archivo CAF solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        // entregar XML
        $file = 'caf_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$dte.'_'.$desde.'.xml';
        $xml = $DteCaf->getXML();
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($xml);
    }

    /**
     * Acción que permite eliminar un XML (CAF) específico del mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-18
     */
    public function eliminar_xml($dte, $desde)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo un administrador de la empresa puede eliminar los archivos CAF.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el mantenedor de folios solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        $DteCaf = new Model_DteCaf($Emisor->rut, $dte, $Emisor->enCertificacion(), $desde);
        if (!$DteCaf->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el archivo CAF solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        $vigente = $DteCaf->getCAF()->vigente();
        $usado = $DteCaf->usado();
        if ($vigente and $usado) {
            \sowerphp\core\Model_Datasource_Session::message('No es posible eliminar un XML de un CAF vigente y con folios usados en LibreDTE. Debe esperar a que el CAF esté vencido y ahí lo podrá eliminar.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        $DteCaf->delete();
        $DteFolio->calcularDisponibles();
        \sowerphp\core\Model_Datasource_Session::message('El XML del CAF de '.$DteCaf->getTipo()->tipo.' que inicia en '.$DteCaf->desde.' ha sido eliminado.', 'ok');
        $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
    }

    /**
     * Acción que permite reobtener un archivo CAF al SII y cargarlo en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-05
     */
    public function reobtener_caf($dte = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
            'dte' => $dte,
        ]);
        // procesar solicitud de folios
        if (isset($_POST['submit'])) {
            // buscar el mantenedor de folios del CAF
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], $Emisor->enCertificacion());
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message('Antes de reobtener un CAF para este tipo de documento, primero debe crear el mantenedor de folios de tipo '.$_POST['dte'].'.', 'error');
                return;
            }
            // recuperar firma electrónica
            $Firma = $Emisor->getFirma($this->Auth->User->id);
            if (!$Firma) {
                \sowerphp\core\Model_Datasource_Session::message('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de reobtener un CAF. [faq:174]', 'error');
                return;
            }
            // consultar listado de solicitudes
            $r = libredte_api_consume(
                '/sii/dte/caf/solicitudes/'.$Emisor->getRUT().'/'.$DteFolio->dte.'?formato=json&certificacion='.$Emisor->enCertificacion(),
                [
                    'auth' => [
                        'cert' => [
                            'cert-data' => $Firma->getCertificate(),
                            'pkey-data' => $Firma->getPrivateKey(),
                        ],
                    ],
                ]
            );
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible obtener el listado de CAFs solicitados en SII: '.$r['body'], 'error');
                return;
            }
            // no hay folios timbrados en SII
            if (empty($r['body'])) {
                \sowerphp\core\Model_Datasource_Session::message('No se encontraron folios para el tipo de documento '.$DteFolio->dte.' en SII.', 'warning');
                return;
            }
            // armar listado de solicitudes de folios que no están en LibreDTE
            $solicitudes = [];
            foreach ($r['body'] as $s) {
                $DteCaf = new Model_DteCaf($Emisor->rut, $DteFolio->dte, $Emisor->enCertificacion(), $s['inicial']);
                if (!$DteCaf->hasta) {
                    $solicitudes[] = $s;
                }
            }
            // si todo está cargado -> ok
            if (empty($solicitudes)) {
                \sowerphp\core\Model_Datasource_Session::message('Todos los folios encontrados en el SII se encuentran cargados en LibreDTE.', 'ok');
                return;
            }
            // asignar variables para la vista
            $this->set([
                'solicitudes' => $solicitudes,
                'dte' => $DteFolio->dte,
            ]);
        }
    }

    /**
     * Acción que permite descargar un archivo CAF previamente solicitado al SII
     * y cargarlo en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-05
     */
    public function reobtener_caf_cargar($dte, $folio_inicial, $folio_final, $fecha_autorizacion)
    {
        $Emisor = $this->getContribuyente();
        // buscar el mantenedor de folios del CAF
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('Primero debe crear el mantenedor de los folios de tipo '.$dte.'.', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        // si ya existe un caf no se vuelve a cargar
        $DteCaf = new Model_DteCaf($Emisor->rut, $DteFolio->dte, $Emisor->enCertificacion(), $folio_inicial);
        if ($DteCaf->hasta) {
            \sowerphp\core\Model_Datasource_Session::message('El CAF solicitado ya se encontraba cargado.', 'ok');
            $this->redirect('/dte/admin/dte_folios/reobtener_caf/'.$DteFolio->dte);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de reobtener un CAF. [faq:174]', 'error');
            $this->redirect('/dte/admin/dte_folios');
        }
        // consultar listado de solicitudes
        $r = libredte_api_consume(
            '/sii/dte/caf/xml/'.$Emisor->getRUT().'/'.$DteFolio->dte.'/'.$folio_inicial.'/'.$folio_final.'/'.$fecha_autorizacion.'?certificacion='.$Emisor->enCertificacion(),
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
            ]
        );
        if ($r['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message('No fue posible obtener el CAF desde el SII: '.$r['body'].' Se recomienda usar la opción de reobtención directa en SII. [faq:83]', 'error');
            $this->redirect('/dte/admin/dte_folios/reobtener_caf/'.$DteFolio->dte);
        }
        // guardar el CAF
        try {
            $DteFolio->guardarFolios($r['body']);
            $Folios = new \sasco\LibreDTE\Sii\Folios($r['body']);
            \sowerphp\core\Model_Datasource_Session::message('El CAF para el documento de tipo '.$Folios->getTipo().' que inicia en '.$Folios->getDesde().' fue cargado. El siguiente folio disponible es '.$DteFolio->siguiente.'.', 'ok');
            $this->redirect('/dte/admin/dte_folios/reobtener_caf/'.$DteFolio->dte);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage().' Se recomienda usar la opción de reobtención directa en SII. [faq:83]', 'error');
            $this->redirect('/dte/admin/dte_folios/reobtener_caf/'.$DteFolio->dte);
        }
    }

    /**
     * Acción que permite solicitar un archivo CAF al SII y cargarlo en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-10-14
     */
    public function solicitar_caf($dte = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'dte_tipos' => $Emisor->getDocumentosAutorizados(),
            'dte' => $dte,
        ]);
        // procesar solicitud de folios
        if (isset($_POST['submit'])) {
            // buscar el mantenedor de folios del CAF
            $DteFolio = new Model_DteFolio($Emisor->rut, $_POST['dte'], $Emisor->enCertificacion());
            if (!$DteFolio->exists()) {
                \sowerphp\core\Model_Datasource_Session::message('Primero debe crear el mantenedor de los folios de tipo '.$_POST['dte'].'. [faq:10]', 'error');
                return;
            }
            // solicitar timbraje
            if ($_POST['cantidad'] <= 0) {
                \sowerphp\core\Model_Datasource_Session::message('La cantidad de folios solicitados debe ser mayor a 0.', 'error');
                return;
            }
            try {
                $xml = $DteFolio->timbrar($_POST['cantidad']);
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage().' Se recomienda usar la opción de timbraje directo en SII. [faq:10]', 'error');
                return;
            }
            // guardar timbraje
            try {
                $Folios = $DteFolio->guardarFolios($xml);
                \sowerphp\core\Model_Datasource_Session::message(
                    'El CAF para el documento de tipo '.$Folios->getTipo().' que inicia en '.$Folios->getDesde().' fue cargado. El siguiente folio disponible es '.$DteFolio->siguiente.'.', 'ok'
                );
                $this->redirect('/dte/admin/dte_folios');
            } catch (\Exception $e) {
                throw new \Exception('No fue posible guardar el CAF obtenido desde el SII: '.$e->getMessage().' Se recomienda usar la opción de timbraje directo en SII. [faq:10]');
            }
        }
    }

    /**
     * Acción que muestra la página con el estado del folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function estado($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/admin/dte_folios/estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?formato=html');
        if ($r['status']['code']!=200) {
            die($r['body']);
        }
        $this->layout = null;
        $this->set([
            'Emisor' => $Emisor,
            'dte' => $dte,
            'folio' => $folio,
            'estado_web' => $r['body'],
        ]);
    }

    /**
     * Acción que permite anular un folio directamente en el sitio del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function anular($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/admin/dte_folios/anular/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?formato=html');
        if ($r['status']['code']!=200) {
            $this->response->send($r['body']);
        }
        $this->response->send($r['body']);
    }

    /**
     * Acción que permite descargar del SII los folios según su estado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-18
     */
    public function descargar($dte, $folio, $estado = 'recibidos')
    {
        $Emisor = $this->getContribuyente();
        $DteCaf = new Model_DteCaf($Emisor->rut, $dte, $Emisor->enCertificacion(), $folio);
        if (!$DteCaf->exists()) {
            \sowerphp\core\Model_Datasource_Session::message('No existe el CAF solicitado.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        try {
            $detalle = $DteCaf->{'getFolios'.ucfirst($estado)}();
        } catch(\sowerphp\core\Exception_Object_Method_Missing $e) {
            \sowerphp\core\Model_Datasource_Session::message('No fue posible descargar el estado de folios '.$estado.'.', 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        } catch(\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        if (!$detalle) {
            \sowerphp\core\Model_Datasource_Session::message('No se encontraron folios con el estado \''.$estado.'\' en el SII para el CAF que inicia en '.$folio.'.', 'warning');
            $this->redirect('/dte/admin/dte_folios/ver/'.$dte);
        }
        array_unshift($detalle, ['Folio inicial', 'Folio final', 'Cantidad de folios']);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
        $this->response->sendContent($csv, 'folios_'.$estado.'_'.$Emisor->rut.'_'.$dte.'_'.$folio.'_'.date('Y-m-d').'.csv');
    }

    /**
     * Acción que permite solicitar el informe de estado de los folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-19
     */
    public function informe_estados()
    {
        $Emisor = $this->getContribuyente();
        $aux = $Emisor->getDocumentosAutorizados();
        $documentos = [];
        foreach ($aux as $d) {
            if (!in_array($d['codigo'], [39, 41])) {
                $documentos[] = $d;
            }
        }
        $this->set([
            'documentos' => $documentos,
        ]);
        // procesar formulario
        if (isset($_POST['submit'])) {
            // si no hay documentos error
            if (empty($_POST['documentos'])) {
                \sowerphp\core\Model_Datasource_Session::message('Debe seleccionar al menos un tipo de documento para obtener el estado.', 'error');
                return;
            }
            if (empty($_POST['estados'])) {
                \sowerphp\core\Model_Datasource_Session::message('Debe seleccionar al menos un estado a obtener.', 'error');
                return;
            }
            // lanzar comando
            $cmd = 'Dte.Admin.DteFolios_Estados '.escapeshellcmd((int)$Emisor->rut).' '.escapeshellcmd(implode(',',$_POST['documentos'])).' '.escapeshellcmd(implode(',',$_POST['estados'])).' '.escapeshellcmd((int)$this->Auth->User->id).' -v';
            if ($this->shell($cmd)) {
                \sowerphp\core\Model_Datasource_Session::message('Error al tratar de generar su informe, por favor reintentar.', 'error');
            } else {
                \sowerphp\core\Model_Datasource_Session::message('Su informe está siendo generado, será enviado a su correo cuando esté listo.', 'ok');
            }
            $this->redirect('/dte/admin/dte_folios');
        }
    }

    /**
     * Recurso que entrega el la información de cierto mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-26
     */
    public function _api_info_GET($dte, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe.', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            $this->Api->send('No existe el mantenedor de folios para el tipo de DTE '.$dte.'.', 404);
        }
        extract($this->getQuery(['sinUso'=>false]));
        if ($sinUso) {
            $DteFolio->sin_uso = $DteFolio->getSinUso();
        }
        return $DteFolio;
    }

    /**
     * Recurso que permite modificar el mantenedor de folios
     * Modifica: folio siguiente y/o alerta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-16
     */
    public function _api_modificar_POST($dte, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe.', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            $this->Api->send('No existe el mantenedor de folios para el tipo de DTE '.$dte.'.', 404);
        }
        // validar que campos existan y asignar
        foreach (['siguiente', 'alerta'] as $attr) {
            if (isset($this->Api->data[$attr])) {
                $DteFolio->$attr = $this->Api->data[$attr];
            }
        }
        // verificar CAF vigente
        $Caf = $DteFolio->getCaf();
        if (!$Caf) {
            $this->Api->send('CAF que contenga el folio '.$DteFolio->siguiente.' no se encuentra cargado.', 500);
        }
        if (!$Caf->vigente()) {
            $this->Api->send('El CAF que contiene el folio '.$DteFolio->siguiente.' está vencido, no se puede asignar.', 500);
        }
        // verificar que el folio siguiente que se está asignando no esté siendo usado actualmente por otro DTE ya emitido
        $DteEmitido = new \website\Dte\Model_DteEmitido($DteFolio->emisor, $DteFolio->dte, $DteFolio->siguiente, (int)$DteFolio->certificacion);
        if ($DteEmitido->exists()) {
            $this->Api->send('El folio '.$DteFolio->siguiente.' se encuentra usado, no se puede asignar como folio siguiente.', 500);
        }
        // guardar e informar
        try {
            if (!$DteFolio->calcularDisponibles()) {
                $this->Api->send('No fue posible actualizar el mantenedor de folios.', 500);
            }
            return $DteFolio;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->Api->send('No fue posible actualizar el mantenedor de folios: '.$e->getMessage().'.', 500);
        }
    }

    /**
     * Recurso que permite solicitar un CAF al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function _api_solicitar_caf_GET($dte, $cantidad, $emisor)
    {
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe.', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/subir_caf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        // verificar que exista un mantenedor de folios
        $DteFolio = new Model_DteFolio($Emisor->rut, (int)$dte, $Emisor->enCertificacion());
        if (!$DteFolio->exists()) {
            $this->Api->send('Primero debe crear el mantenedor de los folios de tipo '.$dte.'.', 500);
        }
        if (!$DteFolio->siguiente) {
            $this->Api->send('Debe tener al menos un CAF cargado manualmente antes de solicitar timbraje vía LibreDTE.', 500);
        }
        // solicitar timbraje
        try {
            $xml = $DteFolio->timbrar($cantidad);
            return base64_encode($xml);
        } catch (\Exception $e) {
            $this->Api->send('No fue posible timbrar: '.$e->getMessage().'.', 500);
        }
    }

    /**
     * Recurso que permite consultar el estado de un folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-18
     */
    public function _api_estado_GET($dte, $folio, $emisor)
    {
        extract($this->getQuery(['formato'=>'json']));
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe.', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de consultar el estado de un folio. [faq:174]', 506);
        }
        // consultar estado del folio
        $r = libredte_api_consume(
            '/sii/dte/caf/estado/'.$Emisor->getRUT().'/'.$dte.'/'.$folio.'?formato='.$formato.'&certificacion='.$Emisor->enCertificacion(),
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
            ]
        );
        if ($r['status']['code']!=200) {
            $this->Api->send('No fue posible consultar el estado del folio: '.$r['body'], 500);
        }
        if ($formato=='html') {
            $this->Api->response()->type('text/html');
        } else {
            $this->Api->response()->type('application/json');
        }
        $this->Api->send($r['body']);
    }

    /**
     * Recurso que permite anular un folio en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-18
     */
    public function _api_anular_GET($dte, $folio, $emisor)
    {
        extract($this->getQuery(['formato'=>'json']));
        // crear usuario, emisor y verificar permisos
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe.', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/admin/dte_folios/subir_caf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        // recuperar firma electrónica
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de anular un folio. [faq:174]', 506);
        }
        // anular folio
        $r = libredte_api_consume(
            '/sii/dte/caf/anular/'.$Emisor->getRUT().'/'.$dte.'/'.$folio.'?formato='.$formato.'&certificacion='.$Emisor->enCertificacion(),
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
            ]
        );
        if ($r['status']['code']!=200) {
            $this->Api->send('No fue posible anular el folio: '.$r['body'], 500);
        }
        if ($formato=='html') {
            $this->Api->response()->type('text/html');
        } else {
            $this->Api->response()->type('application/json');
        }
        $this->Api->send($r['body']);
    }

}
