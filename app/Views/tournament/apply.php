<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card container shadow-sm">
    <div class="card-body">
        <h1 class="text-center">Tournament Registration</h1>
        <div class="p-3 mb-2 bg-primary-subtle text-primary-emphasis">
            <p>You've been invited to register for a tournament!</p>
            <p>
                To register for the Tournament, please indicate your participation mode below.
                If participating as a group/guild, only one member needs to register on behalf of the group.
            </p>
            <p>
                Upon indicating your participation status, you will be placed into the tournament bracket soon, and no further action is required after submitting the registration form.
                We look forward to your participation in the Tournament!
            </p>
            <p>Incredible rewards await the winners 🏆! Will you dare to take on the challenge?</p>
        </div>


        <?php if (session()->getFlashdata('message')): ?>
        <p style="color: green;"><?= session()->getFlashdata('message') ?></p>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <ul style="color: red;">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <form action="<?= site_url('/tournament/save-apply') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group mb-3">
                <div class="row">
                    <label for="participation_mode" class="col-auto">Participation Mode *</label>
                    <div class="col-auto">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="participation_mode" value="group" id="group">
                            <label class="form-check-label" for="group">Group/Guild</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="participation_mode" value="solo" id="solo">
                            <label class="form-check-label" for="solo">Solo</label>
                        </div>
                    </div>
                </div>


            </div>

            <div class="form-group mb-3">
                <label for="name">Name *</label>
                <input type="text" class="form-control" name="name" value="">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" name="agree" value="1" id="agree">
                <label class="form-check-label" for="agree">I/We agree to participate in the tournament.</label>
            </div>

            <button type="submit" class="btn btn-primary">REGISTER</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>