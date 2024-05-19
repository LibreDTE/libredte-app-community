<div class="container">
    <div class="page-header"><h1>Estado Dropbox <small><?=$Contribuyente->getRUT()?></small></h1></div>
    <p>El contribuyente <?=$Contribuyente->getNombre()?> tiene conectada la cuenta de LibreDTE con una cuenta en Dropbox. Esto permitirá que se realicen respaldos automáticos de los datos de la empresa.</p>
    <div class="row row-cols-2 g-3 text-center">
        <div class="col">
            <div class="card mb-4">
                <div class="card-body">
                    <span class="fas fa-user fa-fw fa-2x"></span>
                    <br/>
                    <span class="lead"><?=$account->getDisplayName()?></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-4">
                <div class="card-body">
                    <span class="fas fa-envelope fa-fw fa-2x"></span>
                    <br/>
                    <span class="lead"><?=$account->getEmail()?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-cols-2 g-3 text-center">
        <div class="col">
            <div class="card mb-4">
                <div class="card-body">
                    <span class="fas fa-globe fa-fw fa-2x"></span>
                    <br/>
                    <span class="lead"><?=$account->getCountry()?> / <?=$account->getLocale()?></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-4">
                <div class="card-body">
                    <span class="fas fa-database fa-fw fa-2x"></span>
                    <br/>
                    <span class="lead"><?=num($accountSpace['used']/1024/1024/1024,1)?> / <?=num($accountSpace['allocation']['allocated']/1024/1024/1024,1)?> GB</span>
                </div>
            </div>
        </div>
    </div>
    <div class="progress">
        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$uso?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$uso?>%;">
            <?=$uso?>%
        </div>
    </div>
</div>
