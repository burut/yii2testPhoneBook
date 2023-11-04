<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

    <link href="css/datatables.min.css?<?php echo time() ?>" rel="stylesheet" type="text/css"/>
    <link href="css/jquery.dataTables.min.css?<?php echo time() ?>" rel="stylesheet" type="text/css"/>
    <link href="datepicker/css/datepicker.css?<?php echo time() ?>" rel="stylesheet" type="text/css"/>
    <link href="css/select2.css?<?php echo time() ?>" rel="stylesheet" type="text/css"/>
    <link href="css/custom.css?<?php echo time() ?>" rel="stylesheet" type="text/css"/>

    <script src="js/jquery-3.3.1.min.js?<?php echo time() ?>" type="text/javascript"></script>
    <script src="js/serialize.js?<?php echo time() ?>" type="text/javascript"></script>
    <script src="datatables/js/jquery.dataTables.js?<?php echo time() ?>" type="text/javascript"></script>
    <script src="datepicker/js/datepicker.js?<?php echo time() ?>" type="text/javascript"></script>
    <script src="js/select2.min.js?<?php echo time() ?>" type="text/javascript"></script>
    <script src="js/main.js?<?php echo time() ?>" type="text/javascript"></script>

    <div class="js-response-loader loader-cover container-loader" id="loader" style="display: none;">
        <div class="css-loader-circle"></div>
    </div>
    <div class="body">

        <div class="row">
            <div class="col-sm-2">
                <button class="btn btn-primary js-add-new-person">Add new</button>
            </div>
        </div>

        <table class="js-datatable dataTable">
            <thead>
                <tr>
                    <th>first name</th>
                    <th>last name</th>
                    <th>Email</th>
                    <th>birthday</th>
                    <th>phone number</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($persons as $person) {
                        echo '
                            <tr>
                                <td>' . $person->firstname . '</td>
                                <td>' . $person->lastname . '</td>
                                <td>' . $person->email . '</td>
                                <td>' . $person->birthday . '</td>
                                <td>' . (!empty($person->phones[0]) ? $person->phones[0]->number : '') . (!empty($person->phones[1]) ? ', ...' : '') . '</td>
                                <td>';
                                    foreach ($person->phones as $phone) {
                                        echo $phone->number . ',';
                                    }
                        echo'   </td>
                                <td><button class="btn btn-primary js-edit" data-id="' . $person->id . '">Edit</button></td>
                                <th><button class="btn btn-danger js-delete" data-id="' . $person->id . '">Delete</button></th>
                            </tr>';
                        }
                ?>
            </tbody>

        </table>
    </div>

    <!--MODAL EDIT PERSON-->
    <div class="modal fade in delete" id="edit-person" tabindex="-1" role="dialog" aria-labelledby="ultraModal-Label" aria-hidden="true">
        <div class="modal-dialog animated bounceInUp">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <?php $form = ActiveForm::begin() ?>
                <div class="modal-body">
                    <?= $form->field($personModel, 'id')->hiddenInput()->label('') ?>
                    <?= $form->field($personModel, 'firstname') ?>
                    <?= $form->field($personModel, 'lastname') ?>
                    <?= $form->field($personModel, 'email') ?>
                    <?= $form->field($personModel, 'birthday') ?>
                    <?= $form->field($phoneModel, 'number') ?>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
    <!--/MODAL EDIT PERSON-->

    <!--MODAL DELETE PERSON-->
    <div class="modal fade in delete" id="delete-person" tabindex="-1" role="dialog" aria-labelledby="ultraModal-Label" aria-hidden="true">
        <div class="modal-dialog modal-delete-person animated bounceInUp">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="modal-img">
                        <img src="images/delete.png" alt="">
                    </div>
                    <div class="title">Do you want to delete it?</div>
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <button class="btn btn-primary js-delete-person" type="button">Yes</button>
                </div>
            </div>
        </div>
    </div>
    <!--/MODAL DELETE PERSON-->
