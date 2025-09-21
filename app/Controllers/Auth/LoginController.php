<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Controllers\LoginController as ShieldLogin;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends ShieldLogin
{
    public function loginView()
    {
        return parent::loginView();
    }

    public function loginAction(): RedirectResponse
    {
        /* Check if the user already loggedin */
        if (auth()->user()) {
            session()->setTempdata('welcome_message', 'Welcome, ' . auth()->user()->username . '!');
            return redirect()->to('/');
        }

        $result = parent::loginAction();

        if (auth()->user()) {
            session()->setTempdata('welcome_message', 'Welcome, ' . auth()->user()->username . '!');
        }

        return $result;
    }
}