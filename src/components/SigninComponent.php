<?php 
namespace ntentan\extensions\social\components;

use ntentan\extensions\social\Social;
use ntentan\controllers\Component;
use ntentan\honam\TemplateEngine;
use ntentan\Ntentan;
use ntentan\Model;
use ntentan\utils\Text;
use ntentan\View;
use ntentan\config\Config;
use ntentan\Router;
use ntentan\controllers\Redirect;
use ntentan\Session;

class SigninComponent extends Component
{
    const ON_SUCCESS_REDIRECT = 'redirect';
    const ON_SUCCESS_CALL_FUNCTION = 'call_function';
    
    public $redirectUrl = '/';
    public $signinRoute = false;
    private $excludedRoutes = array();
    public $onSignin = self::ON_SUCCESS_REDIRECT;
    public $signinFunction;
    public $onSignout = self::ON_SUCCESS_REDIRECT;
    public $signoutFunction;

    /**
     * Initialize the component.
     * 
     * @see ntentan\controllers.Controller::init()
     */
    public function init()
    {
        parent::init();
        
        // Setup the template engine and set params
        TemplateEngine::appendPath(realpath(__DIR__ . "/../../views/signin"));
        View::set('app', Config::get('ntentan:app.name'));
        View::set('social_signin_base_url', '');
    }
        
    public function setBaseUrl($baseUrl)
    {
        Social::$baseUrl = $baseUrl;
        View::set('social_signin_base_url', $baseUrl);
        $this->excludedRoutes[] = "{$baseUrl}/signin";
        $this->excludedRoutes[] = "{$baseUrl}/get_profile";
        $this->excludedRoutes[] = "{$baseUrl}/register";
        $this->excludedRoutes[] = "{$baseUrl}/signup";
    }
    
    public function authorize()
    {
        // Prevent the user from having access to protected content
        foreach($this->excludedRoutes as $excludedRoute)
        {
            if(preg_match_all("/$excludedRoute/i", Router::getRoute()) > 0)
            {
                return;
            }
        }
        
        if(Session::get("logged_in"))
        {
            Redirect::path($this->signinRoute ? $this->signinRoute : Url::action('signin'));
        }
    }
    
    private function getSigninServiceObject($serviceType)
    {
        $serviceTypeClass = "\\ntentan\\extensions\\social\\components\\signin\\" . Text::ucamelize($serviceType) . "Signin";
        return new $serviceTypeClass();
    }
    
    public function signin($provider = null)
    {
        if($provider === null)
        {
            View::setTemplate('social_signin.tpl.php');
            if(isset($_POST['username']))
            {
                $user = Model::load('users')->getFirstWithUsername($_POST['username']);
                if(md5($_POST['password']) == $user->password)
                {
                    $_SESSION = array(
                        'logged_in' => true,
                        'user' => $user->toArray(),
                    );
                    $this->performSuccessOperation();
                }
                else
                {
                    View::set('failed', true);
                }
            }
        }
        else
        {
            View::setTemplate('third_party.tpl.php');
            $service = $this->getSigninServiceObject($provider);
            $authStatus = $service->signin();
            
            if($authStatus === false)
            {
                View::set('failed', true);
                View::set('status', 'failed');
            }
            else
            {
                // Check if the third party profile exists if it does fetch the
                // associated user.
                $thirdPartyProfile = Model::load('third_party_profiles')->fetchFirstWithKey($authStatus['key']);
                if(count($thirdPartyProfile) > 0)
                {
                   $_SESSION['user'] = $thirdPartyProfile['user']->toArray();
                   $_SESSION['logged_in'] = true;
                   $this->performSuccessOperation();
                }
                else 
                {
                    // If the third party profile doesn't exist create it and create
                    // an associated user. However check if the email exists and warn
                    // if necessary
                    $user = Model::load('users')->getJustFirst(
                        array(
                            'conditions' => array(
                                'email' => $authStatus['email'],
                                'email<>' => null
                            )
                        )
                    );
                    
                    if($user->count() == 1) 
                    {
                        View::set('status', 'existing');
                        return;
                    }
                    else
                    {
                        $user = Model::load('users')->getNew();
                        $user->username = $authStatus['email'] == '' ? uniqid() : $authStatus['email'];
                        $user->password = '-';
                        $user->email = $authStatus['email'];

                        @$avatar = uniqid(); // . '.' . (isset($authStatus['avatar_format']) ? $authStatus['avatar_format'] : end(explode('.', $authStatus['avatar'])));
                        $avatarData = file_get_contents($authStatus['avatar']);
                        file_put_contents("uploads/avatars/$avatar", $avatarData);
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file("uploads/avatars/$avatar");
                        @$finalAvatar = $avatar . '.' . end(explode('/', $mime));
                        rename("uploads/avatars/$avatar", "uploads/avatars/$finalAvatar");

                        $user->avatar = $finalAvatar;
                        $user->firstname = $authStatus['firstname'];
                        $user->lastname = $authStatus['lastname'];
                        $user->email_confirmed = $authStatus['email_confirmed'];
                        $userID = $user->save();
                        $user->id = $userID;

                        $thirdParty = Model::load('third_party_profiles')->getNew();
                        $thirdParty->user_id = $userID;
                        $thirdParty->provider = $service->getProvider();
                        $thirdParty->key = $authStatus['key'];
                        $thirdParty->save();
                        
                        $_SESSION['user'] = $user->toArray();
                        $_SESSION['logged_in'] = true;
                        $this->performSuccessOperation();                        
                    }                    
                }
            }
        }
    }
    
