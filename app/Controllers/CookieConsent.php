<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class CookieConsent extends BaseController
{
    public function index()
    {
        //
    }

    public function consent()
    {
        $response = service('response');
        $consent = $this->request->getPost('consent');

        if ($consent === 'accept') {
            $response->setCookie('cookie_consent', 'accepted', YEAR_IN_SECONDS);
        } elseif ($consent === 'reject') {
            $response->setCookie('cookie_consent', 'rejected', YEAR_IN_SECONDS);
        }

        return redirect()->back();
    }
}