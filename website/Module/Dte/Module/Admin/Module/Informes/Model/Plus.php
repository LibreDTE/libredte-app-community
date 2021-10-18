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

// namespace del modelo
namespace website\Dte\Admin\Informes;

/**
 * Modelo para obtener datos de planes plus
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-01
 */
class Model_Plus
{

    /**
     * Constructor del modelo Plus
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-01
     */
    public function __construct()
    {
        $this->db = &\sowerphp\core\Model_Datasource_Database::get();
    }

    /**
     * Método que entrega el listado de usuarios plus y sus empresas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-26
     */
    public function getAll()
    {
        return $this->db->getTable('
            SELECT
                u.usuario,
                u.nombre,
                u.email,
                u.ultimo_ingreso_fecha_hora,
                c.rut,
                c.razon_social,
                c.email AS email_contribuyente,
                c.telefono,
                cc.valor AS en_certificacion
            FROM
                usuario AS u,
                usuario_grupo AS ug,
                grupo As g,
                contribuyente AS c,
                contribuyente_config AS cc
            WHERE
                ug.grupo = g.id
                AND ug.usuario = u.id
                AND g.grupo = \'dte_plus\'
                AND c.usuario = u.id
                AND cc.contribuyente = c.rut
                AND cc.configuracion = \'ambiente\'
                AND cc.variable = \'en_certificacion\'
            ORDER BY u.usuario, c.razon_social
        ');
    }

}
