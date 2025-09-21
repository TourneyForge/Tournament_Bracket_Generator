<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>About, FAQ, and Contact<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-fluid align-middle p-5">
    <div class="row mt-3">
        <h3 class="text-center">Join the TournCreator Community!</h3>
        <h5 class="text-center mb-4">We believe tournaments should be fun, easy, and accessible to everyone.</h5>
        <p class="text-center mb-5">Whether you're a casual organizer or a spectator, TournCreator equips you with everything you need to engage with exciting, well-managed competitions!</p>
    </div>

    <div class="accordion" id="accordionAboutUs">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#keyFeatures" aria-expanded="true" aria-controls="collapseOne">
                    Key Features
                </button>
            </h2>
            <div id="keyFeatures" class="accordion-collapse collapse show" data-bs-parent="#accordionAboutUs">
                <div class="accordion-body">
                    <p><strong>Flexible Tournament Formats</strong> – Choose from a variety of formats, including knockout brackets, single elimination, and double elimination.</p>
                    <p><strong>Complete Customization</strong> – Personalize every aspect of your tournament: set participant images, customize themes (from Classic to Championship Gold), and even integrate opening media (audio/video) or winner celebration sounds.</p>
                    <p><strong>Effortless Sharing & Collaboration</strong> – Share your tournament through the generated QR Code or via a direct link showcased in the <a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a>. Manage access and permissions from your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> with ease.</p>
                    <p><strong>Delegate Administration</strong> – Assign admin (edit) role to other registered users, allowing them to update the tournament description, edit round names, add or remove participants, and manage winners. Collaboration has never been smoother!</p>
                    <p><strong>Interactive Voting Options</strong> – Vote/let spectators vote for participants with flexible settings like <strong>Round Duration</strong>, <strong>Open-Ended</strong> Voting, and <strong>Max Votes Per Round</strong>.</p>
                    <p><strong>Real-Time Tracking & Engagement</strong> – Keep participants engaged with real-time updates, match progress tracking, and a dynamic <a href="<?= base_url('participants') ?>">leaderboard</a> to enhance the competitive experience.</p>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#customizationAtFingertips" aria-expanded="false" aria-controls="collapseTwo">
                    Customization at Your Fingertips
                </button>
            </h2>
            <div id="customizationAtFingertips" class="accordion-collapse collapse" data-bs-parent="#accordionAboutUs">
                <div class="accordion-body">
                    <p class="text-center"><strong>Shape your tournament the way you want it!</strong></p>
                    <div>
                        <p class="ps-2">✔ Personalize the tournament name, description, and media for a unique identity.</p>
                        <p class="ps-2">✔ Add, remove, and customize participants effortlessly.</p>
                        <p class="ps-2">✔ Enable public or private voting to determine winners.</p>
                        <p class="ps-2">✔ Monitor every tournament action with a detailed log in your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> – perfect for tracking changes when delegating admin (edit) roles.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#FAQ" aria-expanded="false" aria-controls="collapseThree">
                    Frequently Asked Questions (FAQ)
                </button>
            </h2>
            <div id="FAQ" class="accordion-collapse collapse" data-bs-parent="#accordionAboutUs">
                <div class="accordion-body">
                    <div class="accordion" id="faqItems">
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainAuth" aria-expanded="true" aria-controls="collapseThree">
                                    <strong>Why is there an authentication mechanism if tournaments can be created for free without signing up?</strong>
                                </button>
                            </h5>
                            <div id="explainAuth" class="accordion-collapse collapse p-3 show" data-bs-parent="#faqItems">
                                <p class="ps-2">Great question! While you can create and administer tournaments for free without an account, signing up unlocks the full power of your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> — giving you more control and flexibility.</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainWithAccount" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>With an account, you can:</strong>
                                </button>
                            </h5>
                            <div id="explainWithAccount" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">✔ Rename your tournament and update its status (In Progress, Completed, Abandoned).</p>
                                <p class="ps-2">✔ Archive, reset, or delete tournaments.</p>
                                <p class="ps-2">✔ Manage sharing permissions and access detailed logs.</p>
                                <p class="ps-2">✔ Customize tournament settings like Name, Description, Elimination Type, Voting Mechanism, Scoring Rules, Participant Images, Audio for Final Winner, and more!</p>
                                <p class="ps-2">✔ Registered users can be invited as official participants using the <strong>@username</strong> prefix, ensuring accurate tracking on the <a href="<?= base_url('participants') ?>">Participant Leaderboard</a>.</p>

                                <p class="ms-1">Unlike anonymous participants, registered participants retain their stats across multiple tournaments, allowing for better tracking and recognition. If an anonymous participant joins multiple tournaments with the same name, there's no way to verify if they’re the same person. <br />Signing up ensures consistency, proper verification, and priority placement on the <a href="<?= base_url('participants') ?>">Participant Leaderboard</a>!</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainGuestMode" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>Can I continue creating tournaments for free without signing up?</strong>
                                </button>
                            </h5>
                            <div id="explainGuestMode" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">Absolutely! You can create and manage tournaments as a guest with <strong>no restrictions</strong> — except for one:</p>
                                <p class="ps-2">To prevent spam, guest-created tournaments are <strong>automatically deleted after 24 hours</strong>.</p>
                                <p class="ps-2">If you’d like to keep your tournament beyond this period, simply <a href="<?= base_url('register') ?>">Sign up</a> or <a href="<?= base_url('login') ?>">Log in</a> to claim and preserve it!</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainReservation" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>What happens if I leave my browser session before signing up/signing in?</strong>
                                </button>
                            </h5>
                            <div id="explainReservation" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">Unfortunately, guest tournaments are tied to the current browser session. If you close the tab and youre using your browser as something like incognito/private mode, your tournament will be unclaimed and automatically <strong>deleted after 24 hours</strong>!</p>
                                <p class="ps-2">If you want to retain full access, we recommend signing up/signing in before leaving your session. That way, your tournament is linked to your account and won’t be lost!</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainContact" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>How can I contact a tournament organizer/host or a registered participant on the leaderboard?</strong>
                                </button>
                            </h5>
                            <div id="explainContact" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">There are information modals/popups available from a tournament title/name and participant bracket boxes (for registered participants) displaying the email addresses, as well as by hovering on the participant name in the leaderboard table.</p>
                                <p class="ps-2">If a tournament is hosted by a guest, or a participant is a nonregistered user, no email address is associated/available.</p>
                                <p class="ps-2">Do note that if a tournament organizer/host or a registered participant has chosen to hide their email address (for privacy reasons) from their <strong>profile settings</strong>, their email address will not be displayed in the tournament, so unfortunately you will not be able to contact the organizer/host or verified participant in such scenario.</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainActions" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>As a tournament organizer/host, or someone whose been delegated admin (edit) permissions, how can I administer the participants in the tournament, such as <strong>Marking participants as winners</strong> manually, <strong>Adding participants</strong>, <strong>Changing participants</strong>, <strong>Removing participants</strong>, and <strong>Deleting brackets</strong>?</strong>
                                </button>
                            </h5>
                            <div id="explainActions" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">You can simply right-click (or hold on mobile/tablet) the participant bracket box/modal and several actions will be available to you to administer the participants/brackets!</p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainAllowInvitation" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>Is there a way to decline Tournament organizer participant invitations automatically?</strong>
                                </button>
                            </h5>
                            <div id="explainAllowInvitation" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">Of course! To opt out of tournament invitations, you can adjust the "Allow Invitations" option from your <strong>Profile Settings</strong>. </p>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h5 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#explainCustomScoring" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>Can participants report their own scores, bypassing the score settings set in the Tournament properties?</strong>
                                </button>
                            </h5>
                            <div id="explainCustomScoring" class="accordion-collapse collapse p-3" data-bs-parent="#faqItems">
                                <p class="ps-2">Participants cannot report their own scores at the moment to prevent overranking and abuse in the Participant Leaderboard statistics.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3 class="text-center">Need Help? We're Here for You!</h3>
            <p><strong>Have questions or need assistance?</strong></p>
            <p>Contact us at <a href="mailto:contact@tourncreator.com">contact@tourncreator.com</a>, and we’ll be happy to help!</p>
            <p>Please make sure you provide as much details as possible to ensure your request is clear for us.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>