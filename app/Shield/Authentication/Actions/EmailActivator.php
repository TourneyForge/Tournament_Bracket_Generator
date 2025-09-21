<?php

declare(strict_types=1);

namespace App\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator as ShieldEmailActivator;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use RuntimeException;

class EmailActivator extends ShieldEmailActivator
{    
    private string $type = Session::ID_TYPE_EMAIL_ACTIVATE;
    
    /**
     * Overrides the verify method to send a welcome email after activation.
     */
    public function verify(IncomingRequest $request)
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $postedToken = $request->getVar('token');

        $user = $authenticator->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        $identity = $this->getIdentity($user);

        // No match - let them try again.
        if (! $authenticator->checkAction($identity, $postedToken)) {
            session()->setFlashdata('error', lang('Auth.invalidActivateToken'));

            return $this->view(setting('Auth.views')['action_email_activate_show'], ['user' => $user]);
        }

        // Get authenticated user
        $user = $authenticator->getUser();

        // Set the user active now
        $user->activate();
        
        // Send welcome email
        $this->sendWelcomeEmail($user);
        
        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', lang('Auth.registerSuccess'));
    }

    /**
     * Sends a welcome email to the user after successful activation.
     */
    private function sendWelcomeEmail(User $user): void
    {
        helper('email');
        $email = emailer(['mailType' => 'html'])
            ->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->email)
            ->setSubject(lang('Auth.welcomeEmailSubject', [setting('Email.fromName')]))
            ->setMessage(
                $this->view(
                    'email/welcome_email',  // Custom email view
                    ['username' => $user->username]
                )
            );

        if (!$email->send(false)) {
            log_message('error', 'Failed to send welcome email to ' . $user->email . "\n" . $email->printDebugger(['headers']));
        }

        // Clear the email
        $email->clear();
    }
    
    /**
     * Returns an identity for the action of the user.
     */
    private function getIdentity(User $user): ?UserIdentity
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getIdentityByType(
            $user,
            $this->type
        );
    }
}