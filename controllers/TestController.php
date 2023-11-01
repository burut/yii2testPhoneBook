<?php

namespace app\controllers;

use app\Manager\PersonManager;
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
        return $this->render('test.html.twig', [
            'persons' => $this->manager->getPersonsList(),
            'time'    => time(),
        ]);
    }

    public function actionPersonDataLoad()
    {
        $id = (int) (Yii::$app->request->get()['id'] ?? 0);

        return json_encode($this->manager->personDataLoad($id));
    }

    public function actionPersonDataSave()
    {
        $data = Yii::$app->request->post()['form'] ?? [];

        return json_encode($this->manager->personDataSave($data));
    }

    public function actionPersonDataDelete()
    {
        $personId = (int) (Yii::$app->request->post()['id'] ?? 0);

        $this->manager->personDataDelete($personId);

        return json_encode(['status' => true]);
    }
}