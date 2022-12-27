<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/item_clasificaciones/listar?search=activa:1" title="Mantenedor de clasificaciones de items" class="nav-link">
            <i class="fa fa-list-alt"></i> Clasificaciones
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/itemes/exportar" title="Exportar items a archivo CSV" class="nav-link">
            <i class="fa fa-download"></i> Exportar
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/itemes/importar" title="Importar items desde archivo CSV" class="nav-link">
            <i class="fa fa-upload"></i> Importar
        </a>
    </li>
</ul>

<div class="page-header"><h1>Listado de productos y/o servicios</h1></div>
<p><?=$comment?></p>

<?php

// preparar títulos de columnas (con link para ordenar por dicho campo)
$titles = [];
$colsWidth = [];
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
    // si es la moneda se muestra un select
    if ($column == 'moneda') {
        $row[] = $form->input([
            'type' => 'select',
            'name' => $column,
            'options' => [''=>'Todas', 'CLP'=>'Pesos', 'CLF'=>'UF', 'USD'=>'Dólares', 'EUR'=>'Euros'],
            'value' => (isset($search[$column])?$search[$column]:''),
        ]);
    }
    // si es de tipo boolean se muestra lista desplegable
    else if ($info['type']=='boolean' || $info['type']=='tinyint') {
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $optionsBoolean, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es llave foránea
    else if ($info['fk']) {
        $class = 'Model_'.\sowerphp\core\Utility_Inflector::camelize(
            $info['fk']['table']
        );
        $classs = $fkNamespace[$class].'\Model_'.\sowerphp\core\Utility_Inflector::camelize(
            \sowerphp\core\Utility_Inflector::pluralize($info['fk']['table'])
        );
        $objs = new $classs();
        $options = $objs->getList();
        array_unshift($options, array('', 'Todos'));
        $row[] = $form->input(array('type'=>'select', 'name'=>$column, 'options' => $options, 'value' => (isset($search[$column])?$search[$column]:'')));
    }
    // si es un tipo de dato de fecha o fecha con hora se muestra un input para fecha
    else if (in_array($info['type'], ['date', 'timestamp', 'timestamp without time zone'])) {
        $row[] = $form->input(array('type'=>'date', 'name'=>$column, 'value'=>(isset($search[$column])?$search[$column]:'')));
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

// crear filas de la tabla
foreach ($Objs as &$obj) {
    $row = array();
    foreach ($columns as $column => &$info) {
        // si es un archivo
        if ($info['type']=='file') {
            if ($obj->{$column.'_size'})
                $row[] = '<a href="'.$_base.$module_url.$controller.'/d/'.$column.'/'.urlencode($obj->id).'" class="btn btn-primary"><i class="fa fa-download"></i></a>';
            else
                $row[] = '';
        }
        // si es boolean se usa Si o No según corresponda
        else if ($info['type']=='boolean' || $info['type']=='tinyint') {
            $row[] = $obj->{$column}=='t' || $obj->{$column}=='1' ? 'Si' : 'No';
        }
        // si es llave foránea
        else if ($info['fk']) {
            // si no es vacía la columna
            if (!empty($obj->{$column})) {
                $method = 'get'.\sowerphp\core\Utility_Inflector::camelize($info['fk']['table']);
                $row[] = $obj->$method($obj->$column)->{$info['fk']['table']};
            } else {
                $row[] = '';
            }
        }
        // si es cualquier otro tipo de datos
        else {
            $row[] = $info['type']=='real' ? (float)$obj->{$column} : $obj->{$column};
        }
    }
    $pkValues = $obj->getPkValues();
    $pkURL = implode('/', array_map('urlencode', $pkValues));
    $actions = '';
    if (!empty($extraActions)) {
        foreach ($extraActions as $a => $i) {
            $actions .= '<a href="'.$_base.$module_url.$controller.'/'.$a.'/'.$pkURL.$listarFilterUrl.'" title="'.(isset($i['desc'])?$i['desc']:'').'"><i class="'.$i['icon'].' btn btn-primary"></i></a> ';
        }
    }
    $actions .= '<a href="'.$_base.$module_url.$controller.'/editar/'.urlencode($obj->codigo).'/'.urlencode($obj->codigo_tipo).$listarFilterUrl.'" title="Editar" class="btn btn-primary"><i class="fa fa-edit fa-fw"></i></a>';
    if ($deleteRecord) {
        $actions .= ' <a href="'.$_base.$module_url.$controller.'/eliminar/'.urlencode($obj->codigo).'/'.urlencode($obj->codigo_tipo).$listarFilterUrl.'" title="Eliminar" onclick="return eliminar(this, \''.$model.'\', \''.implode(', ', $pkValues).'\')" class="btn btn-primary"><i class="fas fa-times fa-fw"></i></a>';
    }
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
?>
<div class="mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=146">¿Cómo configuro el listado de productos y/o servicios?</a>
            </h5>
        </div>
    </div>
</div>
