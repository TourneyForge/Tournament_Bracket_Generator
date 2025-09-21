<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UserSettingsController extends BaseController
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

    public function show($id = null)
    {
        $setting = $this->userSettingsModel->find($id);
        if (!$setting) {
            return $this->response->setJson(['status' => 'error', 'msg' => 'Setting not found']);
        }
        return $this->response->setJson($setting);
    }

    public function save($id = null)
    {
        $data = $this->request->getRawInput();
        
        $db = \Config\Database::connect();
        $dbDriver = $db->DBDriver;
        if (!auth()->user() && $dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        }

        if (!auth()->user() && $dbDriver === 'SQLite3') {
            $db->query('PRAGMA foreign_keys = OFF');
        }

        $user_id = auth()->user() ? auth()->user()->id : 0;

        foreach ($data as $key => $value) {
            if (auth()->user()) {
                $setting = $this->userSettingsModel->where(['user_id' => $user_id, 'setting_name' => $key])->first();
            } else {
                $values = explode('_', $value);
                $uuid = $values[1];
                $value = $values[0];
                $setting = $this->userSettingsModel->where(['uuid' => $uuid, 'setting_name' => $key])->first();
            }
            
            if ($setting) {
                $setting['setting_value'] = $value;
            } else {
                $setting = new \App\Entities\UserSetting($data);
                $setting->user_id = $user_id;
                $setting->setting_name = $key;
                $setting->setting_value = $value;

                if (isset($uuid)) {
                    $setting->uuid = $uuid;
                }
            }
            
            if (!$this->userSettingsModel->save($setting)) {
                return $this->response->setJson(['status' => 'error', 'msg' => 'Failed to save the setting']);
            }
        }
        
        if (!auth()->user() && $dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        }

        if (!auth()->user() && $dbDriver === 'SQLite3') {
            $db->query('PRAGMA foreign_keys = ON');
        }

        return $this->response->setJson(['status' => 'success', 'setting' => $setting, 'message' => 'Settings updated successfully!']);
    }

    public function delete($id = null)
    {
        if ($this->userSettingsModel->delete($id)) {
            return $this->response->setJson(['status' => 'success']);
        }
        return $this->response->setJson(['status' => 'error', 'msg' => 'Setting not found']);
    }
}