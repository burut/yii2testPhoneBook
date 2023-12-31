<?php

namespace app\Manager;

use app\models\Person;
use app\models\Phone;

class PersonManager
{
    public function getPersonsList() :array
    {
        $persons = Person::find()->with('phones')->all();

// ****** this part works well also. only one query instead of many
//
//        $sql = "SELECT p.id, p.firstname, p.lastname, p.email, IFNULL(GROUP_CONCAT(ph.number), '') AS phones
//                FROM Person p
//                LEFT JOIN Phone ph ON p.id = ph.person_id
//                GROUP BY p.id";
//
//        $persons = Person::findBySql($sql)->asArray()->all();
//
//        foreach ($persons as $k => $person) {
//            $numbers = explode(',', $person['phones']);
//            $persons[$k]['phones'] = [];
//            foreach ($numbers as $number) {
//                $persons[$k]['phones'][] = ['number' => $number];
//            }
//        }

        return $persons;
    }

    public function personDataLoad(int $id) :array
    {
        $person = Person::find()->asArray()->with('phones')->where("id={$id}")->limit(1)->all()[0] ?? [];

        $phones = [];
        foreach ($person['phones'] as $phone) {
            $phones[] = [
                'id'   => $phone['number'],
                'text' => $phone['number'],
            ];
        }
        $person['phones'] = $phones;

        return $person;
    }

    public function personDataSave(array $form) :bool
    {
        $isError = false;

        $personModel = $personModelOrig =  Person::findOne($form['Person']['id'] ?? 0);

        if (!$personModel) {
            $personModel = new Person();
        }

        if ($personModel->load($form) && $personModel->validate()) {
            $personModel->id = (int) $personModel->id;
            $personModel->save();

            $phonesOrig = Phone::findAll(['person_id' => $personModel->id]);
            Phone::deleteAll(['person_id' => $personModel->id]);
            foreach ($form['Phones'] as $phone) {
                $phoneModel = new Phone();
                $phone['Phone']['person_id'] = $personModel->id;
                $phoneModel->load($phone);
                if ($phoneModel->validate()) {
                    $phoneModel->save();
                } else {
                    Phone::deleteAll(['person_id' => $personModel->id]);
                    foreach ($phonesOrig as $phoneOrig) {
                        $phone = new Phone();
                        $phone->person_id = $phoneOrig->person_id;
                        $phone->number = $phoneOrig->number;
                        $phone->save();
                    }
                    $isError = true;

                    break;
                }
            }
        } else {
            $isError = true;
        }

        if ($isError && !$personModelOrig) {
            $personModel->delete();
        }

        return $isError;
    }

    public function personDataDelete(int $personId) :void
    {
        $person = Person::findOne($personId);

        if ($person) {
            $person->delete();
        }
    }

    public function getPreparedPostForm($form) :array
    {
        if (!isset($form['Person'])) {
            return [];
        }
        $personId = $form['Person']['id'] ?? null;
        $numbers = $form['Phone']['number'];
        $phoneForm = [];

        $numbersRaw = [];
        foreach ($numbers as $number) {
            $phoneForm[]['Phone'] = [
                'person_id' => $personId,
                'number'    => $number,
            ];
            $numbersRaw[] = "'{$number}'";
        }

        $form['Phones'] = $phoneForm;
        $form['numbersRaw'] = implode(',', $numbersRaw);

        return $form;
    }
}