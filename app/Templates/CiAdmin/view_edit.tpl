<div class="container">
    <h1 class="mb-4">Editar {{viewFolder}}</h1>

    <form method="post" action="<?= site_url('{{viewFolder}}/update/' . $row['id']) ?>" class="needs-validation" novalidate>
        <div class="row">
            {{formFields}}
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="<?= site_url('{{viewFolder}}') ?>" class="btn btn-secondary">Volver al listado</a>
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
