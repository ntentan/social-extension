<?php
namespace ntentan\plugins\social\components\signin\services;

use ntentan\plugins\social\components\signin\SigninService;

class Yahoo extends SigninService
{
    public function signin()
    {
        return $this->doOpenId("https://yahoo.com");
    }
    
    public function getProvider()
    {
        return 'yahoo';
    }
    
    /**
     * (non-PHPdoc)
     * @see ntentan\plugins\social\components\signin.SigninService::getProfile()
     */
    public function getProfile()
    {
        require "vendor/yahoo-yos-social-php/lib/OAuth.php";
        require "vendor/yahoo-yos-social-php/lib/Yahoo.inc";
        require "vendor/class.http.php";
        
        // Your Consumer Key (API Key) goes here.
        define('CONSUMER_KEY', Ntentan::$config['ntentan_social']['yahoo']['consumer_key']);
        
        // Your Consumer Secret goes here.
        define('CONSUMER_SECRET', Ntentan::$config['ntentan_social']['yahoo']['consumer_secret']);
        
        // Your application ID goes here.
        define('APPID', Ntentan::$config['ntentan_social']['yahoo']['app_id']);
        
        $session = \YahooSession::requireSession(CONSUMER_KEY,CONSUMER_SECRET,APPID);
        
        // Fetch the profile for the current user.
        if(is_object($session))
        {
            $user = $session->getSessionedUser();
            $profile = $user->getProfile();
        
            $_SESSION['provider_data'] = json_encode(
                array(
                    "user"=>$user,
                    "token"=>$token
                )
            );
        
            foreach($profile->emails as $email)
            {
                if($email->primary == true)
                {
                    $_SESSION['third_party_profile']['email'] = $email->handle;
                    break;
                }
            }
            $_SESSION['third_party_profile']['firstname'] = $profile->givenName;
            $_SESSION['third_party_profile']['lastname'] = $profile->familyName;
            $extension = end(explode('.', $profile->image->imageUrl));
            $_SESSION['third_party_profile']['avatar'] = "tmp/" . uniqid() . ".$extension";
        
        
            $http = new \Http();
            @$http->execute($profile->image->imageUrl);
            file_put_contents($_SESSION['third_party_profile']['avatar'], $http->result);
            Ntentan::redirect("/signup");
        }
    }
}