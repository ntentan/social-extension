<?php
namespace ntentan\extensions\social\components\signin\services;

use ntentan\extensions\social\components\signin\SigninService;
use ntentan\Ntentan;

class Google extends SigninService
{
    public function signin()
    {
        $client = new \Google_Client();
        $client->setClientId(Ntentan::$config['social.google.client_id']);
        $client->setClientSecret(Ntentan::$config['social.google.client_secret']);
        $client->setRedirectUri(Ntentan::$config['social.google.redirect_uri']);
        
        
        if (isset($_REQUEST['logout'])) 
        {
            unset($_SESSION['access_token']);
            $client->revokeToken();
        }

        if (isset($_GET['code'])) 
        {
            $client->authenticate($_GET['code']);
            $_SESSION['access_token'] = $client->getAccessToken();
            header('Location: ' . Ntentan::getUrl(Ntentan::$route));
        }

        if (isset($_SESSION['access_token'])) 
        {
            $client->setAccessToken($_SESSION['access_token']);
        }

        if ($client->getAccessToken()) 
        {
            $user = $oauth2->userinfo->get();
            $_SESSION['token'] = $client->getAccessToken();
            
            return array(
                'firstname' => $user['given_name'],
                'lastname' => $user['family_name'],
                'key' => "google_{$user['id']}",
                'avatar' => $user['picture'],
                'email' => $user['email'],
                'email_confirmed' => $user['verified_email']
            );                            
        }
        else
        {
            header("Location: {$client->createAuthUrl()}");
            die();
        }
        
        return false;
    }
    
    public function getProvider()
    {
        return 'google';
    }
}
