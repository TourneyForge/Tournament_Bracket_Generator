<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\NotificationService;
use App\Services\SendGridEmailService;
use App\Services\UserSettingsService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
      /*
      * public static function example($getShared = true)
      * {
      *     if ($getShared) {
      *         return static::getSharedInstance('example');
      *     }
      *
      *     return new \CodeIgniter\Example();
      * }
      */

      public static function userSettings(): UserSettingsService
      {
         return new UserSettingsService();
      }

      public static function notification(): NotificationService
      {
        return new NotificationService();
      }

      public static function email($config = null, $getShared = true)
      {
         if (setting('Email.protocol') == 'sendgrid') {
            $getShared = false;
         }
         
         if ($getShared) {
            return static::getSharedInstance('email');
         }

         return new SendGridEmailService();
      }
}