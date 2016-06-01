<?php
namespace ntentan\extensions\social\components\signin;

use ntentan\Ntentan;

class GoogleSignin extends AbstractSignin
{
    public function signin()
    {
        $client = new \Google_Client();
        $client->setClientId(Ntentan::$config['social.google.client_id']);
        $client->setClientSecret(Ntentan::$config['social.google.client_secret']);
        $client->setRedirectUri(Ntentan::$config['social.google.redirect_uri']);
        $client->addScope(array('profile', 'email'));
        $oauth2 = new \Google_Service_Oauth2($client);
        
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
        
        if($client->isAccessTokenExpired()) {

            $authUrl = $client->createAuthUrl();
            header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));

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
