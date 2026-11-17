<div class="card-body">
	<div class="row">
		<div class="col-12">
			<form <?= ui_binding('backend/account.messages.areYouSureStartingReset', 'data-message', [], true); ?> id="getResetPassword" class="w-100" data-message="<?= esc(lang('backend/account.messages.areYouSureStartingReset'), 'attr'); ?>" data-unsaved-check>
				<div class="row justify-content-start">
					<div class="col-12 col-lg-4">
						<button type="submit" class="btn app-btn-success btn-sm w-100">
							<i class="fa-solid fa-unlock"></i> <?= ui_text('backend/account.buttons.resetPassword', [], false); ?>
						</button>
					</div>
				</div>
			</form>
		</div>
		<?php if(isset($expiringDate) && $expiringDate): ?>
			<div class="col-12 mt-3">
				<div class="text-start fw-bold">
					<?= ui_text('backend/account.messages.expiringDate', [$expiringDate], false); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
