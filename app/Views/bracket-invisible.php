<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Invisible Tournament<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card col-12 shadow-sm" style="min-height: calc(100vh - 60px);">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:;" onclick="history.back()"><i class="fa fa-angle-left"></i> Back</a>
                </li>
            </ol>
        </nav>

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

        <?php if (isset($tournament)): ?>
        <h5 class="card-title d-flex justify-content-center mb-5">
            <?= $tournament['name'] ?>
        </h5>

        <?php $userSettingService = service('userSettings') ?>
        <div class="alert alert-danger" role="alert">
            <?php if (!$userSettingService->get('hide_email_host', $tournament['user_id'])): ?>
            This tournament is not made public by the host (<?= $created_by ? $created_by->email : 'Guest User' ?>).
            <?php else: ?>
            This tournament is not made public by the host (<?= $created_by ? $created_by->username : 'Guest User' ?>).
            <?php endif ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>