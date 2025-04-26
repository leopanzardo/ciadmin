<!DOCTYPE html>
<html>
<head>
    <title>Editar {{viewFolder}}</title>
</head>
<body>
    <h1>Editar {{viewFolder}}</h1>

    <form method="post" action="<?= site_url('{{viewFolder}}/update/' . $row['id']) ?>">
        {{formFields}}
        <button type="submit">Actualizar</button>
    </form>

    <p><a href="<?= site_url('{{viewFolder}}') ?>">Volver al listado</a></p>
</body>
</html>
