<?php

namespace App\Services;

use App\Models\UserSettingModel;

class UserSettingsService
{
    protected $userSettingsModel;

    public function __construct()
    {
        $this->userSettingsModel = new UserSettingModel();
    }

    /**
     * Add a new user setting.
     *
     * @param array $data user setting data
     * @return bool|int The ID of the inserted notification or false on failure
     */
    public function addSetting(array $data)
    {
        $user_id = auth()->user() ? auth()->user()->id : 0;
        if ($this->userSettingsModel->insert($data)) {
            return $this->userSettingsModel->getInsertID();
        }
        return false;
    }

    /**
     * Get user setting for a specific user.
     *
     * @param int $user_id User ID
     * @return bool | string
     */
    public function get($key, $user_id = null)
    {
        if (!$user_id) {
            if (!auth()->user()) {
                return false;
            }
            
            $user_id = auth()->user()->id;
        }

        $setting = $this->userSettingsModel->where(['setting_name' => $key, 'user_id' => $user_id])->first();
        
        if ($setting) {
            return $setting['setting_value'];
        }
        
        return false;
    }
}