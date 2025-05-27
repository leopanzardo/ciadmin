<div class="container">
    <h1 class="mb-4">Detalles de {{viewFolder}}</h1>

        <div class="row border rounded shadow p-3">
            {{rowFields}}
        </div>

        <div class="row mt-4">
            <div class="col">
                <a href="<?= site_url('{{viewFolder}}') ?>" class="btn btn-secondary">Volver al listado</a>
            </div>
            <div class="col text-end">
                <a href="<?= site_url('{{viewFolder}}/edit/' . $row[$primaryKey]) ?>" class="btn btn-primary">Editar</a>
            </div>
        </div>

</div>
