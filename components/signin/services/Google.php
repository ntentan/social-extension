<?php
namespace ntentan\plugins\social\components\signin\services;

use ntentan\plugins\social\components\signin\SigninService;

class Google extends SigninService
{
    public function signin()
    {
        return $this->doOpenId("https://www.google.com/accounts/o8/id");
    }
    
    public function getProvider()
    {
        return 'google';
    }
    
    public function getProfile()
    {
        require "vendor/google-api-php-client/src/apiClient.php";
        require "vendor/http/class.http.php";
        
        $apiClient = new \apiClient();
        
        $apiClient->setScopes(
            array(
                "https://www.googleapis.com/auth/userinfo.profile",
                "https://www.googleapis.com/auth/userinfo.email"
            )
        );
        $token = json_decode($apiClient->authenticate(), true);
        $profileData = array();
        
        if(is_array($token))
        {
        
            $profileData = $token;
        
            $http = new \Http();
            @$http->execute("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token['access_token']}");
            $profile = json_decode($http->result, true);
            $profileData['provider_data'] = $token;
        
            $profileData['third_party_profile']['email'] = $profile['email'];
            $profileData['third_party_profile']['firstname'] = $profile['given_name'];
            $profileData['third_party_profile']['lastname'] = $profile['family_name'];
            $array = explode('.', $profile['picture']);
            $extension = end($array);
            $profileData['third_party_profile']['avatar'] = "tmp/" . uniqid() . ".$extension";
            $profileData['third_party_profile']['username'] = reset(explode("@", $profile['email']));
        
            $http = new \Http();
            @$http->execute($profile['picture']);
            file_put_contents($profileData['third_party_profile']['avatar'], $http->result);
            return $profileData;
        }
        else
        {
            return false;
        }
    }
}
