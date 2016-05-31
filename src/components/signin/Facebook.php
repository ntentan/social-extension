<?php
namespace ntentan\extensions\social\components\signin\services;

use ntentan\Ntentan;
use ntentan\extensions\social\components\signin\SigninService;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

class Facebook extends SigninService
{
    public function signin()
    {
        FacebookSession::setDefaultApplication(
            Ntentan::$config['social.facebook.app_id'],
            Ntentan::$config['social.facebook.secret']
        );
        
        $helper = new \Facebook\FacebookRedirectLoginHelper('http://paanoo.com/users/signin/facebook');
        try{
            $session = $helper->getSessionFromRedirect();
            if($session === null){
                header('Location: ' . $helper->getLoginUrl(array('email')));
            }
        } catch (FacebookRequestException $ex) {
            
        } catch(\Exception $ex) {
            
        }
        
        if($session){
            try{
                $userRequest = new FacebookRequest($session, 'GET', '/me');
                $user = $userRequest->execute()->getGraphObject(GraphUser::className())->asArray();
                
                return array(
                    'firstname' => $user['first_name'],
                    'lastname' => $user['last_name'],
                    'key' => "facebook_{$user['id']}",
                    'avatar' => "http://graph.facebook.com/{$user['id']}/picture?type=large",
                    'email' => $user['email'],
                    'email_confirmed' => $user['verified'],
                    'avatar_format' => 'jpg'
                );             

            } catch (Exception $ex) {
                
            }
        }        
    }
    
    public function getProvider()
    {
        return 'facebook';
    }
}
