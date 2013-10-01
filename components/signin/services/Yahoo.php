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
        require "vendor/yahoo-yos-social-php5/lib/OAuth/OAuth.php";
        require "vendor/yahoo-yos-social-php5/lib/Yahoo/YahooOAuthApplication.class.php";
        require "vendor/http/class.http.php";
        
        $oauthapp = new \YahooOAuthApplication(
            Ntentan::$config['social.yahoo.consumer_key'],
            Ntentan::$config['social.yahoo.consumer_secret'],
            Ntentan::$config['social.yahoo.app_id'],
            Ntentan::$config['social.yahoo.redirect_uri']
        );

           if(!isset($_REQUEST['openid_mode']))
        {
            Ntentan::redirect($oauthapp->getOpenIDUrl($oauthapp->callback_url), true);
            die();
        }
        
        if($_REQUEST['openid_mode'] == 'id_res')
        {
            $requestToken = new \YahooOAuthRequestToken($_REQUEST['openid_oauth_request_token'],'');
            $_SESSION['yahoo_oauth_request_token'] = $requestToken->to_string();
            $oauthapp->token = $oauthapp->getAccessToken($requestToken);
            $_SESSION['yahoo_oauth_access_token'] = $oauthapp->token->to_string();
        }
        
        $profile = $oauthapp->getProfile()->profile;        
        if(is_object($profile))
        {
            foreach($profile->emails as $email)
            {
                if($email->primary == 'true')
                {
                    $email = $email->handle;
                    break;
                }
            }
            
            return array(
                'firstname' => $profile->givenName,
                'lastname' => $profile->familyName,
                'key' => "yahoo_{$profile->guid}",
                'avatar' => $profile->image->imageUrl,
                'email' => $email,
                'email_confirmed' => true
            );            
        }
        
        die();
    }
    
    public function getProvider()
    {
        return 'yahoo';
    }    
}
