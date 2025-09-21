<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="d-flex justify-content-center" style="flex:auto;">
    <h1>Change Email</h1>
    <?= session('message') ?>
    <?= \Config\Services::validation()->listErrors() ?>

    <form action="<?= site_url('profile/update-email') ?>" method="post">
        <?= csrf_field() ?>

        <label for="new_email">New Email</label>
        <input type="email" name="new_email" id="new_email" value="<?= old('new_email') ?>" required>

        <button type="submit">Change Email</button>
    </form>
</div>
<?= $this->endSection() ?>