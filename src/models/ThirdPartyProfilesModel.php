<?php
namespace ntentan\extensions\social\models;

use ntentan\Model;

class ThirdPartyProfilesModel extends Model
{
    public $belongsTo = array(
        'user',
    );
}