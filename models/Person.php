<?php

namespace app\models;

use yii\db\ActiveRecord;

class Person extends ActiveRecord
{
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['firstname', 'email', 'birthday'], 'required'],
            ['firstname', 'safe'],
            ['lastname', 'safe'],
            ['email', 'email'],
            ['birthday', 'validateBirthday'],
        ];
    }

    public function validateBirthday($attribute)
    {
        $birthday = date('Y-m-d', strtotime('-18 year'));

        if ($birthday < $this->$attribute) {
            $this->addError($attribute, 'must be no later than ' . $birthday);
        }
    }

    public function getPhones()
    {
        return $this->hasMany(Phone::className(), ['person_id' => 'id']);
    }
}