    public function signout()
    {
        $this->template = false;        
        
        if($this->signoutFunction != '')
        {
            $decomposed = explode("::", $this->signoutFunction);
            $className = $decomposed[0];
            $methodName = $decomposed[1];
            $method = new \ReflectionMethod($className, $methodName);
            $method->invoke(null, $this->controller);       
        }
        
        session_destroy();        
        Ntentan::redirect($this->redirectUrl);
    }
    
    public function register()
    {
        View::setTemplate('signin_register.tpl.php');
        View::set('errors', []);
        View::set('form_data', []);
        if(isset($_POST['firstname']))
        {
            View::set('form_data', $_POST);
    
            if($_POST['password'] != $_POST['password2'] && !Session::get('third_party_authenticated'))
            {
                View::set('errors',  array('password'=>array('Passwords do not match')));
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
    
                    if($_SESSION['third_party_authenticated'])
                    {
                        $thirdPartyProfile = Model::load('third_party_profiles');
                        $thirdPartyProfile->user_id = $userId;
                        $thirdPartyProfile->provider = $_SESSION['third_party_provider'];
                        $thirdPartyProfile->provider_data = json_encode($_SESSION['imported_profile_data']['provider_data']);
                        $thirdPartyProfile->key = $_SESSION['provider_key'];
                        $profileId = $thirdPartyProfile->save();
                    }
    
                    $_SESSION = array(
                        'logged_in' => true,
                        'user' => $user->getFirstWithId($userId)->toArray(),
                    );
    
                    Ntentan::redirect('users/confirm_registration');
                }
                else
                {
                    View::set('errors', $user->invalidFields);
                }
            }
        }
        else if(is_array(Session::get('imported_profile_data')['third_party_profile']))
        {
            View::set('form_data', Session::get('imported_profile_data')['third_party_profile']);
        }
    }
    
    /**
     * @ntentan.action register
     * @ntentan.method POST
     * @param \paanoo\models\Users $user
     */
    public function saveRegistration(\paanoo\models\Users $user)
    {
        var_dump($user);
    }
    
    public function confirmRegistration()
    {
        if($_GET['confirmed'] == 'yes')
        {
            $this->performSuccessOperation();
        }
        else
        {
            View::set('firstname', $_SESSION['user']['firstname']);
        }
    }
    
    public function addExcludedRoutes()
    {
        $args = func_get_args();
        foreach($args as $arg)
        {
            if(is_array($arg)) 
            {
                $this->excludedRoutes += $arg;
            }
            else
            {
                $this->excludedRoutes[] = $arg;
            }
        }
    }
    
    private function performSuccessOperation()
    {
        switch($this->onSignin)
        {
            case self::ON_SUCCESS_REDIRECT:
                Redirect::path($this->redirectUrl);
                break;

            case self::ON_SUCCESS_CALL_FUNCTION:
                $decomposed = explode("::", $this->signinFunction);
                $className = $decomposed[0];
                $methodName = $decomposed[1];
                $method = new \ReflectionMethod($className, $methodName);
                $method->invoke(null, $this->controller);                            
                break;
        }        
    }
}
