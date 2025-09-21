<?php

namespace App\Libraries;

use WebSocket\Client;

class WebSocketClient
{
    private $client;

    public function __construct($url = "ws://localhost:8089")
    {
        $this->client = new Client($url);
    }

    public function sendMessage($message)
    {
        try {
            $this->client->send($message);
            return "Message sent: " . $message;
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}