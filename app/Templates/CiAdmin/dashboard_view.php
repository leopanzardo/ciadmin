<div class="container">
    <h1 class="mb-4"><?= CIADMIN_APPNAME ?></h1>

    <?php if (!empty($modules)): ?>
        <div class="list-group">
            <?php foreach ($modules as $module): ?>
                <a href="<?= site_url($module) ?>" class="list-group-item list-group-item-action">
                    <?= ucwords(str_replace('_', ' ', $module)) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay módulos disponibles aún.
        </div>
    <?php endif; ?>
</div>
