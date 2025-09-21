<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <?= $this->renderSection('pageStyles') ?>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="bg-light">

    <div class="header border-bottom sticky-top">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary p-0">
                <a class="navbar-brand" href="<?= base_url() ?>"><img src="/images/logo.jpg" class="logo" /></a>

                <button class="navbar-toggler order-lg-5" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link home d-flex align-items-center <?php if(current_url()==base_url()){echo "active";}?>" aria-current="page" href="<?= base_url() ?>">
                                Home
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link gallery d-flex align-items-center <?php if(current_url()==base_url('gallery')){echo "active";}?>" href="<?= base_url('gallery') ?>?filter=glr">
                                Tournament Gallery
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link dashboard d-flex align-items-center <?php if(current_url()==base_url('tournaments')){echo "active";}?>" href="<?= base_url('tournaments') ?>">
                                My Tournament Dashboard
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link leaderboard d-flex align-items-center <?php if(current_url()==base_url('participants')){echo "active";}?>" aria-current="page" href="<?= base_url('participants') ?>">
                                Participant Leaderboard
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link faq d-flex align-items-center <?php if(current_url()==base_url('contact')){echo "active";}?>" href="<?= base_url('contact') ?>">
                                About, FAQ, and Contact
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link terms d-flex align-items-center <?php if(current_url()==base_url('terms')){echo "active";}?>" href="<?= base_url('terms') ?>">
                                Terms of Service & Privacy Policy
                            </a>
                        </li>
                    </ul>
                </div>

            </nav>
        </div>
    </div>
    <main role="main" class="main-content">
        <?= $this->renderSection('main') ?>
    </main>

    <div class="footer border-top p-3">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>Pages</h4>
                    <ul class="link-group">
                        <li><a href="/"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/home.svg') ?>" class="menu-icon home"></object>Home</a></li>
                        <li><a href="<?= base_url('gallery') ?>?filter=glr"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/gallery.svg') ?>" class="menu-icon gallery"></object>Tournament Gallery</a></li>
                        <li><a href="<?= base_url('tournaments') ?>"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/dashboard.svg') ?>" class="menu-icon dashboard"></object>My Tournament Dashboard</a></li>
                        <li>
                            <a href="<?= base_url('participants') ?>"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/leaderboard.svg') ?>" class="menu-icon leaderboard"></object>Participant Leaderboard</a>
                        </li>
                        <li>
                            <a href="<?= base_url('contact') ?>"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/faq.svg') ?>" class="menu-icon faq"></object>About, FAQ, and Contact</a>
                        </li>
                        <li>
                            <a href="<?= base_url('terms') ?>"><object type="image/svg+xml" data="<?= base_url('images/menu-icons/terms.svg') ?>" class="menu-icon terms"></object>Terms of Service & Privacy Policy</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                </div>
                <div class="col-md-4">
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copy border-top p-3">
        <div class="container text-center">
            copyright ©️ 2025
        </div>
    </div>

    <!-- Cookie Consent Modal -->
    <div id="cookieConsentModal" style="display:none; position:fixed; bottom:0; width:100%; background-color:#f1f1f1; padding:10px; text-align:center;">
        <p>
            This site uses cookies 🍪 to store information for the purpose of enhancing user experience. <br> If you reject cookies, you may experience limitations with functionality.
        </p>
        <button onclick="acceptCookies()">Accept</button>
        <button onclick="rejectCookies()">Reject</button>
    </div>
    <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="/js/functions.js"></script>

    <script>
    // Show the modal if cookie consent is not given
    if (!localStorage.getItem('cookie_consent')) {
        document.getElementById('cookieConsentModal').style.display = 'block';
    }

    // Handle scatter effect for Create Tournament button
    document.querySelector('.navbar-brand').addEventListener('click', function(e) {
        const button = e.target;
        const buttonRect = button.getBoundingClientRect();
        const particlesCount = 5; // Number of particles to generate

        for (let i = 0; i < particlesCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            particle.textContent = '🔥';
            document.querySelector('.navbar-brand').appendChild(particle);

            // Increase the range of scatter
            const randomX = (Math.random() * 400 - 250); // Range -200px to +200px for more balanced scattering
            const randomY = (Math.random() * 400 - 250); // Range -200px to +200px for more balanced scattering

            particle.style.setProperty('--x', `${randomX}px`);
            particle.style.setProperty('--y', `${randomY}px`);

            // Position the particle near the center of the button
            const xPos = buttonRect.left + buttonRect.width / 2;
            const yPos = buttonRect.top + buttonRect.height / 2;
            particle.style.left = `${xPos}px`;
            particle.style.top = `${yPos}px`;

            particle.style.fontSize = '15px';

            // Remove the particle after animation
            setTimeout(() => {
                particle.remove();
            }, 1000); // Match the animation duration
        }
    });
    </script>

    <?= $this->renderSection('pageScripts') ?>
</body>

</html>