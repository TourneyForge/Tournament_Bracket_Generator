<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/themes/base/jquery-ui.min.css" integrity="sha512-F8mgNaoH6SSws+tuDTveIu+hx6JkVcuLqTQ/S/KJaHJjGc8eUxIrBawMnasq2FDlfo7FYsD8buQXVwD+0upbcA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.css">

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/fontawesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?= $this->renderSection('pageStyles') ?>
    <link rel="stylesheet" href="/css/theme.css">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="<?= (isset($page) && $page == 'view' && isset($tournament) && $tournament['theme']) ? 'bg-' . $tournament['theme'] : 'bg-light' ?>">

    <main role="main">
        <div class="header border-bottom sticky-top">
            <div class="container-fluid">
                <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary position-relative d-flex p-0">
                    <a class="navbar-brand me-auto pe-3" href="<?= base_url() ?>"><img src="/images/logo.jpg" class="logo" /></a>

                    <?php if (auth()->user() && auth()->user()->id) : ?>
                    <div class="notification-box me-3 order-lg-3">
                        <?php $notificationService = service('notification'); ?>
                        <?php $notifications = $notificationService->getNotifications(auth()->user()->id) ?>
                        <button class="btn btn-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell"></i>
                            <?php if (count($notifications)): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= count($notifications) ?>
                            </span>
                            <?php endif ?>
                        </button>
                        <ul class="dropdown-menu  dropdown-menu-end">
                            <li class="d-flex justify-content-between border-bottom ps-3 pe-3">
                                <div class="form-check form-switch form-check-reverse">
                                    <?php $userSettingService = service('userSettings') ?>
                                    <?php $emailNotificationSetting = $userSettingService->get('email_notification') ?>
                                    <input class="form-check-input" type="checkbox" id="emailNotificationSwitch" onchange="changeEmailNotificationSetting(this)" <?= (!$emailNotificationSetting || $emailNotificationSetting == 'on') ? "checked": '' ?>>
                                    <label class="form-check-label" for="emailNotificationSwitch">Email Notifications</label>
                                </div>
                                <?php if (count($notifications)): ?>
                                <a href="javascript:;" class="clear-notifications" data-bs-toggle="modal" data-bs-target="#clearNotificationsModal">Clear All</a>
                                <?php endif; ?>
                            </li>
                            <?php if (count($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                            <li class="notification">
                                <p class="dropdown-item border-bottom p-2 pe-5">
                                    <a class="text-end ms-2 me-4" href="javascript:;" onclick="readNotification(this)" data-link="<?= base_url($notification['link']) ?>" data-id="<?= $notification['id'] ?>">
                                        <span><?= $notification['message'] ?></span><br />
                                        <span class="d-block w-100 text-end">By <?= $notification['created_by'] ?? 'Guest' ?> on <?= convert_to_user_timezone($notification['created_at'], user_timezone(auth()->user()->id)) ?></span>
                                    </a>
                                    <a class="delete" onclick="deleteNotification(this)" data-link="<?= base_url($notification['link']) ?>" data-id="<?= $notification['id'] ?>"><i class="fa fa-remove"></i></a>
                                </p>
                            </li>
                            <?php endforeach ?>
                            <?php else: ?>
                            <li class="notification">
                                <p class="dropdown-item p-2 pe-5">No Notifications</p>
                            </li>
                            <?php endif ?>
                        </ul>
                    </div>

                    <div class="d-flex order-lg-4 position-relative profile">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= auth()->user()->username ?>'s profile
                        </button>
                        <ul class="profile-menu dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal">General Settings</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('profile') ?>">Profile Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo base_url('logout') ?>">Log out</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a class="btn btn-primary me-2 order-lg-3" href="<?= base_url('login') ?>">Login</a>
                    <a class="btn btn-primary me-2 order-lg-4" href="<?= base_url('register') ?>">Signup</a>
                    <?php endif; ?>

                    <button class="navbar-toggler ms-2 order-lg-5" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
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

        <div class="main-content container-fluid p-0">
            <div id="notificationAlertPlaceholder" class="position-fixed"></div>

            <?= $this->renderSection('main') ?>
        </div>

        <div class="footer border-top p-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
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
    </main>

    <!-- Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">General Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <p class="mb-0">All timestamps will be based on the following Timezone setting.</p>
                                <p class="mb-0">Default is EST (time offset is −5 hours (UTC/GMT -5) during standard time and −4 hours (UTC/GMT -4) during daylight saving time)</p>
                                <p class="mt-3">You may need to refresh the page after modifications to the timezone settings to observe the updated timestamps</p>
                            </div>
                        </div>
                        <div class="row">
                            <label for="timezone" class="form-label col-sm-3 col-form-label">Timezone</label>
                            <div class="col-sm-9">
                                <select class="form-select" id="timezone" name="<?= USERSETTING_TIMEZONE ?>">
                                    <!-- Timezone options will be populated here -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div id="timezoneInfo" class="col-md-9 offset-md-3">
                                <div class="row">
                                    <p class="col-6 mb-0"><em>UTC time is: <span id="utcTime"></span></em></p>
                                    <p class="col-6 mb-0"><em>Local time is: <span id="localTime"></span></em></p>
                                    <p><em>Choose a city in the same timezone as you.</em></p>
                                    <p id="timezoneStatus" class="col-12 mb-0">This timezone is currently in <span id="currentTimeZoneLabel">standard time</span>.</p>
                                    <p id="daylightSaving" class="col-12">Daylight saving time begins on: <span id=""></span></p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveGeneralSettings" onclick="saveGeneralSettings(this)">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="errorModalLabel">Warning</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="errorDetails"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clearNotificationsModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clearNotificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="clearNotificationsModalLabel">⚠️ Confirm Clear All Notifications</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h3>Are you sure you want to clear all notifications?</h3>
                    <p>This action is permanent and cannot be undone. You will lose all notification history.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="clearNotifications()">Clear All</button>
                </div>
            </div>
        </div>
    </div>

    <div id="beforeProcessing" class="overlay d-none">
        <div class="snippet p-3 .bg-light" data-title="dot-elastic">
            <p>Processing, please wait...</p>
            <div class="stage">
                <div class="dot-elastic"></div>
            </div>
        </div>
    </div>

    <!-- Cookie Consent Modal -->
    <div id="cookieConsentModal" style="display:none; position:fixed; bottom:0; width:100%; background-color:#f1f1f1; padding:10px; text-align:center; z-index: 999">
        <p>
            This site uses cookies 🍪 to store information for the purpose of enhancing user experience. <br> If you reject cookies, you may experience limitations with functionality.
        </p>
        <button onclick="acceptCookies()">Accept</button>
        <button onclick="rejectCookies()">Reject</button>
    </div>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> -->
    <script src="/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.contextMenu.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/2.7.1/jquery.ui.position.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="<?= base_url('js') ?>/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="/js/moment-timezone-with-data.min.js"></script>
    <script src="/js/functions.js"></script>
    <script type="text/javascript">
    let apiURL = "<?= base_url('api') ?>";

    var defaultTimezone = '<?= user_timezone() ?>';
    $(document).ready(function() {
        const timezoneSelect = $('#timezone');
        timezoneSelect.val(defaultTimezone);
        updateTime(defaultTimezone);

        let currentYear = new Date().getFullYear();
        let dstStart = getSecondSundayOfMarch(currentYear, defaultTimezone);
        const formattedDate = formatDateToTimeZone(dstStart, defaultTimezone);
        $('#timezoneStatus').text(`This timezone is currently in ${defaultTimezone}.`);
        $('#daylightSaving').text(`Daylight saving time begins on: ${formattedDate}.`);

        <?php if (session()->getTempdata('welcome_message')) : ?>
        appendNotification('<?= session()->getTempdata('welcome_message') ?>', 'success');
        <?php session()->remove('welcome_message') ?>
        <?php endif; ?>

        const settingsModal = document.getElementById('settingsModal');
        if (settingsModal) {
            settingsModal.addEventListener('show.bs.modal', event => {
                const selectedTimezone = $('#timezone').val();
                updateTime(selectedTimezone);
            })
        }

        const themeClass = document.getElementsByTagName('body')[0].classList
        if (themeClass && themeClass[0] != 'bg-light') {
            document.querySelectorAll('.menu-icon').forEach((element => {
                element.onload = function() {
                    const svgDoc = element.contentDocument; // Access the SVG document
                    const paths = svgDoc.querySelectorAll('path, line, polyline, polygon, rect, circle, ellipse'); // Select all relevant elements
                    paths.forEach(path => {
                        path.setAttribute('fill', 'white'); // Change the stroke color
                        path.setAttribute('stroke', 'white'); // Change the stroke color
                    });
                }
            }))
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
    })

    // Show the modal if cookie consent is not given
    if (!localStorage.getItem('cookie_consent')) {
        document.getElementById('cookieConsentModal').style.display = 'block';
    }
    </script>
    <?= $this->renderSection('pageScripts') ?>
</body>

</html>