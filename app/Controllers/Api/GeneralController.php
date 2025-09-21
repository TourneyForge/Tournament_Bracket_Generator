<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\UploadConfig;

class GeneralController extends BaseController
{
    protected $userSettingsModel;
    public function __construct() {
        $this->userSettingsModel = model('App\Models\UserSettingModel');
    }

    public function index()
    {
        $settings = $this->userSettingsModel->where('user_id', auth()->user()->id)->findAll();
        return $this->response->setJson($settings);
    }

    public function uploadImage()
    {
        $uploadConfig = new UploadConfig();
        
        $file = $this->request->getFile('image');
        if($file){
            $filepath = '';
            if (! $file->hasMoved()) {
                if ($this->request->getPost('type') == 'group') {
                    $filepath = '/uploads/' . $file->store($uploadConfig->groupImagesUploadPath);
                }
            }

            return $this->response->setJson(['status' => 'success', 'file_path' => $filepath]);
        }
        
        return $this->response->setJson(['status' => 'error', 'msg' => 'Setting not found']);
    }

    public function removeImage()
    {
        
    }
}