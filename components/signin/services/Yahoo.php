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
            $_SESSION['yahoo_oauth_request_token'] = $requestToken->to_string();
            $oauthapp->token = $oauthapp->getAccessToken($requestToken);
            var_dump($oauthapp->token->to_string());
            $_SESSION['yahoo_oauth_access_token'] = $oauthapp->token->to_string();
        }
        
        $profile = $oauthapp->getProfile();
        
        
        
    }
}
