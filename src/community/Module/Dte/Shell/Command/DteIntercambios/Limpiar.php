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
 * Comando que limpia las bandejas de intercambio de los contribuyentes
 * Básicamente elimina:
 *  - Documentos recibidos de certificación mayores a 1 mes
 *  - Guías recibidas que fueron emitidas por el mismo contribuyente que las recibe
 *  - Intercambios de certificación mayores a 1 mes
 *  - Intercambios donde el emisor es igual al receptor
 *  - Intercambios no procesados recibidos hace más de 4 meses
 *  - Intercambios marcados como rechazados/reclamados mayores a 2 meses
 * Solo se eliminan intercambios que NO estén asociados a un DTE recibido,
 * esto se asegura con una restricción en la base de datos, en la tabla dte_recibido
 * debe estar la regla de llave foránea con RESTRICT para los DELETE. Así:
 *  ALTER TABLE dte_recibido ADD CONSTRAINT
 *    "dte_recibido_intercambio_fk"
 *    FOREIGN KEY (receptor, intercambio, certificacion)
 *    REFERENCES dte_intercambio(receptor, codigo, certificacion)
 *    ON UPDATE CASCADE ON DELETE RESTRICT;
 */
class Shell_Command_DteIntercambios_Limpiar extends \sowerphp\autoload\Shell
{

    private $reglas = [
        'Recibidos certificación >1 mes' => 'DELETE FROM dte_recibido WHERE certificacion = true AND fecha < (NOW() - INTERVAL \'1 MONTH\')',
        'Guías recibidas con receptor igual al emisor' => 'DELETE FROM dte_recibido WHERE emisor = receptor and dte = 52',
        'Intercambios certificación >1 mes' => 'DELETE FROM dte_intercambio WHERE certificacion = true AND fecha_hora_email < (NOW() - INTERVAL \'1 MONTH\')',
        'Receptor igual al emisor en intercambio' => 'DELETE FROM dte_intercambio WHERE emisor = receptor',
        'Intercambios sin estado >4 meses' => 'DELETE FROM dte_intercambio WHERE estado IS NULL AND fecha_hora_email < (NOW() - INTERVAL \'4 MONTH\')',
        'Intercambios rechazados >2 meses' => 'DELETE FROM dte_intercambio AS i WHERE i.estado IS NOT NULL AND i.estado != 0 AND i.fecha_hora_email < (NOW() - INTERVAL \'2 MONTH\')',
    ];

    public function main($commit = false)
    {
        $this->out('Limpieza de registros del '.date('Y-m-d H:i:s'),2);
        // crear conexión a base de datos e iniciar transacción
        $this->db = database()
        $this->db->beginTransaction();
        // ejecutar consultas estándares de limpieza
        $total = 0;
        foreach ($this->reglas as $name => $query) {
            $rows = $this->eliminar($name, $query);
            $total += $rows;
            if ($this->verbose) {
                $this->out($name.': '.num($rows));
            }
        }
        // guardar transacción solo si se pidió explícitamente
        if (!empty($commit)) {
            $this->db->commit();
        } else {
            $this->db->rollback();
        }
        // estadísticas
        $this->out();
        if (!empty($commit)) {
            $this->out('Total registros eliminados: '.num($total),2);
        } else {
            $this->out('Total registros simulados: '.num($total),2);
        }
        $this->showStats();
        return 0;
    }

    private function eliminar($name, $query)
    {
        try {
            return $this->db->executeRawQuery($query)->rowCount();
        } catch (\Exception $e) {
            if ($this->verbose) {
                $this->out();
                $this->out('<error>'.$e->getMessage().'</error>', 2);
            }
            return 0;
        }
    }

}
