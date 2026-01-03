<?php

use App\Models\SettingModel;

if (!function_exists('setting')) {

    function setting($key = null)
    {
        static $settings = null;

        if ($settings === null) {
            $model = new SettingModel();
            $settings = $model->find(1);
        }

        if (!$settings) {
            return null;
        }

        return $key ? ($settings->$key ?? null) : $settings;
    }
}
