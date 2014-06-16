<?php 
namespace ntentan\plugins\social\widgets\signin;

use ntentan\plugins\social\Social;
use ntentan\Ntentan;
use ntentan\views\widgets\Widget;

class SigninWidget extends Widget
{
    public function execute()
    {
        $this->set('social_signin_base_url', Social::$baseUrl);
    }
}
