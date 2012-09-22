<?php 
namespace ntentan\plugins\social\components\signin;

use ntentan\controllers\components\Component;
use ntentan\Ntentan;

class SigninComponent extends Component
{
    public function init()
    {
        parent::init();
        Ntentan::addIncludePath(Ntentan::$pluginsPath . "social");
    }
    
    private function doOpenId($identity)
    {
        require "vendor/lightopenid/openid.php";
        $openid = new \LightOpenID(Ntentan::$config['application']['domain']);
        if(!$openid->mode)
        {
            $this->view->layout = false;
            $this->view->template = false;
            $identity = $openid->discover($identity);
            $openid->identity = $identity;
    
            $openid->required = array(
                'contact/email',
                'namePerson/first',
                'namePerson/last',
                'namePerson/friendly'
            );
    
            header('Location: ' . $openid->authUrl());
        }
        elseif($openid->mode == 'cancel')
        {
            return "cancelled";
        }
        else
        {
            if($openid->validate())
            {
                $this->set('status', 'logged_in');
                $oidStatus = $openid->getAttributes();
                $status = array(
                    'email' => $oidStatus['contact/email'],
                    'firstname' => $oidStatus['namePerson/first'],
                    'lastname' => $oidStatus['namePerson/last'],
                    'nickname' => $oidStatus['namePerson/friendly'],
                    'key' => $oidStatus['contact/email']
                );
                return $status;
            }
            else
            {
                return "failed";
            }
        }
    }
    
    public function signinWithGoogle()
    {
        $authStatus = $this->doOpenId("https://www.google.com/accounts/o8/id");
        $this->doThirdPartySignin($authStatus, 'Google', 'register_through_google');
    }
}