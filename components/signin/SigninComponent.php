<?php 
namespace ntentan\plugins\social\components\signin;

use ntentan\plugins\social\Social;
use ntentan\controllers\components\Component;
use ntentan\views\template_engines\TemplateEngine;
use ntentan\Ntentan;
use ntentan\models\Model;

class SigninComponent extends Component
{
    public $redirectUrl = '/';
    
    public function init()
    {
        parent::init();
        Ntentan::addIncludePath(Ntentan::$pluginsPath . "social");
        TemplateEngine::appendPath(Ntentan::getPluginPath("social/views/signin"));
        $this->set('app', Ntentan::$config['application']['name']);
    }
    
    private function doThirdPartySignin($status, $provider)
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
                Ntentan::redirect($this->redirectUrl);
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
                    $this->set('register', "register_through/{$provider}");
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
    
    public function setBaseUrl($baseUrl)
    {
        Social::$baseUrl = $baseUrl;
        $this->set('social_signin_base_url', $baseUrl);
    }
    
    private function getSigninServiceObject($serviceType)
    {
        $serviceTypeClass = "\\ntentan\\plugins\\social\\components\\signin\\services\\" . Ntentan::camelize($serviceType);
        return new $serviceTypeClass();
    }
    
    public function signin($serviceType)
    {
        $service = $this->getSigninServiceObject($serviceType);
        $authStatus = $service->signin();
        $this->doThirdPartySignin($authStatus, $service->getProvider());
    }

    public function getProfile()
    {
        $this->view->template = false;
        $this->view->layout = false;
        $service = $this->getSigninServiceObject($_SESSION['third_party_provider']);
        $profile = $service->getProfile();
        if($profile !== false)
        {
            $_SESSION['imported_profile_data'] = $profile;
            Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . '/register'));
        }
    }
    
    public function registerThrough($serviceType)
    {
        $service = $this->getSigninServiceObject($serviceType);
        $this->view->template = false;
        $this->view->layout = false;
    
        if(!$_SESSION['third_party_authenticated'] || $_SESSION['third_party_provider']!= $service->getProvider())
        {
            throw new \ntentan\exceptions\RouteNotAvailableException();
        }
    
        switch($_POST['action'])
        {
            case 'import':
                Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . "/get_profile"));
                break;
    
            default:
                Ntentan::redirect(Ntentan::getUrl(Social::$baseUrl . "/register"));
                break;
        }
    }
    
    public function signout()
    {
        $this->template = false;
        $_SESSION = array();
        Ntentan::redirect($this->redirectUrl);
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
                    if($_SESSION['imported_profile_data']['third_party_profile']['avatar'] != '')
                    {
                        $dest = basename($_SESSION['imported_profile_data']['third_party_profile']['avatar']);
                        $user->avatar = $dest;
                        copy($_SESSION['imported_profile_data']['third_party_profile']['avatar'], "uploads/avatars/$dest");
                        unlink($_SESSION['imported_profile_data']['third_party_profile']['avatar']);
                    }
    
                    $userId = $user->save();
    
                    //Activities::log("JOINED", array('user_id' => $userId));
    
                    if($_SESSION['third_party_authenticated'])
                    {
                        $thirdPartyProfile = Model::load('third_party_profiles');
                        $thirdPartyProfile->user_id = $userId;
                        $thirdPartyProfile->provider = $_SESSION['third_party_provider'];
                        $thirdPartyProfile->provider_data = json_encode($_SESSION['imported_profile_data']['provider_data']);
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
        else if(is_array($_SESSION['imported_profile_data']['third_party_profile']))
        {
            $this->set('form_data', $_SESSION['imported_profile_data']['third_party_profile']);
        }
    }
    
    public function confirmRegistration()
    {
        $this->set('firstname', $_SESSION['user']['firstname']);
    }
}
