<div class="card-body">
	<div class="row">
		<div class="col-12">
			<form <?= ui_binding('frontend/account.messages.areYouSureStartingReset', 'data-message', [], true); ?> id="getResetPassword" class="w-100" data-message="<?= esc(lang('frontend/account.messages.areYouSureStartingReset'), 'attr'); ?>" data-unsaved-check>
				<div class="row justify-content-start">
					<div class="col-12 col-lg-4">
						<button type="submit" class="btn app-btn-success btn-sm w-100">
							<i class="fa-solid fa-unlock"></i> <?= ui_text('frontend/account.buttons.resetPassword', [], false); ?>
						</button>
					</div>
				</div>
			</form>
		</div>
		<?php if(isset($expiringDate) && $expiringDate): ?>
			<?php
				$dateClass = date('Y-m-d H:i:s') < $expiringDate ? 'app-text-success' : 'app-text-danger text-decoration-line-through';
				$variants = [];
				foreach (config('App')->supportedLocales as $locale):
					$date = '<span class="' . $dateClass . '">' . esc(convertDate($expiringDate, 'conversational', $locale)) . '</span>';
					$variants[$locale] = sprintf(lang('frontend/account.messages.expiringDate', [], $locale), $date);
				endforeach;
			?>
			<div class="col-12 mt-3">
				<div class="text-start fw-bold" data-i18n-text="<?= esc(json_encode(['variants' => $variants], JSON_THROW_ON_ERROR), 'attr'); ?>">
					<?= $variants[service('request')->getLocale()] ?? $variants['en']; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
