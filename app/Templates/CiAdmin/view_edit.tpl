<div class="container">
    <h1 class="mb-4">Editar {{viewFolder}}</h1>

    <form method="post" action="<?= site_url('{{viewFolder}}/update/' . $row['id']) ?>">
        <div class="row">
            {{formFields}}
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?= site_url('{{viewFolder}}') ?>" class="btn btn-secondary">Volver al listado</a>
        </div>
    </form>
</div>
