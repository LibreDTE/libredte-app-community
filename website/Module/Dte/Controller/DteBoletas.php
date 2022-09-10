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
namespace website\Dte;

/**
 * Clase para las acciones asociadas al libro de boletas electrónicas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-11-07
 */
class Controller_DteBoletas extends \Controller_App
{

    /**
     * Acción principal que lista los períodos con boletas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-08
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $custodia_xml = (int)\sowerphp\core\Configure::read('dte.custodia_boletas');
        $this->set([
            'Emisor' => $Emisor,
            'periodos' => $Emisor->getResumenBoletasPeriodos(),
            'custodia_boletas_limitada' => $Emisor->config_libredte_custodia_boletas_limitada,
            'custodia_xml' => $custodia_xml,
            'custodia_obligatoria' => 3,
        ]);
    }

    /**
     * Acción para descargar libro de boletas en XML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-09-10
     */
    public function xml($periodo, $FolioNotificacion = 1)
    {
        $Emisor = $this->getContribuyente();
        $boletas = $Emisor->getBoletas($periodo);
        $Libro = new \sasco\LibreDTE\Sii\LibroBoleta();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de generar el XML. [faq:174]', 'error'
            );
            $this->redirect('/dte/dte_boletas');
        }
        $Libro->setFirma($Firma);
        foreach ($boletas as $boleta) {
            $Libro->agregar([
                'TpoDoc' => $boleta['dte'],
                'FolioDoc' => $boleta['folio'],
                //'Anulado' => $boleta['anulada'] ? 'A' : false,
                'FchEmiDoc' => $boleta['fecha'],
                'RUTCliente' => $boleta['rut'],
                'MntExe' => $boleta['exento'] ? $boleta['exento'] : false,
                'MntTotal' => $boleta['total'],
            ]);
        }
        $Libro->setCaratula([
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'FchResol' => $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' =>  $Emisor->enCertificacion() ? 0 : $Emisor->config_ambiente_produccion_numero,
            'FolioNotificacion' => $FolioNotificacion,
        ]);
        $xml = $Libro->generar();
        if (!$Libro->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el libro de boletas<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_boletas');
        }
        // entregar XML
        $file = 'boletas_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($xml);
    }

    /**
     * Acción para descargar libro de boletas en CSV
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-09-10
     */
    public function csv($periodo)
    {
        $Emisor = $this->getContribuyente();
        $boletas = $Emisor->getBoletas($periodo);
        $Libro = new \sasco\LibreDTE\Sii\LibroBoleta();
        foreach ($boletas as $boleta) {
            $Libro->agregar([
                'TpoDoc' => $boleta['dte'],
                'FolioDoc' => $boleta['folio'],
                //'Anulado' => $boleta['anulada'] ? 'A' : false,
                'FchEmiDoc' => $boleta['fecha'],
                'RUTCliente' => $boleta['rut'],
                'MntExe' => $boleta['exento'] ? $boleta['exento'] : false,
                'MntTotal' => $boleta['total'],
                // oficialmente no son parte del libro estos campos, pero se entregan
                // ya que permiten tener mayor información de la boleta. igualmente
                // se podrían calcular a partir del monto exento y monto total pero se
                // dejan porque son útiles en el archivo para ser usados en el reporte
                // de consumo de folios
                'MntNeto' =>$boleta['neto'],
                'MntIVA' => $boleta['iva'],
                'TasaImp' => $boleta['tasa'],
            ]);
        }
        unset($boletas);
        $detalle = $Libro->getDetalle();
        // entregar XML
        $file = 'boletas_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo;
        if ($detalle) {
            array_unshift($detalle, array_keys($detalle[0]));
        }
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
        $this->response->sendContent($csv, $file.'.csv');
    }

    /**
     * Acción para eliminar el XML de las boletas de un período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    public function eliminar_xml()
    {
        if (isset($_POST['submit'])) {
            if (empty($_POST['periodo'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe indicar un período a eliminar', 'error'
                );
                return;
            }
            if (empty($_POST['respaldo'])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe confirmar que ya realizó el respaldo de los XML', 'error'
                );
                return;
            }
            $Emisor = $this->getContribuyente();
            $DteEmitidos = (new Model_DteEmitidos())->setContribuyente($Emisor);
            try {
                $borrados = $DteEmitidos->eliminarBoletasXML((int)$_POST['periodo']);
                \sowerphp\core\Model_Datasource_Session::message('Se eliminaron '.num($borrados).' XML de boletas', 'ok');
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            }
        }
    }

}
