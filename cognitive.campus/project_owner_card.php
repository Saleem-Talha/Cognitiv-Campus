<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-3">Project Owner</h2>
            <div class="card-body">
                <p class="text-center">Project Owner: <?php echo $ownerEmail?></p>
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <img src="<?php echo get_gravatar($ownerEmail); ?>" alt="Owner Avatar" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                    <p class="mb-0 fw-bold">Project Owner: <span class="text-primary"><?php echo $ownerEmail; ?></span></p>
                </div>
            </div>
        </div>
    </div>
</div>