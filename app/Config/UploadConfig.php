<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class UploadConfig extends BaseConfig
{
    public $localAudioUploadPath = 'audios/local/';
    public $urlAudioUploadPath = 'audios/url/';
    public $localVideoUploadPath = 'videos/local/';
    public $urlVideoUploadPath = 'videos/url/';
    public $descriptionImagesUploadPath = 'images/description/';
    public $participantImagesUploadPath = 'images/participants/';
    public $groupImagesUploadPath = 'images/groups/';
    public $csvUploadPath = 'CSV/UserLocal/';
    public $ffmpegPath = '/opt/homebrew/bin/';
}