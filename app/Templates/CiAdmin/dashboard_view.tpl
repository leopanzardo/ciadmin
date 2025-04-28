<div class="container">
    <h1 class="mb-4">Panel de Administración</h1>

    <div class="list-group">
        <?php foreach ($modules as $module): ?>
            <a href="<?= site_url($module) ?>" class="list-group-item list-group-item-action">
                <?= ucfirst(str_replace('_', ' ', $module)) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
