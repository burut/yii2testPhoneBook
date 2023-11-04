<?php

namespace app\models;

use yii\db\ActiveRecord;

class Phone extends ActiveRecord
{
    public function rules()
    {
        return [
            ['person_id', 'integer'],
            ['number', 'required']
        ];
    }

    public function getPerson() :Person
    {
        return $this->hasOne(Person::className(), ['id' => 'person_id']);
    }
}