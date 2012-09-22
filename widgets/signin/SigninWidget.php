<?php 
namespace ntentan\plugins\social\widgets\signin;

use ntentan\views\template_engines\TemplateEngine;
use ntentan\Ntentan;
use ntentan\views\widgets\Widget;

class SigninWidget extends Widget
{
    public function execute()
    {
        TemplateEngine::appendPath(Ntentan::getPluginPath("social/views/signin"));
    }
}
