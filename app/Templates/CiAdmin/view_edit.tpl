<div class="container">
    <h1 class="mb-4">Editar {{viewFolder}}</h1>

    <form method="post" action="<?= site_url('{{viewFolder}}/update/' . $row['id']) ?>" class="needs-validation" novalidate>
        <div class="row border rounded shadow p-3">
            {{formFields}}
        </div>

        <div class="row mt-4">
            <div class="col">
                <a href="<?= site_url('{{viewFolder}}') ?>" class="btn btn-secondary">Volver al listado</a>
            </div>
            <div class="col text-end">
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </form>
</div>

<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
