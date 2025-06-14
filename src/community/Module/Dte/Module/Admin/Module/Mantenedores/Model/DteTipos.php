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

namespace website\Dte\Admin\Mantenedores;

use sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "dte_tipo" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_DteTipos extends Model_Plural
{
    private $internos = [
        'HEM' => 'Hoja de entrada de materiales (HEM)',
        'HES' => 'Hoja de entrada de servicios (HES)',
        'EM' => 'Entrada de mercadería (EM)',
        'RDM' => 'Recepción de material/mercadería (RDM)',
        'MLE' => 'Modalidad libre elección (MLE)',
        'RC' => 'Recepción Conforme (RC)',
    ]; ///< Tipos de documentos internos de LibreDTE (sin código oficial del SII)

    /**
     * Entrega el listado de tipos de documentos tributarios.
     */
    public function getList($all = false): array
    {
        if ($all) {
            if (is_array($all)) {
                return $this->getDatabaseConnection()->getTable('
                    SELECT codigo, codigo || \' - \' || tipo AS glosa
                    FROM dte_tipo
                    WHERE codigo IN ('.implode(',', $all).')
                    ORDER BY codigo
                ');
            } else {
                return $this->getDatabaseConnection()->getTable('
                    SELECT codigo, codigo || \' - \' || tipo AS glosa
                    FROM dte_tipo
                    WHERE categoria = \'T\'
                    ORDER BY codigo
                ');
            }
        } else {
            return $this->getDatabaseConnection()->getTable('
                SELECT codigo, codigo || \' - \' || tipo AS glosa
                FROM dte_tipo
                WHERE categoria = \'T\' AND electronico = true
                ORDER BY codigo
            ');
        }
    }

    /**
     * Entrega el listado de todos los tipos de documentos que se
     * pueden usar como referencias.
     */
    public function getListReferencias()
    {
        $tipos = $this->getDatabaseConnection()->getTable('
            SELECT codigo, codigo || \' - \' || tipo AS glosa
            FROM dte_tipo
            ORDER BY codigo
        ');
        foreach ($this->internos as $codigo => $glosa) {
            $tipos[] = [$codigo, $codigo.' - '.$glosa];
        }
        return $tipos;
    }
}
