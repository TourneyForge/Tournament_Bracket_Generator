<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
    }

    public function viewContact()
    {
        return view('contact-us');
    }

    public function viewTerms()
    {
        return view('terms');
    }
}