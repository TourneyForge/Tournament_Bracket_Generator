<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.verificationSuccessTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <?php if (session('error') !== null) : ?>
            <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
            <?php elseif (session('errors') !== null) : ?>
            <div class="alert alert-danger" role="alert">
                <?php if (is_array(session('errors'))) : ?>
                <?php foreach (session('errors') as $error) : ?>
                <?= $error ?>
                <br>
                <?php endforeach ?>
                <?php else : ?>
                <?= session('errors') ?>
                <?php endif ?>
            </div>
            <?php endif ?>

            <h2>Registration Successful!</h2>
            <p>You will be redirected to the homepage in 5 seconds...</p>
            <div class="stage">
                <div class="dot-elastic"></div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
setTimeout(function() {
    window.location.href = "/";
}, 5000); // Redirect after 5 seconds
</script>
<?= $this->endSection() ?>