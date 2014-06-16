<?php 
namespace ntentan\extensions\social\widgets\signin;

use ntentan\extensions\social\Social;
use ntentan\views\widgets\Widget;

class SigninWidget extends Widget
{
    public function execute()
    {
        $this->set('social_signin_base_url', Social::$baseUrl);
    }
}
