<?php
namespace ntentan\extensions\social\components\signin;

use ntentan\Ntentan;
use ntentan\config\Config;
use ntentan\utils\Input;
use ntentan\Session;
use ntentan\controllers\Redirect;

class GoogleSignin extends AbstractSignin
{
    public function signin()
    {
        $client = new \Google_Client();
        $client->setClientId(Config::get('ntentan:social.google.client_id'));
        $client->setClientSecret(Config::get('ntentan:social.google.client_secret'));
        $client->setRedirectUri(Config::get('ntentan:social.google.redirect_uri'));
        $client->addScope(array('profile', 'email'));
        $oauth2 = new \Google_Service_Oauth2($client);
        
        if (isset($_REQUEST['logout'])) 
        {
            Session::set('access_token', '');
            $client->revokeToken();
        }

        if (isset($_GET['code'])) 
        {
            $client->authenticate($_GET['code']);
            Session::set('access_token', $client->getAccessToken());            
            Redirect::path(\ntentan\Router::getRoute());
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
