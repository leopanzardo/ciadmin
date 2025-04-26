<!DOCTYPE html>
<html>
<head>
    <title>Crear {{viewFolder}}</title>
</head>
<body>
    <h1>Crear {{viewFolder}}</h1>

    <form method="post" action="<?= site_url('{{viewFolder}}/store') ?>">
        {{formFields}}
        <button type="submit">Guardar</button>
    </form>

    <p><a href="<?= site_url('{{viewFolder}}') ?>">Volver al listado</a></p>
</body>
</html>
