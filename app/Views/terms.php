<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Terms of Service & Privacy Policy<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid align-middle p-5">
    <div class="row">
        <div class="col-12">
            <h3 class="text-center">Terms of Service & Privacy Policy</h3>
            <p class="text-center">Last Updated: 2025-03-12</p>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <p>Welcome to <strong>TourneyForge</strong>!</p>
            <p>These Terms of Service ("Terms") govern your access to and use of <strong>TourneyForge</strong> ("the Platform"), including all features, services, and content available through our website. <br />
                By using the Platform, you <strong>agree to these Terms</strong> — so please read them carefully.</p>

            <p><strong>1. Acceptance of Terms</strong></p>
            <p class="mb-1 ps-2">By accessing or using the Platform, you confirm that you have read, understood, and agree to these Terms. If you do not agree, please do not use the platform/site.</p>

            <p class="mt-3"><strong>2. Eligibility</strong></p>
            <p class="mb-1 ps-2">✔ You must be at least 13 years old (or the minimum age required by your jurisdiction) to use this platform/site.</p>
            <p class="mb-1 ps-2">✔ If you are using the Platform on behalf of an organization, you confirm that you have the authority to bind the organization to these Terms.</p>

            <p class="mt-3"><strong>3. Creating and Managing Tournaments</strong></p>
            <p class="mb-1 ps-2">✔ You can create and manage tournaments for free, with or without signing up.</p>
            <p class="mb-1 ps-2">✔ Guest-created tournaments will be <strong>automatically deleted after 24 hours</strong> unless linked to a registered account.</p>
            <p class="mb-1 ps-2">✔ As a tournament creator, you are responsible for managing participants, ensuring fairness, and maintaining compliance with these Terms.</p>

            <p class="mt-3"><strong>4. User Accounts and Responsibilities</strong></p>
            <p class="mb-1 ps-2">✔ Signing up grants access to the <a href="<?= base_url('tournaments')?>">Tournament Dashboard</a>, which enables advanced tournament management.</p>
            <p class="mb-1 ps-2">✔ You are responsible for maintaining the security of your account and not sharing your login credentials.</p>
            <p class="mb-1 ps-2">✔ Any actions taken from your account will be considered as your own. If you believe your account has been compromised, contact us immediately.</p>

            <p class="mt-3"><strong>5. Content and Conduct Guidelines</strong></p>
            <p>To keep TourneyForge a safe and enjoyable platform, you agree not to:</p>
            <p class="mb-1 ps-2">❌ Post, share, or upload <strong>offensive, abusive, or illegal content</strong>.</p>
            <p class="mb-1 ps-2">❌ Impersonate others or misrepresent your identity.</p>
            <p class="mb-1 ps-2">❌ Engage in harassment, discrimination, or any harmful behavior toward other users.</p>
            <p class="mb-1 ps-2">❌ Use automated systems (bots, scrapers) to disrupt or interfere with the Platform.</p>
            <p>We reserve the right to suspend or delete accounts and tournaments that violate these guidelines.</p>

            <p class="mt-3"><strong>6. Privacy and Data Usage</strong></p>
            <p class="mb-1 ps-2">✔ <strong>We value your privacy!</strong></p>
            <p class="mb-1 ps-2">By using the site, you consent to limited data collection necessary for tournament functionality, such as participant records and leaderboard rankings.</p>
            <p class="mb-1 ps-2">✔ <strong>Guest Users</strong></p>
            <p class="mb-1 ps-2">Please note that tournament data created by guest users is temporary and may not be permanently stored unless claimed by or linked to a registered account.</p>
            <p class="mb-1 ps-2">✔ <strong>Your Privacy Controls</strong></p>
            <p class="mb-1 ps-2">
                We provide tools to help you manage your privacy at any time from your Profile Settings:<br />
            <p class="mb-0 ps-3">• <strong>Reset Cookie Preferences:</strong> Revisit and modify your cookie consent choices.</p>
            <p class="mb-0 ps-3">• <strong>Email Privacy:</strong> Choose to hide your email address from tournaments and participant leaderboards.</p>
            <p class="mb-0 ps-3">• <strong>Account Deletion:</strong> You may delete your account permanently at any time.</p>
            </p>

            <p class="mt-3"><strong>7. Intellectual Property & Media Usage</strong></p>
            <p class="mb-1 ps-2">✔ TourneyForge’s software, trademarks, and design are owned by us and may not be copied, modified, or distributed without permission.</p>
            <p class="mb-1 ps-2">✔ <strong>User-Uploaded Content</strong></p>
            <p class="mb-1 ps-2">You retain ownership of any content you upload (images, audio, video) but grant TourneyForge a license to store, display, and process it for tournament functionality.</p>
            <p class="mb-1 ps-2">You are responsible for ensuring you have the necessary rights or permissions to use any uploaded media.</p>
            <p class="mb-1 ps-2">If you believe content infringes copyright, contact us for a takedown request.</p>
            <p class="mb-1 ps-2">✔ <strong>YouTube & Third-Party Media</strong></p>
            <p>TourneyForge allows embedding YouTube videos and may store metadata (URLs, titles, thumbnails) for functionality, but ownership remains with the original creators.<br />Users must comply with <a href="https://www.youtube.com/static?template=terms">YouTube’s Terms of Service</a> and ensure their use of third-party media follows copyright laws.</p>

            <p class="mt-3"><strong>8. Limitation of Liability</strong></p>
            <p class="mb-1 ps-2">✔ We strive to provide a smooth and reliable service, but we do not guarantee that the site will always be available or error-free.</p>
            <p class="mb-1 ps-2">✔ We are not responsible for any loss, damages, or disputes arising from tournament outcomes, data loss, or user interactions.</p>
            <p class="mb-1 ps-2">✔ Your use of this site is <strong>at your own risk</strong>.</p>

            <p class="mt-3"><strong>9. Modifications to These Terms</strong></p>
            <p class="mb-1 ps-2">✔ We may update these Terms from time to time. If significant changes occur, we will notify users via the Platform.</p>
            <p class="mb-1 ps-2">✔ Continued use of the site after updates means <strong>you accept the revised Terms</strong>.</p>

            <p class="mt-3"><strong>10. Contact Us</strong></p>
            <p class="mb-1 ps-2">If you have any questions/concerns, feel free to contact us at <a href="mailto:info@tourneyforge.com">info@tourneyforge.com</a>.</p>
            <p class="mb-1 ps-2">Please make sure you provide as much details as possible to ensure your request is clear for us.</p>

            <br />
            <p class="text-center">By using this site, you acknowledge and agree to these Terms of Service.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>