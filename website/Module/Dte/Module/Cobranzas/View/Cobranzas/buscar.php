<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa fa-search"></span> Filtrar
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="<?=$_base?>/dte/cobranzas/cobranzas/buscar?vencidos" class="dropdown-item">Vencidos <span class="badge bg-danger border"><?=num($cobranza_resumen['vencidos'])?></span></a>
            <a href="<?=$_base?>/dte/cobranzas/cobranzas/buscar?vencen_hoy" class="dropdown-item">Vencen hoy <span class="badge bg-warning border"><?=num($cobranza_resumen['vencen_hoy'])?></span></a>
            <a href="<?=$_base?>/dte/cobranzas/cobranzas/buscar?vigentes" class="dropdown-item">Vigentes <span class="badge bg-success border"><?=num($cobranza_resumen['vigentes'])?></span></a>
            <div class="dropdown-divider"></div>
            <a href="<?=$_base?>/dte/cobranzas/cobranzas/buscar" class="dropdown-item">Limpiar búsqueda</a>
        </div>
    </li>
</ul>
<div class="page-header"><h1>Buscar pagos programados ventas a crédito</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'check' => 'date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'check'=>'date',
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor',
    'placeholder' => 'RUT o razón social',
]);
echo $f->end('Buscar');

if (isset($cobranza)) {
    foreach ($cobranza as &$c) {
        $c[] = '<a href="'.$_base.'/dte/cobranzas/cobranzas/ver/'.$c['dte'].'/'.$c['folio'].'/'.$c['fecha_pago'].'" title="Ver pago" class="btn btn-primary"><span class="fa fa-search fa-fw"></span></a>';
        $c['fecha_emision'] = \sowerphp\general\Utility_Date::format($c['fecha_emision']);
        $c['fecha_pago'] = \sowerphp\general\Utility_Date::format($c['fecha_pago']);
        $c['total'] = num($c['total']);
        $c['monto_pago'] = num($c['monto_pago']);
        if ($c['pagado']!==null) {
            $c['pagado'] = num($c['pagado']);
        }
        unset($c['dte'], $c['rut']);
    }
    array_unshift($cobranza, ['Receptor', 'Emisión', 'Documento', 'Folio', 'Total', 'Fecha pago', 'Monto', 'Glosa', 'Parcial', 'Ver']);
    new \sowerphp\general\View_Helper_Table($cobranza, 'pagos_programados_pendientes', true);
}
