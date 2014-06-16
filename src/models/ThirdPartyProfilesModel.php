<?php
namespace ntentan\plugins\social\models;

use ntentan\models\Model;

class ThirdPartyProfilesModel extends Model
{

    public $belongsTo = array(
        'user',
    );

}