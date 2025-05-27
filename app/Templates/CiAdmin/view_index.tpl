<div class="container">
	<h1 class="mb-4">Listado de {{viewFolder}}</h1>

	<div class="mb-3 text-end">
		<a href="<?= site_url('{{viewFolder}}/create') ?>" class="btn btn-success"><i class="bi bi-plus-square me-2"></i>Crear Nuevo</a>
	</div>

	<div class="table-responsive">
		<table class="table table-hover table-bordered">
			<thead class="table-primary">
				<tr>
{{thead}}
				</tr>
			</thead>
			<tbody>
{{tbody}}
			</tbody>
		</table>
	</div>
	
	<?= $pager->links('{{viewFolder}}', 'ciadmin') ?>
	
	<div class="mt-4">
		<a href="<?= site_url('/') ?>" class="btn btn-secondary">
			<i class="bi bi-arrow-left"></i> Volver al Dashboard
		</a>
	</div>

</div>
