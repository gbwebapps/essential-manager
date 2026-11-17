<div class="card-body">
    <div class="row mb-3">

        <!-- Campo nome e cognome -->
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.firstname', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= esc($admin->firstname); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6">
            <!-- Campo lastname -->
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.lastname', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= esc($admin->lastname); ?></li>
            </ul>
        </div>
        <!-- End Campo nome e cognome -->

    </div>
    <div class="row mb-3">

        <!-- Campo email e telefono -->
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.email', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= esc($admin->email); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.phone', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= esc($admin->phone); ?></li>
            </ul>
        </div>
        <!-- End Campo email e telefono -->

    </div>
    <div class="row">

        <!-- Campo stato -->
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.status', [], false); ?></li>
                <li class="list-group-item">
                    <div id="changeStatusPartial">
                        <?= $this->include('backend/users/partials/show/changeStatusPartial', $this->data); ?>
                    </div>
                </li>
            </ul>
        </div>
        <!-- End Campo stato -->

    </div>
    <?php if( ! empty($admin->note)): ?>
        <div class="row">

            <!-- Campo note -->
            <div class="col-12">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/users.labels.note', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= esc($admin->note); ?></li>
                </ul>
            </div>
            <!-- End Campo note -->

        </div>
    <?php endif; ?>
</div>