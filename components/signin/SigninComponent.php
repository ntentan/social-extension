<?php 
namespace ntentan\plugins\social\components\signin;

use ntentan\plugins\social\Social;
use ntentan\controllers\components\Component;
use ntentan\views\template_engines\TemplateEngine;
use ntentan\Ntentan;
use ntentan\models\Model;

class SigninComponent extends Component
{
    public function init()
    {
        parent::init();
        Ntentan::addIncludePath(Ntentan::$pluginsPath . "social");
        TemplateEngine::appendPath(Ntentan::getPluginPath("social/views/signin"));
        $this->set('app', Ntentan::$config['application']['name']);
    }
    
    private function doThirdPartySignin($status, $provider, $register)
    {
        $this->view->template = 'signin_third_party.tpl.php';
    
        if(is_array($status))
        {
            $_SESSION['third_party_authenticated'] = true;
            $_SESSION['third_party_provider'] = $provider;
            $_SESSION['provider_key'] = $status['key'];
    
            $thirdPartyProfile = Model::load('third_party_profiles')->getJustFirstWithKey(
                $status['key'],
                array(
                    'conditions'=>array(
                        'provider'=>$provider
                    )
                )
            );
    
            if($thirdPartyProfile->count() == 1)
            {
                $_SESSION['logged_in'] = true;
                $user = Model::load('users')->getJustFirstWithId($thirdPartyProfile->user_id);
                $_SESSION['user'] = $user->toArray();
    
                /*Activities::log(
                    "LOGGED IN VIA THIRD PARTY",
                    array(
                        'object' => Activities::OBJECT_THIRD_PARTY_PROFILE,
                        'object_id' => $thirdPartyProfile->id
                    )
                );*/
            }
    
            if(isset($status['email']))
            {
                $user = Model::load('users')->getJustFirstWithEmail($status['email'])->toArray();
                if(isset($user['id']))
                {
                    $this->set('status', 'existing');
                    $this->set('provider', $provider);
                }
                else
                {
                    $this->set('provider', $provider);
                    $this->set('name', $status['firstname']);
                    $this->set('status', 'no_profile');
                    $this->set('register', $register);
                }
            }
        }
        else if($authStatus == "cancelled")
        {
            $this->set('status', $status);
            $this->set('provider', $provider);
        }
        else if($authStatus == "failed")
        {
            $this->set('status', $status);
            $this->set('provider', $provider);
        }
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
    
    public function setBaseUrl($baseUrl)
    {
        Social::$baseUrl = $baseUrl;
        $this->set('social_signin_base_url', $baseUrl);
    }
    
    public function signinWithGoogle()
    {
        $authStatus = $this->doOpenId("https://www.google.com/accounts/o8/id");
        $this->doThirdPartySignin($authStatus, 'Google', 'register_through_google');
    }
    
    public function getGoogleProfile()
    {
        $this->view->template = false;
        $this->view->layout = false;
        
        if(!$_SESSION['third_party_authenticated'] || $_SESSION['third_party_provider']!= 'Google')
        {
            throw new \ntentan\exceptions\RouteNotAvailableException();
        }
        
        require "vendor/google-api-php-client/src/apiClient.php";
        require "vendor/class.http.php";
        
        $apiClient = new \apiClient();
        
        $apiClient->setScopes(
            array(
                "https://www.googleapis.com/auth/userinfo.profile",
                "https://www.googleapis.com/auth/userinfo.email"
            )
        );
        $token = json_decode($apiClient->authenticate(), true);
        
        if(is_array($token))
        {

            $_SESSION['provider_data'] = $token;

            $http = new \Http();
            @$http->execute("https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token['access_token']}");
            $profile = json_decode($http->result, true);
            $_SESSION['provider_data']['user_id'] = $profile['id'];

            $_SESSION['third_party_profile']['email'] = $profile['email'];
            $_SESSION['third_party_profile']['firstname'] = $profile['given_name'];
            $_SESSION['third_party_profile']['lastname'] = $profile['family_name'];
            $extension = end(explode('.', $profile['picture']));
            $_SESSION['third_party_profile']['avatar'] = "tmp/" . uniqid() . ".$extension";
            $_SESSION['third_party_profile']['username'] = reset(explode("@", $profile['email']));

            $http = new \Http();
            @$http->execute($profile['picture']);
            file_put_contents($_SESSION['third_party_profile']['avatar'], $http->result);
            Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . "/register"));
        }
    }    
    
    public function registerThroughGoogle()
    {
        $this->view->template = false;
        $this->view->layout = false;
    
        if(!$_SESSION['third_party_authenticated'] || $_SESSION['third_party_provider']!= 'Google')
        {
            throw new \ntentan\exceptions\RouteNotAvailableException();
        }
    
        switch($_POST['action'])
        {
            case 'import':
                Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . "/get_google_profile"));
                break;
    
            default:
                Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . "/register"));
                break;
        }
    }
    
    public function register()
    {
        $this->view->template = 'signin_register.tpl.php';
        if(isset($_POST['firstname']))
        {
            $this->set('form_data', $_POST);
    
            if($_POST['password'] != $_POST['password2'] && !$_SESSION['third_party_authenticated'])
            {
                $this->set('errors',  array('password'=>array('Passwords do not match')));
            }
            else
            {
                $user = Model::load('users');
                $user->firstname = $_POST['firstname'];
                $user->lastname = $_POST['lastname'];
                $user->email = $_POST['email'];
                $user->username = $_POST['username'];
                $user->password = md5($_POST['password']);
    
                if($user->validate())
                {
                    if($_SESSION['third_party_profile']['avatar'] != '')
                    {
                        $dest = basename($_SESSION['third_party_profile']['avatar']);
                        $user->avatar = $dest;
                        copy($_SESSION['third_party_profile']['avatar'], "uploads/avatars/$dest");
                    }
    
                    $userId = $user->save();
    
                    //Activities::log("JOINED", array('user_id' => $userId));
    
                    if($_SESSION['third_party_authenticated'])
                    {
                        $thirdPartyProfile = Model::load('third_party_profiles');
                        $thirdPartyProfile->user_id = $userId;
                        $thirdPartyProfile->provider = $_SESSION['third_party_provider'];
                        $thirdPartyProfile->provider_data = json_encode($_SESSION['provider_data']);
                        $thirdPartyProfile->key = $_SESSION['provider_key'];
                        $profileId = $thirdPartyProfile->save();
                        
                        /*Activities::log(
                            "ADDED A LINK TO A THIRD PARTY PROFILE",
                            array(
                                'object' => Activities::OBJECT_THIRD_PARTY_PROFILE,
                                'object_id' => $profileId,
                                'user_id' => $userId
                            )
                        );*/
                    }
    
                    $_SESSION = array(
                        'logged_in' => true,
                        'user' => $user->getFirstWithId($userId)->toArray(),
                        'artist' => $_POST['artist_name'] == '' ? null : $artist->getFirstWithId($artistId)->toArray()
                    );
    
                    Ntentan::redirect('users/confirm_registration');
                }
                else
                {
                    $this->set('errors', $user->invalidFields);
                }
            }
        }
        else if(is_array($_SESSION['third_party_profile']))
        {
            $this->set('form_data', $_SESSION['third_party_profile']);
        }
    }
    
    public function confirmRegistration()
    {
        $this->set('firstname', $_SESSION['user']['firstname']);
    }
}
