<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use App\Libraries\SendGridEmail;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use App\Libraries\TournamentLibrary;
use CodeIgniter\HTTP\RedirectResponse;

class ProfileController extends BaseController
{
    protected $session;

    public function __construct()
    {
        $this->session = service('session');
    }

    public function index() {
        $userModel = new UserModel();
        $user = $userModel->find(auth()->user()->id);

        return view('profile/user_profile', ['userInfo' => $user]);
    }

    public function changeEmail()
    {
        return view('profile/change_email');
    }

    public function sendVerification()
    {
        $validation = service('validation');

        // Validate input
        $validation->setRules([
            'new_email' => [
            'label' => 'New Email', // Custom label for error messages
            'rules' => 'required|valid_email|is_unique[auth_identities.secret]',
            'errors' => [
                'required'    => 'The {field} field is required.',
                'valid_email' => lang('Auth.invalidEmailFormat'),
                'is_unique'   => lang('Auth.errorUpdateEmailTaken', [$this->request->getPost('new_email')]),
            ],
        ],
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON(['status' => 'failed', 'errors' => $validation->getErrors()]);
        }
        
        $userId = auth()->user()->id; // Assuming user is logged in
        $new_email = $this->request->getPost('new_email');

        $emailVerificationModel = new \App\Models\EmailVerificationModel();
        $code = $emailVerificationModel->generateCode($userId, $new_email);

        // Send email
        $email = service('email');
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
        $email->setTo($new_email);
        $email->setSubject(lang('Auth.emailUpdateVerificationSubject'));
        $email->setMessage(view(
        setting('Auth.views')['new-email-verification'],
        ['username' => auth()->user()->username, 'sendername' => getenv('Email.fromName'), 'code' => $code, 'newEmail' => $new_email],
        ['debug' => false]
        ));
        
        if ($email->send(false) === false) {
            throw new RuntimeException('Cannot send email for user: ' . $new_email . "\n" . $email->printDebugger(['headers']));
        }

        return $this->response->setJSON(['status' => 'success', 'success' => true, 'message' => lang('Auth.newVerificationCodeSentMessage', [$new_email])]);
    }

    public function updateEmailConfirm()
    {
        $userId = auth()->user()->id;
        $code = $this->request->getPost('confirm_code');

        $emailVerificationModel = new \App\Models\EmailVerificationModel();
        $verification = $emailVerificationModel->where('user_id', $userId)
            ->where('verification_code', $code)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if ($verification) {
            // Get current user
            $user = auth()->user();

            // Update email
            $newEmail = $this->request->getPost('new_email');
            $oldEmail = $user->email;
            $user->email = $newEmail;
            $user->email_verified_at = null; // Mark email as unverified
            $userModel = new UserModel();
            $userModel->save($user);

            // Send verification email
            auth()->sendVerificationEmail($user);

            // Send email
            $email = service('email');
            $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
            $email->setTo($oldEmail);
            $email->setSubject(lang('Auth.emailUpdateNotificationSubject', [getenv('Email.fromName')]));
            $email->setMessage(view(
            setting('Auth.views')['old-email-notification'],
            ['username' => auth()->user()->username, 'sendername' => getenv('Email.fromName'), 'oldEmail' => $oldEmail, 'newEmail' => $newEmail],
            ['debug' => false]
            ));
            
            if ($email->send(false) === false) {
                throw new RuntimeException('Cannot send email for user: ' . $oldEmail . "\n" . $email->printDebugger(['headers']));
            }

            $emailVerificationModel->where('user_id', $userId)->delete(); // Cleanup

            return $this->response->setJSON(['success' => true, 'message' => 'Email updated. Please verify the new email address.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => lang('Auth.invalidActivateToken')]);
        }
    }

    public function changePassword()
    {
        return view('profile/change_password');
    }

    public function updatePassword()
    {
        if ($this->request->isAJAX()) {
            $newPassword = $this->request->getPost('password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Validate the form input
            $validation = \Config\Services::validation();
            // $validation->setRules([
            //     'new_password' => 'required|min_length[8]',
            //     'confirm_password' => 'required|matches[new_password]'
            // ]);
            $validation->setRules(config('Validation')->passwordReset);

            if (!$validation->withRequest($this->request)->run()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode('<br>', $validation->getErrors())
                ]);
            }

            $auth = service('auth');
            $user = $auth->user();
            
            // Verify the current password
            // if (!$auth->check(['email' => $user->email, 'password' => $currentPassword])->isOK()) {
            //     return $this->response->setJSON([
            //         'success' => false,
            //         'message' => 'Current password is incorrect.'
            //     ]);
            // }

            // Update the password
            $user->setPassword($newPassword);

            $userModel = new UserModel();
            $userModel->save($user);

            // Send email
            $email = service('email');
            $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
            $email->setTo($user->email);
            $email->setSubject(lang('Auth.passwordChangeEmailSubject'));
            $email->setMessage(view(
            setting('Auth.views')['password-changed-email'],
            ['username' => auth()->user()->username, 'sendername' => getenv('Email.fromName')],
            ['debug' => false]
            ));
            if ($email->send(false) === false) {
                throw new RuntimeException('Cannot send email for user: ' . $user->email . "\n" . $email->printDebugger(['headers']));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Password successfully updated.'
            ]);
        }
    }

    public function deleteUser(): RedirectResponse {
        $user_email = auth()->user()->email;
        $username = auth()->user()->username;
        /**
         * @var mixed
         * Delete all the tournaments by the user
         */
        $tournamentLibrary = new TournamentLibrary();
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournaments = $tournamentModel->where('user_id', auth()->user()->id)->findAll();
        if ($tournaments) {
            foreach ($tournaments as $tournament) {
                $tournamentLibrary->deleteTournament($tournament['id']);
            }
        }
        
        /** Delete the user settings */
        $userSettingsModel = model('\App\Models\UserSettingModel');
        $userSettingsModel->where('user_id', auth()->user()->id)->delete();

        $users = auth()->getProvider();

        $users->delete(auth()->user()->id, true);

        /** Send the email */
        $email = service('email');
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
        $email->setTo($user_email);
        $email->setSubject(lang('Profile.closeAccountEmailSubject', [getenv('Email.fromName')]));
        $email->setMessage(view(
        'email/close-account-email',
        ['username' => $username, 'sendername' => getenv('Email.fromName')],
        ['debug' => false]
        ));
        if ($email->send(false) === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user_email . "\n" . $email->printDebugger(['headers']));
        }

        // Success!
        return redirect()->to('logout')->withInput()->with('message', 'You have closed your account successfully!');
    }
}