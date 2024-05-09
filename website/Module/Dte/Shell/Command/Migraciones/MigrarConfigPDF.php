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

namespace website\Dte;

/**
 * Comando para migrar la configuración de los PDF al nuevo sistema
 * @version 2020-08-02
 */
class Shell_Command_Migraciones_MigrarConfigPDF extends \Shell_App
{

    public function main($limpiar = false)
    {
        $contribuyentes = $this->getContribuyentes();
        foreach ($contribuyentes as $rut) {
            $this->migrar($rut, $limpiar);
        }
        $this->showStats();
        return 0;
    }

    private function migrar($rut, $limpiar)
    {
        $Contribuyente = new \website\Dte\Model_Contribuyente($rut);
        $this->out('Migrando a '.$Contribuyente->getRUT().' '.$Contribuyente->razon_social);
        // armar mapa de PDF
        $Contribuyente->set([
            'config_pdf_mapeo' => [
                [
                    'documento' => '*',
                    'actividad' => '*',
                    'sucursal' => '*',
                    'formato' => 'estandar',
                    'papel' => (int)$Contribuyente->config_pdf_dte_papel,
                ]
            ]
        ]);
        // armar configuración de PDF estándar
        $Contribuyente->set([
            'config_dtepdfs_estandar' => [
                'disponible' => 1,
                'carta' => [
                    'logo' => [
                        'posicion' => $Contribuyente->config_pdf_logo_posicion,
                    ],
                    'detalle' => [
                        'fuente' => $Contribuyente->config_pdf_detalle_fuente,
                        'posicion' => $Contribuyente->config_pdf_item_detalle_posicion,
                        'ancho' => [
                            'CdgItem' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->CdgItem : null,
                            'QtyItem' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->QtyItem : null,
                            'PrcItem' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->PrcItem : null,
                            'DescuentoMonto' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->DescuentoMonto : null,
                            'RecargoMonto' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->RecargoMonto : null,
                            'MontoItem' => !empty($Contribuyente->config_pdf_detalle_ancho) ? $Contribuyente->config_pdf_detalle_ancho->MontoItem : null,
                        ],
                    ],
                    'timbre' => [
                        'posicion' => $Contribuyente->config_pdf_timbre_posicion,
                    ],
                ],
                'continuo' => [
                    'logo' => [
                        'posicion' => $Contribuyente->config_pdf_logo_continuo,
                    ],
                ],
            ]
        ]);
        // limpiar configuraciones
        if ($limpiar) {
            $Contribuyente->set([
                'config_pdf_dte_papel' => null,
                'config_pdf_logo_posicion' => null,
                'config_pdf_detalle_fuente' => null,
                'config_pdf_item_detalle_posicion' => null,
                'config_pdf_detalle_ancho' => null,
            ]);
        }
        // guardar cambios
        $Contribuyente->save();
    }

    private function getContribuyentes()
    {
        $db = \sowerphp\core\Model_Datasource_Database::get();
        return $db->getCol('SELECT rut FROM contribuyente WHERE usuario IS NOT NULL ORDER BY razon_social');
    }

}
