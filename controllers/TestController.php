<?php

namespace app\controllers;

use app\models\Person;
use app\models\Phone;
use app\Manager\PersonManager;
use Yii;
use yii\web\Controller;

class TestController extends Controller
{
    /** @var PersonManager */
    public $manager;

    public function __construct($id, $module, $config = [], PersonManager $manager)
    {
        parent::__construct($id, $module, $config);

        $this->manager = $manager;
    }

    public function actionIndex()
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

        return $this->render('test.html.twig', [
            'persons' => $persons,
            'time'    => time(),
        ]);
    }

    public function actionPersonDataLoad()
    {
        $id = Yii::$app->request->get()['id'] ?? 0;

        $person = Person::find()->asArray()->with('phones')->where("id={$id}")->limit(1)->all()[0] ?? [];

        return json_encode($person);
    }

    public function actionPersonDataSave()
    {
        $data = Yii::$app->request->post()['form'] ?? [];

        $personId = (int) trim($data['id']);
        $idNew = empty($personId);
        $firstname = trim($data['firstname']);
        $lastname = trim($data['lastname']);
        $email = trim($data['email']);
        $birthday = trim($data['birthday']);
        $numbers = $data['phonenumber'] ?? [];

        $tooYoung = (time() - (18 * 365 * 24 * 60 * 60)) < strtotime($birthday . ' 00:00:00');

        if ($tooYoung || !$firstname || !$numbers || $isInvalidEmal = !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $textMessage = $tooYoung ? 'This person is too young. must be at least 18 years old' : '';
            $textMessage = !$firstname ? 'Firstname should not be empty' : $textMessage;
            $textMessage = !$numbers ? 'Person must have at least one phone number' : $textMessage;
            $textMessage = $isInvalidEmal ? 'Email is not valid' : $textMessage;

            return json_encode(
                [
                    'status'          => false,
                    'text_message'    => $textMessage,
                    'too_young'       => $tooYoung,
                    'empty_firstname' => !$firstname,
                    'empty_numbers'   => !$numbers,
                    'invalid_email'   => $isInvalidEmal,
                ]
            );
        }

        if ($personId) {
            $person = $personOrigin = Person::findOne($personId); // need to check for existing phones objects in main object
            if (!$person) {

                return json_encode(
                    [
                        'status'       => false,
                        'text_message' => 'this person is not in the DB',
                    ]
                );
            }
        } else {
            $person = new Person();
        }

        $checkEmailPerson = Person::find()->where("email=:email", [':email' => $email])->limit(1)->all()[0] ?? null;

        if ($checkEmailPerson && $checkEmailPerson->id !== $personId) {
            return json_encode(
                [
                    'status'        => false,
                    'text_message'  => 'Peron email not added. Probably already in use',
                    'invalid_email' => true,
                ]
            );
        }

        $person->firstname = $firstname;
        $person->lastname = $lastname;
        $person->email = $email;
        $person->birthday = $birthday;

        $existsNumbers = [];
        /** @var Phone $phone */
        foreach ($person->phones as $phone) {
            $existsNumbers[] = $phone;
        }

        $newPhones = [];
        foreach ($numbers as $number) {
            $number = trim($number);
            if (!in_array($number, $existsNumbers, true)) {
                $phone = Phone::find()->where("number={$number}")->limit(1)->all()[0] ?? null;
                if ($phone && $phone->person_id !== $personId) {

                    return json_encode(
                        [
                            'status'          => false,
                            'text_message'    => 'Phone number not added. Probably already in use',
                            'duplicate_phone' => $number,
                        ]
                    );
                }

                if (!$phone) {
                    preg_match('/^\+{0,1}(?:[0-9]?){6,14}[0-9]$/', $number, $q);
                    if ($q) {
                        $newPhone = new Phone();
                        $newPhone->number = $number;

                        $newPhones[] = $newPhone;
                    }
                }
            }
        }

        /** @var Phone $phone */
        foreach ($person->phones as $phone) {
            if (!in_array($phone->number, $numbers, true)) {
                $phone->delete();
            }
        }


        if (!$newPhones && !$personId) {

            return json_encode(
                [
                    'status'       => false,
                    'text_message' => 'Phone number not added.Probably already in use',
                ]
            );
        }

        if (!$personId) {
            $person->save();
            $personId = $person->id;
        }

        /** @var Phone $phone */
        foreach ($newPhones as $phone) {
            $phone->person_id = $personId;
            $phone->save();
        }

        $person->save();

        $person = Person::find()->with('phones')->where("id={$personId}")->limit(1)->all()[0] ?? null;
        if (!$idNew && !$person->phones) {
            $personOrigin->save();

            foreach ($personOrigin->phones as $phone) {
                $phone->save();
            }

            return json_encode(
                [
                    'status'       => false,
                    'text_message' => 'Errors in phone numbers',
                ]
            );
        }

        if ($person && !$person->phones) {
            $person->delete();

            return json_encode(
                [
                    'status'       => false,
                    'text_message' => 'Person was deleted, because there is no phone numbers/',
                ]
            );
        }

        return json_encode(['status' => true,]);
    }

    public function actionPersonDataDelete()
    {
        $personId = Yii::$app->request->post()['id'] ?? 0;

        $person = Person::findOne($personId);

        $person->delete();

        return json_encode(['status' => true,]);
    }
}