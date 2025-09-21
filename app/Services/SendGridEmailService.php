<?php
namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;
use CodeIgniter\Email\Email;

class SendGridEmailService extends Email
{
    protected $apiKey;
    protected $email;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = getenv('SENDGRID_API_KEY');
        $this->email = new Mail();
    }

    public function setFrom($from, $name = '', $returnPath = null)
    {
        $this->email->setFrom($from, $name);

        return $this;
    }

    public function setTo($to)
    {
        $this->email->addTo($to);

        return $this;
    }

    public function setSubject($subject)
    {
        $this->email->setSubject($subject);

        return $this;
    }

    public function setMessage($body)
    {
        $this->email->addContent("text/html", $body);

        return $this;
    }

    public function send($autoClear = true)
    {
        $sendgrid = new SendGrid($this->apiKey);
        
        try {
            $response = $sendgrid->send($this->email);
            return $response->statusCode() == 202 ? 'Email sent successfully!' : 'Failed to send email';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}