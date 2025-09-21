<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('contact', 'Home::viewContact');
$routes->get('terms', 'Home::viewTerms');

$routes->get('profile', 'ProfileController::index');
$routes->get('profile/change-email', 'ProfileController::changeEmail', ['as' => 'profile.change-email']);
$routes->post('profile/update-email', 'ProfileController::sendVerification');
$routes->post('profile/update-email-confirm', 'ProfileController::updateEmailConfirm');
$routes->get('profile/change-password', 'ProfileController::changePassword', ['as' => 'profile.change-password']);
$routes->post('profile/update-password', 'ProfileController::updatePassword');
$routes->get('close-account', 'ProfileController::deleteUser');

$routes->get('/gallery', 'TournamentController::index');
$routes->get('gallery/(:num)/view', 'TournamentController::view/$1');
$routes->get('gallery/export', 'TournamentController::exportGallery');

$routes->get('fix-issues', 'FixIssueController::index');

$routes->group('tournaments', static function ($routes) {
    $routes->get('/', 'TournamentController::index');
    $routes->get('create', 'TournamentController::create');
    $routes->get('(:num)/view', 'TournamentController::view/$1');
    $routes->get('shared/(:segment)', 'TournamentController::viewShared/$1');
    $routes->get('export', 'TournamentController::export');
    $routes->get('export-logs', 'Api\TournamentController::exportLogs');
    $routes->get('apply', 'TournamentController::apply');
    $routes->post('save-apply', 'TournamentController::saveApply');
});
$routes->group('participants', static function ($routes) {
    $routes->get('/', 'ParticipantsController::index');
    $routes->get('export', 'Api\ParticipantsController::export');
});

$routes->post('consent', 'CookieConsent::consent');

$routes->group('api', static function ($routes) {
    $routes->group('brackets', static function ($routes) {
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
        $routes->post('generate', 'Api\BracketsController::generateBrackets');
        $routes->post('switch', 'Api\BracketsController::switchBrackets');
        $routes->post('save-round', 'Api\BracketsController::saveRoundSettings');
    });

    $routes->group('participants', static function ($routes) {
        $routes->post('get-leaderboard', 'Api\ParticipantsController::getParticipants');
        $routes->post('get-analysis', 'Api\ParticipantsController::getAnalysis');
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('update/(:num)', 'Api\ParticipantsController::updateParticipant/$1');
        $routes->post('delete/(:num)', 'Api\ParticipantsController::deleteParticipant/$1');
        $routes->post('import', 'Api\ParticipantsController::importParticipants');
        $routes->post('clear', 'Api\ParticipantsController::clearParticipants');
        $routes->post('deletes', 'Api\ParticipantsController::deleteParticipants');
    });

    $routes->group('tournaments', static function ($routes) {
        $routes->post('save', 'Api\TournamentController::save');
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->post('upload', 'Api\TournamentController::upload');
        $routes->post('upload-video', 'Api\TournamentController::uploadVideo');
        $routes->get('(:num)/fetch-settings', 'Api\TournamentController::getSettings/$1');
        $routes->get('(:num)/clear', 'Api\BracketsController::clearBrackets/$1');
        $routes->get('(:num)/delete', 'Api\TournamentController::delete/$1');
        $routes->post('(:num)/share', 'Api\TournamentController::share/$1');
        $routes->get('(:num)/share', 'Api\TournamentController::fetchShareSettings/$1');
        $routes->get('(:num)/generateShareUrl', 'Api\TournamentController::generateShareToken/$1');
        $routes->get('purge-share/(:num)', 'Api\TournamentController::purgechShareSettings/$1');
        $routes->get('(:num)/getActionHistory', 'Api\TournamentController::getActionHistory/$1');
        $routes->get('fetchUsersList', 'Api\TournamentController::fetchUsersList');
        $routes->get('fetchShareSetting/(:num)', 'Api\TournamentController::fetchShareSetting/$1');
        $routes->post('bulkDelete', 'Api\TournamentController::bulkDelete');
        $routes->post('bulkReset', 'Api\TournamentController::bulkReset');
        $routes->post('bulkUpdate', 'Api\TournamentController::bulkUpdate');
        $routes->post('get-list', 'Api\TournamentController::fetch');
        $routes->post('get-gallery', 'Api\TournamentController::fetch_gallery');
        $routes->post('reuse-participants', 'Api\TournamentController::reuseParticipants');
        $routes->get('(:num)/get-participants', 'Api\TournamentController::getParticipants/$1');
        $routes->get('get-users', 'Api\TournamentController::getUsers');
        $routes->post('vote', 'Api\TournamentController::saveVote');
    });

    $routes->group('notifications', static function ($routes) {
        $routes->get('get-notifications', 'Api\NotificationsController::getAll');
        $routes->put('mark-as-read/(:num)', 'Api\NotificationsController::markAsRead/$1');
        $routes->delete('delete/(:num)', 'Api\NotificationsController::delete/$1');
        $routes->delete('clear', 'Api\NotificationsController::clear');
    });

    $routes->group('usersettings', static function ($routes) {
        $routes->post('list', 'Api\UserSettingsController::index');
        $routes->post('save', 'Api\UserSettingsController::save');
    });

    $routes->group('groups', static function ($routes) {
        $routes->get('get-list', 'Api\GroupsController::getList');
        $routes->post('save', 'Api\GroupsController::save');
        $routes->post('reset', 'Api\GroupsController::reset');
        $routes->post('delete', 'Api\GroupsController::delete');
        $routes->post('remove-participant', 'Api\GroupsController::removeParticipant');
    });

    $routes->post('upload-image', 'Api\GeneralController::uploadImage');
    $routes->post('remove-image', 'Api\GeneralController::removeImage');
});

/** API to fetch the data of shared tournaments */
$routes->group('api/shared', static function ($routes) {
    $routes->group('tournaments', static function ($routes) {
        $routes->get('(:num)/brackets', 'Api\BracketsController::getBrackets/$1');
        $routes->post('(:num)/update', 'Api\TournamentController::update/$1');
        $routes->get('(:num)/delete', 'Api\TournamentController::delete/$1');
        $routes->get('(:num)/get-participants', 'Api\TournamentController::getParticipants/$1');
        $routes->post('vote', 'Api\TournamentController::saveVote');
    });

    $routes->group('brackets', static function ($routes) {
        $routes->post('save-list', 'Api\BracketsController::createBrackets');
        $routes->put('update/(:num)', 'Api\BracketsController::updateBracket/$1');
        $routes->delete('delete/(:num)', 'Api\BracketsController::deleteBracket/$1');
        $routes->post('save-round', 'Api\BracketsController::saveRoundSettings');
    });

    $routes->group('participants', static function ($routes) {
        $routes->post('new', 'Api\ParticipantsController::addParticipant');
        $routes->post('update/(:num)', 'Api\ParticipantsController::updateParticipant/$1');
    });

});

$routes->post('login', '\App\Controllers\Auth\LoginController::loginAction');
$routes->post('login/magic-link', '\App\Controllers\Auth\CustomMagicLinkController::loginAction');

$routes->get('auth/google', 'GoogleAuthController::login');
$routes->get('auth/google/callback', 'GoogleAuthController::callback');
$routes->get('auth/resend-verification', 'Auth\RegisterController::resendVerification');
$routes->get('auth/a/abort-verification', 'Auth\RegisterController::abortVerification');
$routes->post('register', 'Auth\RegisterController::registerAction');
$routes->get('auth/verification-success', function () {
    return view('shield/verification_success');
});

/** Shield routs for authentication */
service('auth')->routes($routes);