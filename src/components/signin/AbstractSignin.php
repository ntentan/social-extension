<?php
namespace ntentan\extensions\social\components\signin;

use ntentan\Ntentan;

abstract class AbstractSignin
{
    protected function doOpenId($identity)
    {
        require "vendor/lightopenid/openid.php";
        
        $openid = new \LightOpenID(Ntentan::$config['application']['domain']);
        
        if(!$openid->mode)
        {
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
    
    abstract public function signin();
    abstract public function getProvider();
}