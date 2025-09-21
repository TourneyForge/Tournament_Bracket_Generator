<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ParticipantsController extends BaseController
{
    public function index()
    {
        $navActive = ($this->request->getGet('filter')) ? $this->request->getGet('filter') :'all';
        $searchString = $this->request->getGet('query');

        return view('participants-list', ['navActive' => $navActive]);
    }
}