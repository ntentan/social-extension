<?php
namespace ntentan\extensions\social\models;

use ntentan\models\Model;

class ThirdPartyProfilesModel extends Model
{

    public $belongsTo = array(
        'user',
    );

}