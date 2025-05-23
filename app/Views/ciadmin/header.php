<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title><?= CIADMIN_APPNAME ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
	</head>
	<body>

		<nav class="navbar navbar-expand-lg navbar-light bg-info mb-4">
			<div class="container-fluid">
				<a class="navbar-brand" href="/"><?= CIADMIN_APPNAME ?></a>
			</div>
		</nav>

		<div class="container">
		
		<?= displayFlashes() ?>
