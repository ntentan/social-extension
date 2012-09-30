<?php
namespace ntentan\plugins\social\components\signin\services;

use ntentan\plugins\social\components\signin\SigninService;
use ntentan\Ntentan;

class Yahoo extends SigninService
{
    public function __construct()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    
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
        require "vendor/yahoo-yos-social-php5/lib/OAuth/OAuth.php";
        require "vendor/yahoo-yos-social-php5/lib/Yahoo/YahooOAuthApplication.class.php";
        require "vendor/http/class.http.php";
        
        $oauthapp = new \YahooOAuthApplication(
            Ntentan::$config['ntentan_social']['yahoo']['consumer_key'],
            Ntentan::$config['ntentan_social']['yahoo']['consumer_secret'],
            Ntentan::$config['ntentan_social']['yahoo']['app_id'],
            'http://owarega.me/users/get_profile'
        );
        
        if(!isset($_REQUEST['openid_mode']))
        {
            Ntentan::redirect($oauthapp->getOpenIDUrl($oauthapp->callback_url), true);
            die();
        }
        
        if($_REQUEST['openid_mode'] == 'id_res')
        {
            $requestToken = new \YahooOAuthRequestToken($_REQUEST['openid_oauth_request_token'],'');
            $providerData['request_token'] = $requestToken->to_string();
            $oauthapp->token = $oauthapp->getAccessToken($requestToken);
            $providerData['access_token'] = $oauthapp->token->to_string();
        }
        
        $profile = $oauthapp->getProfile()->profile;
        if(is_object($profile))
        {
            foreach($profile->emails as $email)
            {
                if($email->primary == true)
                {
                    $profileData['third_party_profile']['email'] = $email->handle;
                    break;
                }
            }
            $profileData['third_party_profile']['firstname'] = $profile->givenName;
            $profileData['third_party_profile']['lastname'] = $profile->familyName;
            $extension = end(explode('.', $profile->image->imageUrl));
            $profileData['third_party_profile']['avatar'] = "tmp/" . uniqid() . ".$extension";
            $profileData['provider_data'] = $providerData;
            
            $http = new \Http();
            @$http->execute($profile->image->imageUrl);
            file_put_contents($profileData['third_party_profile']['avatar'], $http->result);
            return $profileData;
        }
        else
        {
            return false;
        }
    }
}
