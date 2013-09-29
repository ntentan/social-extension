<?php
namespace ntentan\plugins\social\components\signin\services;

use ntentan\plugins\social\components\signin\SigninService;
use ntentan\Ntentan;

class Google extends SigninService
{
    public function signin()
    {
        require_once "vendor/google-api-php-client/src/Google_Client.php";
        require_once "vendor/google-api-php-client/src/contrib/Google_PlusService.php";
        
        $client = new \Google_Client();
        $client->setClientId(Ntentan::$config['social.google.client_id']);
        $client->setClientSecret(Ntentan::$config['social.google.client_secret']);
        $client->setRedirectUri(Ntentan::$config['social.google.redirect_uri']);
        //$client->setDeveloperKey('insert_your_developer_key');
        
        $plus = new \Google_PlusService($client);
        
        if (isset($_REQUEST['logout'])) {
            unset($_SESSION['access_token']);
        }

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);
            $_SESSION['access_token'] = $client->getAccessToken();
            header('Location: ' . Ntentan::getUrl(Ntentan::$route));
        }

        if (isset($_SESSION['access_token'])) {
            $client->setAccessToken($_SESSION['access_token']);
        }

        if ($client->getAccessToken()) {
            $me = $plus->people->get('me');
            $_SESSION['access_token'] = $client->getAccessToken();
            
            
            
            return true;
        }
        else
        {
            header("Location: {$client->createAuthUrl()}");
            die();
        }
    }
    
    public function getProvider()
    {
        return 'google';
    }
}
