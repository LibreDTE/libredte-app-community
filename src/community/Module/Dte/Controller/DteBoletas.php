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

namespace website\Dte;

/**
 * Clase para las acciones asociadas al libro de boletas electrónicas.
 */
class Controller_DteBoletas extends \Controller_App
{

    /**
     * Acción principal que lista los períodos con boletas.
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'periodos' => $Emisor->getResumenBoletasPeriodos(),
        ]);
    }

    /**
     * Acción para descargar libro de boletas en XML.
     */
    public function xml($periodo, $FolioNotificacion = 1)
    {
        $Emisor = $this->getContribuyente();
        $boletas = $Emisor->getBoletas($periodo);
        $Libro = new \sasco\LibreDTE\Sii\LibroBoleta();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            \sowerphp\core\Facade_Session_Message::write($message, 'error');
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
            'FchResol' => $Emisor->enCertificacion()
                ? $Emisor->config_ambiente_certificacion_fecha
                : $Emisor->config_ambiente_produccion_fecha
            ,
            'NroResol' =>  $Emisor->enCertificacion()
                ? 0
                : $Emisor->config_ambiente_produccion_numero
            ,
            'FolioNotificacion' => $FolioNotificacion,
        ]);
        $xml = $Libro->generar();
        if (!$Libro->schemaValidate()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar el libro de boletas<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_boletas');
        }
        // entregar XML
        $file = 'boletas_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción para descargar libro de boletas en CSV.
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
        $this->response->sendAndExit($csv, $file.'.csv');
    }

}
