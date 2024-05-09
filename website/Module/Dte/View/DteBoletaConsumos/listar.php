<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/pendientes" class="nav-link">
            <i class="fa fa-calendar-alt"></i> Pendientes
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boletas" class="nav-link">
            <i class="fa fa-book"></i> Libro de boletas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Reporte consumo de folios boletas</h1></div>
<?php
// preparar títulos de columnas (con link para ordenar por dicho campo)
$titles = [];
$colsWidth = [120, 120, 120, 120, null, 100];
foreach ($columns as $column => $info) {
    $titles[] = $info['name'].' '.
        '<div class="float-end"><a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/A'.$searchUrl.'" title="Ordenar ascendentemente por '.$info['name'].'"><i class="fas fa-sort-alpha-down"></i></a>'.
        ' <a href="'.$_base.$module_url.$controller.'/listar/'.$page.'/'.$column.'/D'.$searchUrl.'" title="Ordenar descendentemente por '.$info['name'].'"><i class="fas fa-sort-alpha-up"></i></a></div>'
    ;
    $colsWidth[] = null;
}
$titles[] = 'Acciones';
$colsWidth[] = $actionsColsWidth;

// crear arreglo para la tabla y agregar títulos de columnas
$data = array($titles);

// agregar fila para búsqueda mediante formulario
$row = array();
$form = new \sowerphp\general\View_Helper_Form(false);
$optionsBoolean = array(array('', 'Todos'), array('1', 'Si'), array('0', 'No'));
$types_check = ['integer'=>'integer', 'real'=>'real'];
foreach ($columns as $column => &$info) {
    // si es un tipo de dato de fecha o fecha con hora se muestra un input para fecha
    if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
        $row[] = $form->input(array('type'=>'date', 'name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
    }
    // si es el estado se muestra un select
    else if ($column == 'revision_estado') {
        $row[] = $form->input([
            'type' => 'select',
            'name' => $column,
            'options' => [
                ''=> 'Todos',
                'null' => 'Sin estado',
                'ERRONEO' => 'Rechazados',
                'REPARO' => 'Con reparo',
                'CORRECTO' => 'Correctos',
            ],
            'value' => (isset($search[$column])?$search[$column]:''),
        ]);
    }
    // si es cualquier otro tipo de datos
    else {
        $row[] = $form->input([
            'name' => $column,
            'value' => (isset($search[$column])?$search[$column]:''),
            'check' => !empty($types_check[$info['type']]) ? $types_check[$info['type']] : null,
        ]);
    }
}
$row[] = '<button type="submit" class="btn btn-primary" onclick="return Form.check()"><i class="fa fa-search fa-fw"></i></button>';
$data[] = $row;

// función que procesa el resumen y lo entrega como string para la vista
function resumen2string($resumen)
{
    // preparar datos que se mostrarán
    $cols = [
        'TipoDocumento' => 'DTE',
        'FoliosEmitidos' => 'Docs.',
        'MntExento' => 'Exento',
        'MntNeto' => 'Neto',
        'MntIva' => 'IVA',
        'MntTotal' => 'Total',
    ];
    $aux = [];
    foreach($resumen as $dte => $data) {
        foreach($cols as $from => $to) {
            if (!empty($data[$from])) {
                $aux[$dte][$to] = $data[$from];
            }
        }
    }
    // armar el string con los datos preparados
    $string = [];
    foreach($aux as $dte => $data) {
        $values = [];
        foreach($data as $k => $v) {
            $values[] = $k.': '.num($v);
        }
        $string[] = implode(' / ', $values);
    }
    return implode('<br/>', $string);
}

// crear filas de la tabla
foreach ($Objs as &$obj) {
    $row = array();
    foreach ($columns as $column => &$info) {
        if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
            $row[] = \sowerphp\general\Utility_Date::format($obj->{$column});
        }
        // si es la columna de detalle dependerá del estado
        else if ($column == 'revision_detalle' && $obj->revision_estado == 'CORRECTO') {
            $row[] = resumen2string($obj->getResumen());
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $obj->{$column};
        }
    }
    // acciones
    $actions = '<div class="btn-group">';
    $actions .= '<a href="'.$_base.$module_url.$controller.'/actualizar_estado/'.$obj->dia.$listarFilterUrl.'" title="Actualizar estado del envio al SII" class="btn btn-primary" onclick="return __.loading(\'Actualizando estado del RCOF...\')"><i class="fas fa-sync fa-fw me-2"></i></a>';
    $actions .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="visually-hidden">Toggle Dropdown</span></button>';
    $actions .= '<div class="dropdown-menu dropdown-menu-end">';
    if ($obj->track_id) {
        $actions .= '<a href="'.$_base.$module_url.$controller.'/xml/'.$obj->dia.'" title="Descargar XML" class="dropdown-item"><i class="far fa-file-code fa-fw me-2"></i> Descargar XML</a>';
        $actions .= '<a href="'.$_base.$module_url.$controller.'/solicitar_revision/'.$obj->dia.$listarFilterUrl.'" title="Solicitar revisión del envio al SII" class="dropdown-item" onclick="return __.loading(\'Solicitando revisión del envío al SII...\')"><i class="fab fa-rev fa-fw me-2"></i> Solicitar revisión</a>';
        $actions .= '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/estado_envio/'.$obj->track_id.'\', 750, 550); return false" title="Ver el estado del envío en la web del SII" class="dropdown-item"><i class="fa fa-eye fa-fw me-2"></i> Ver estado en SII</a>';
        $actions .= '<div class="dropdown-divider"></div>';
        $actions .= '<a href="'.$_base.$module_url.$controller.'/enviar_sii/'.$obj->dia.$listarFilterUrl.'" title="Reenviar el RCOF al SII" class="dropdown-item" onclick="return __.loading(\'Enviando RCOF al SII...\')"><i class="fas fa-paper-plane fa-fw me-2"></i> Reenviar RCOF</a>';
    }
    if ($is_admin) {
        $actions .= '<div class="dropdown-divider"></div>';
        $actions .= '<a href="'.$_base.$module_url.$controller.'/eliminar/'.$obj->dia.$listarFilterUrl.'" title="Eliminar el RCOF" class="dropdown-item" onclick="return __.confirm(this, \'¿Desea eliminar el RCOF del día '.\sowerphp\general\Utility_Date::format($obj->dia).'?<br/><br/><strong>Importante</strong>: Esto no lo eliminará del SII si fue aceptado\')"><i class="fas fa-times fa-fw me-2"></i> Eliminar RCOF</a>';
    }
    $actions .= '</div>';
    $actions .= '</div>';
    $row[] = $actions;
    $data[] = $row;
}

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer ([
    'link' => $_base.$module_url.$controller,
    'linkEnd' => $linkEnd,
    'listarFilterUrl' => $listarFilterUrl
]);
$maintainer->setId($models);
$maintainer->setColsWidth($colsWidth);
echo $maintainer->listar ($data, $pages, $page);
