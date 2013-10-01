<?php
namespace ntentan\plugins\social\components\signin\services;

use ntentan\Ntentan;
use ntentan\plugins\social\components\signin\SigninService;

class Facebook extends SigninService
{
    public function signin()
    {
        require "vendor/facebook-php-sdk/src/facebook.php";
        $facebook = new \Facebook(
            array(
                'appId' => Ntentan::$config['social.facebook.app_id'],
                'secret' => Ntentan::$config['social.facebook.secret']
            )
        );
        $user = $facebook->getUser();

        if($user == 0)
        {
            header("Location: " . $facebook->getLoginUrl(
                    array(
                        'scope' => array('email')
                    )
                )
            );
            die();
        }
        else
        {
            $profile = $facebook->api('/me');
            
            return array(
                'firstname' => $profile['first_name'],
                'lastname' => $profile['last_name'],
                'key' => "facebook_{$profile['id']}",
                'avatar' => "http://graph.facebook.com/{$profile['username']}/picture",
                'email' => $profile['email'],
                'email_confirmed' => $profile['verified'],
                'avatar_format' => 'jpg'
            );   
        }
        return 'failed';
    }
    
    public function getProvider()
    {
        return 'facebook';
    }
}
