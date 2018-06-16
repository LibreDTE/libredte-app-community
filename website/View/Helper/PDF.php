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

namespace website;

/**
 * Helper para la generación de PDFs personalizados para LibreDTE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-02-02
 */
class View_Helper_PDF extends \sowerphp\general\View_Helper_PDF
{

    public $Contribuyente; ///< Empresa que está generando el PDF
    public $titulo; ///< Título del PDF
    public $subtitulo; ///< Subtítulo del PDF

    /**
     * Método que sobreescribe la cabecera del PDF para tener una personalizada
     * para los informes del contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-06-15
     */
    public function Header()
    {
        // nombre de la empresa
        $this->SetFont('helvetica', 'B', 9);
        $this->SetY(5);
        $this->Texto($this->Contribuyente->razon_social.' / RUT N° '.$this->Contribuyente->getRUT());
        $this->Ln();
        $this->SetFont('helvetica', '', 9);
        $this->Texto($this->Contribuyente->giro);
        $this->Ln();
        $this->Texto($this->Contribuyente->direccion.', '.$this->Contribuyente->getComuna()->comuna);
        $this->Ln();
        $this->Ln();
        // titulo del archivo
        $this->SetFont('helvetica', 'B', 14);
        $this->Texto($this->titulo, null, null, 'C');
        if (isset($this->subtitulo)) {
            $this->Ln();
            $this->SetFont('helvetica', 'B', 10);
            $this->Texto($this->subtitulo, null, null, 'C');
        }
    }

    /**
     * Método que sobreescribe el pie de página del PDF
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2017-03-18
     */
    public function Footer()
    {
        $this->SetFont('helvetica', '', 8);
        \TCPDF::Footer();
        $this->SetY($this->GetY());
        $this->SetFont('helvetica', 'B', 6);
        $link = 'http'.(isset($_SERVER['HTTPS'])?'s':null).'://'.$_SERVER['HTTP_HOST'];
        $this->Texto('Documento generado el '. date('d/m/Y').' a las '.date('H:i').' usando LibreDTE ('.$link.')');
    }

}
