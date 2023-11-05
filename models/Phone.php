<?php

namespace app\models;

use yii\db\ActiveRecord;

class Phone extends ActiveRecord
{
    public function rules()
    {
        return [
            ['person_id', 'integer'],
            ['number', 'required'],
            ['number', 'unique'],
            ['number', 'validateNumber'],
        ];
    }

    public function getPerson() :Person
    {
        return $this->hasOne(Person::className(), ['id' => 'person_id']);
    }

    public function validateNumber($attribute)
    {
        $value = $this->$attribute;
        $valueLen = strlen($value);
        preg_match_all('/[+0-9]/', $value, $match);
        $matchCnt = count($match[0]);
        preg_match_all('/^\+/', $value, $matchPlus);

        if ( !( ($matchPlus[0] && $valueLen === 13 && $matchCnt === 13) || (!$matchPlus[0] && $valueLen === 10 && $matchCnt === 10) ) ) {
            $this->addError($attribute, "({$value}) phone number not valid");
        }
    }
}