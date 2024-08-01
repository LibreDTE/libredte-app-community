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

use \sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "item_clasificacion" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_ItemClasificaciones extends Model_Plural
{

    /**
     * Método que entrega el listado de clasificaciones.
     */
    public function getList()
    {
        $items = \sowerphp\core\Utility_Array::treeToList(
            $this->getArbolItems(), 'clasificacion', 'clasificaciones'
        );
        return array_map(
            function($key, $value) { return [$key, $value]; },
            array_keys($items),
            $items
        );
    }

    /**
     * Método que entrega el listado de clasificaciones con sus items y valores brutos.
     */
    public function getListItems()
    {
        return \sowerphp\core\Utility_Array::treeToAssociativeArray(
            $this->getArbolItems(), 'clasificacion', 'clasificaciones'
        );
    }

    /**
     * Método que entrega el árbol de clasificaciones de items con los items y
     * sus precios.
     */
    public function getArbolItems()
    {
        return \sowerphp\core\Utility_Array::toTree(
            \sowerphp\core\Utility_Array::tableToAssociativeArray(
                \sowerphp\core\Utility_Array::fromTableWithHeaderAndBody(
                    $this->getDatabaseConnection()->getTable('
                        SELECT
                            c.codigo AS clasificacion_codigo,
                            c.clasificacion,
                            c.superior,
                            i.codigo,
                            i.item,
                            CASE WHEN i.bruto OR i.exento != 0 THEN
                                i.precio
                            ELSE
                                i.precio * 1.'.\sasco\LibreDTE\Sii::getIVA().'
                            END AS precio,
                            i.moneda,
                            CASE WHEN i.descuento_tipo = \'%\' OR i.bruto OR i.exento != 0 THEN
                                i.descuento
                            ELSE
                                i.descuento * 1.'.\sasco\LibreDTE\Sii::getIVA().'
                            END AS descuento,
                            i.descuento_tipo
                        FROM
                            item AS i
                            RIGHT JOIN item_clasificacion AS c ON
                                i.contribuyente = c.contribuyente
                                AND i.clasificacion = c.codigo
                        WHERE
                            c.contribuyente = :rut
                            AND c.activa = true
                            AND (i.codigo IS NULL OR i.activo = true)
                        ORDER BY c.clasificacion, i.item
                    ', [
                        ':rut' => $this->getContribuyente()->rut
                    ]),
                    3,
                    'items'
                )
            ),
            'superior',
            'clasificaciones'
        );
    }

    /**
     * Método que exporta todas las clasificaciones de items de un contribuyente.
     */
    public function exportar()
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, clasificacion, superior, activa
            FROM item_clasificacion
            WHERE contribuyente = :contribuyente
            ORDER BY superior, codigo
        ', [':contribuyente' => $this->getContribuyente()->rut]);
    }

}
