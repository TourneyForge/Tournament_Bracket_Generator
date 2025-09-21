<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script type="text/javascript">
// Handle scatter effect for Create Tournament button
document.querySelector('.create-tournament').addEventListener('click', function(e) {
    const button = e.target;
    const buttonRect = button.getBoundingClientRect();
    const particlesCount = 30; // Number of particles to generate

    for (let i = 0; i < particlesCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        particle.textContent = '+';
        document.body.appendChild(particle);

        // Increase the range of scatter
        const randomX = (Math.random() * 400 - 200); // Range -200px to +200px for more balanced scattering
        const randomY = (Math.random() * 400 - 200); // Range -200px to +200px for more balanced scattering

        particle.style.setProperty('--x', `${randomX}px`);
        particle.style.setProperty('--y', `${randomY}px`);

        // Position the particle near the center of the button
        const xPos = buttonRect.left + buttonRect.width / 2;
        const yPos = buttonRect.top + buttonRect.height / 2 + window.scrollY;
        particle.style.left = `${xPos}px`;
        particle.style.top = `${yPos}px`;
        console.log(particle.style.top)

        // Remove the particle after animation
        setTimeout(() => {
            particle.remove();
        }, 1000); // Match the animation duration
    }
});

// Handle teleport button click for beam scatter effect
document.querySelector('.teleport').addEventListener('click', function(e) {
    const button = e.target;

    // Add clicked class for animation
    button.classList.add('clicked');

    // Remove clicked class after animation ends to reset the effect
    setTimeout(() => {
        button.classList.remove('clicked');
    }, 1000); // Match the animation duration
});
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid align-middle">
    <div class="home-title row d-flex justify-content-center text-center p-3">
        <h1 class="p-3"><strong>Welcome to TourneyForge</strong></h1>
        <p>Where Competition Meets Creativity!</p>
    </div>

    <div class="row">
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <div class="home-content p-5">
                <p class="title text-center">
                    Build Epic Brackets with a Click, all for free!<br />
                </p>
                <div>
                    Choose from various elimination types, customize themes, and decide how winners are determined — manually or through voting.<br />
                    Make your tournaments truly immersive with audio/video playback for dramatic matchups and thrilling finales.<br />
                    Plus, enjoy sleek animations that bring your brackets to life!
                </div>
                <p class="text-center"><a class="create-tournament btn btn-success mt-5" href="<?= base_url('/tournaments/create') ?>">Create Tournament</a></p>
            </div>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Here to spectate?</span><br />
                <span>Visit the Tournament Gallery!</span><br />
                <a class="teleport btn btn-danger mt-3" href="<?= base_url('/gallery?filter=glr') ?>">Teleport to Gallery</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Feeling the hype?</span><br />
                <span>Checkout the top contestants on the Participant Leaderboard!</span><br />
                <a class="leaderboard btn btn-info light mt-3" href="<?= base_url('/participants') ?>">Check Leaderboard</a>
            </p>
        </div>
        <div class="home-block col-md-12 d-flex align-items-center justify-content-center text-center">
            <p class="home-content">
                <span class="title text-center">Want to manage/customize your tournaments?</span><br />
                <span>Signup/Signin now to access your own dedicated Tournament Dashboard!</span><br />
                <a class="dashboard btn btn-warning mt-3" href="<?= base_url('/tournaments') ?>">My Tournament Dashboard</a>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>