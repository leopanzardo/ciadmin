<div class="container">
    <h1 class="mb-4">Listado de {{viewFolder}}</h1>

    <div class="mb-3">
        <a href="<?= site_url('{{viewFolder}}/create') ?>" class="btn btn-success">Crear Nuevo</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
{{thead}}
                </tr>
            </thead>
            <tbody>
{{tbody}}
            </tbody>
        </table>
    </div>
</div>
