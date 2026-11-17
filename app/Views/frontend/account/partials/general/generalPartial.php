<div class="card-body">
    <div>
        <p class="lead"><i class="fa-solid fa-user"></i> <span class="fw-bold"><?= esc($currentUser->firstname) . ' ' . esc($currentUser->lastname) ?> </span></p>
        <p class="lead"><i class="fa-solid fa-envelope"></i> <span class="fw-bold"><?= esc($currentUser->email); ?> </span></p>
        <p class="lead"><i class="fa-solid fa-phone"></i> <span class="fw-bold"><?= esc($currentUser->phone); ?> </span></p>
    </div>
</div>