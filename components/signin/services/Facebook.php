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
                'appId' => Ntentan::$config['ntentan_social']['facebook']['app_id'],
                'secret' => Ntentan::$config['ntentan_social']['facebook']['secret']
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
        }
        else
        {
            $fbProfile = $facebook->api('/me');
            $status = array(
                'email' => $fbProfile['email'],
                'firstname' => $fbProfile['first_name'],
                'lastname' => $fbProfile['last_name'],
                'nickname' => $fbProfile['username'],
                'key' => $fbProfile['email']
            );
            $_SESSION['fb_profile'] = $fbProfile;
            $_SESSION['fb_profile']['user'] = $user;
            $_SESSION['fb_provider_data'] = $facebook->getAccessToken();
            return $status;
        }
        return 'failed';
    }
    
    public function getProvider()
    {
        return 'facebook';
    }
    
    public function getProfile()
    {
        $profileData['third_party_profile']['email'] = $_SESSION['fb_profile']['email'];
        $profileData['third_party_profile']['firstname'] = $_SESSION['fb_profile']['first_name'];
        $profileData['third_party_profile']['lastname'] = $_SESSION['fb_profile']['last_name'];
        $profileData['third_party_profile']['othernames'] = $_SESSION['fb_profile']['other_names'];
        $profileData['third_party_profile']['avatar'] = "tmp/" . uniqid() . ".jpg";
        $profileData['third_party_profile']['username'] = $_SESSION['fb_profile']['username'];
        $profileData['provider_data'] = $_SESSION['fb_provider_data'];
        
        require "vendor/class.http.php";
        
        $http = new \Http();
        @$http->execute("http://graph.facebook.com/{$_SESSION['fb_profile']['user']}/picture");
        file_put_contents($profileData['third_party_profile']['avatar'], $http->result);
        
        return $profileData;
    }
}
