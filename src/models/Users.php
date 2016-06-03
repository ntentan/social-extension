<?php

namespace ntentan\extensions\social\models;

use ntentan\Model;

class Users extends Model
{
    public function preSaveCallback()
    {
        if($this->password) {
            $this->password = md5($this->password);
        }
    }
}
