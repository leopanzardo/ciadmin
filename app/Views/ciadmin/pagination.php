<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);

if ($pager->getPageCount() > 1): ?>

<nav class="my-4" aria-label="<?= lang('Pager.pageNavigation') ?>">
	<ul class="pagination justify-content-center">
		<?php if ($pager->hasPrevious()) : ?>
			<li class="page-item">
				<a href="<?= $pager->getFirst() ?>" class="page-link" aria-label="<?= lang('Pager.first') ?>">
					<span aria-hidden="true"><?= lang('Pager.first') ?></span>
				</a>
			</li>
			<li class="page-item">
				<a href="<?= $pager->getPrevious() ?>" class="page-link" aria-label="<?= lang('Pager.previous') ?>">
					<span aria-hidden="true"><?= lang('Pager.previous') ?></span>
				</a>
			</li>
		<?php endif ?>

		<?php foreach ($pager->links() as $link) : ?>
			<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
				<a href="<?= $link['uri'] ?>" class="page-link">
					<?= $link['title'] ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ($pager->hasNext()) : ?>
			<li class="page-item">
				<a href="<?= $pager->getNext() ?>" class="page-link" aria-label="<?= lang('Pager.next') ?>">
					<span aria-hidden="true"><?= lang('Pager.next') ?></span>
				</a>
			</li>
			<li class="page-item">
				<a href="<?= $pager->getLast() ?>" class="page-link" aria-label="<?= lang('Pager.last') ?>">
					<span aria-hidden="true"><?= lang('Pager.last') ?></span>
				</a>
			</li>
		<?php endif ?>
	</ul>
</nav>

<?php endif ?>