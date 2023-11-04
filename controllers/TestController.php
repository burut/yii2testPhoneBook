<?php

namespace app\controllers;

use app\Manager\PersonManager;
use app\models\Person;
use app\models\Phone;
use Yii;
use yii\web\Controller;

class TestController extends Controller
{
    /** @var PersonManager */
    private $manager;

    public function __construct($id, $module, $config = [], PersonManager $manager)
    {
        parent::__construct($id, $module, $config);

        $this->manager = $manager;
    }

    public function actionIndex()
    {
        $personModel = new Person();
        $phoneModel = new Phone();

        if ($form = $this->manager->getPreparedPostForm(Yii::$app->request->post())) {
            $isError = $this->manager->personDataSave($form);

            if ($isError) {
                Yii::$app->session->setFlash('error', 'wrong data');
            } else {
                Yii::$app->session->setFlash('success', 'saved');
            }

            return $this->refresh();
        }

        $persons = $this->manager->getPersonsList();
        $time = time();

        return $this->render('test', compact('personModel', 'phoneModel', 'persons', 'time'));
    }

    public function actionPersonDataLoad()
    {
        $personId = (int) (Yii::$app->request->get()['id'] ?? 0);

        return json_encode($this->manager->personDataLoad($personId));
    }

    public function actionPersonDataDelete()
    {
        $personId = (int) (Yii::$app->request->post()['id'] ?? 0);

        $this->manager->personDataDelete($personId);

        return json_encode(['status' => true]);
    }
}