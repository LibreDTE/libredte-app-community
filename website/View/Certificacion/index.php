<h1><?=$title?></h1>
<div id="navpanel">
<?php foreach ($nav as $link=>&$info): ?>
    <div class="pull-left">
        <div class="icon">
            <a href="<?=$_base,'/',$module,$link?>" title="<?=$info['desc']?>">
<?php if (isset($info['icon'])) : ?>
            <span class="<?=$info['icon']?>" aria-hidden="true" style="font-size:48px;margin-top:10px"></span>
<?php else : ?>
                <img src="<?=$_base,$info['imag']?>" alt="<?=$info['name']?>" align="middle" />
<?php endif; ?>
                <span><?=$info['name']?></span>
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<div style="clear:both;margin-top:2em">&nbsp;</div>

<a class="btn btn-primary btn-lg btn-block" href="http://wiki.libredte.cl/doku.php/faq/libredte/webapp/certificacion" role="button">
    ¿Cómo realizo el proceso de certificación utilizando LibreDTE?
</a>
