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

// namespace del modelo
namespace website\Dte\Admin\Mantenedores;

/**
 * Clase para mapear la tabla iva_no_recuperable de la base de datos
 * Comentario de la tabla: Tipos de IVA no recuperable
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla iva_no_recuperable
 * @author SowerPHP Code Generator
 * @version 2015-09-27 18:24:13
 */
class Model_IvaNoRecuperables extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'iva_no_recuperable'; ///< Tabla del modelo

    /**
     * Método que entrega el listado de ivas no recuperables
         * @version 2015-09-27
     */
    public function getList()
    {
        return $this->db->getTable('
            SELECT codigo, '.$this->db->concat('codigo', ' - ', 'tipo').'
            FROM iva_no_recuperable
            ORDER BY codigo
        ');
    }

}
