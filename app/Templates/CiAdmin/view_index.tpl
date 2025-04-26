<!DOCTYPE html>
<html>
<head>
    <title>Listado de {{viewFolder}}</title>
</head>
<body>
    <h1>Listado de {{viewFolder}}</h1>

    <p><a href="<?= site_url('{{viewFolder}}/create') ?>">Crear Nuevo</a></p>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
{{thead}}
            </tr>
        </thead>
        <tbody>
{{tbody}}
        </tbody>
    </table>

</body>
</html>
