<?php

namespace app\models;

use yii\db\ActiveRecord;

class Person extends ActiveRecord
{
    public function getPhones()
    {
        return $this->hasMany(Phone::className(), ['person_id' => 'id']);
    }
}