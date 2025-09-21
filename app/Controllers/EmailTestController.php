<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class EmailTestController extends BaseController
{
    public function index()
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setSubject("Test Email with sendgrid api");
        $email->addTo("elemental3mperor@gmail.com");
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
    }
}