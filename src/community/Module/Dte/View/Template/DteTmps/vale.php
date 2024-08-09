<?php $__view_layout .= '.min'; ?>
<div class="text-center">
    <span class="lead">RUT <?=$DteTmp->getReceptor()->getRUT()?></span><br/>
    <span class="lead">Total $<?=num($DteTmp->total)?></span><br/><br/>
    <img src="<?=$_base?>/exportar/barcode/<?=base64_encode($DteTmp->getFolio())?>" alt="Barcode <?=$DteTmp->getFolio()?>" style="width:60mm" /><br/>
    <?=$DteTmp->getTipo()->tipo?>: <?=$DteTmp->getFolio()?><br/>
    <a href="javascript:window.print()" class="small d-print-none"><span class="fa fa-print"></span> imprimir</a>
</div>
