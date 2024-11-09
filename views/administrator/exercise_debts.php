<?php $this->beginBlock('title') ?>
    Dettes
<?php $this->endBlock() ?>
<?php $this->beginBlock('style') ?>
    <style>

        .warning-block {
            border : 2px solid darkorange;
            color: #c76c00;
            background-color: rgba(255, 140, 0, 0.17);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
<?php $this->endBlock() ?>
<?php
$refunds = \app\models\Refund::find()->where(['is not','exercise_id',null])->all();
?>
<div class="container mb-5 mt-5">


        <div class="row mb-2">
            <div class="col-12 white-block">
                <h3 class="text-center text-muted">Inscriptions</h3>
                <hr>

                <?php
                $members = \app\models\Member::find()->where(['<','social_crown', \app\managers\SettingManager::getInscription()])->all();
                if (count($members)):
                ?>
                <table class="table table-hover">
                    <thead class="blue-grey lighten-4">
                    <tr>
                        <th>#</th>
                        <th>Membre</th>
                        <th>montant réglé</th>
                        <th>Montant restant à payer</th>
                        <th>action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $index => $member): ?>
                        <?php
                        $memberUser = $member->user();
                        ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td class="text-capitalize"><?= $memberUser->name . " " . $memberUser->first_name ?></td>
                                <td class="blue-text"><?=  $member->social_crown ?> XAF</td>

                                <td class="red-text"><?=  \app\managers\SettingManager::getInscription()- $member->social_crown ?></td>
                                    <td><button class="btn btn-primary p-2 m-0" data-target="#modalS<?= $member->id ?>" data-toggle="modal">payer </button></td>

                            </tr>


                        <div class="modal fade" id="modalS<?= $member->id?>" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">


                                    <?php                                    $model = new  \app\models\forms\FixInscriptionForm();

                                     $form = \yii\widgets\ActiveForm::begin([
                                'errorCssClass' => 'text-secondary',
                                'method' => 'post',
                                'action' => ['@administrator.fix_inscription', 'id'=>$member->id],
                                'options' => ['class' => 'col-12 white-block']
                                ]) ?>

                            <h3> Veuillez entrer le montant à payer</h3>
                                <?= $form->field($model, 'amount')->input('number', ['required' => 'required', 'min' =>1])->label("montant") ?>
                                <?= $form->field($model,'id')->hiddenInput(['value'=>$member->id])->label(false) ?>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary" >valider </button>
                                
                            </div>
                            <?php \yii\widgets\ActiveForm::end(); ?>

                                    
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                else:
                ?>
                <p class="text-center blue-text">Aucune dette de règlement de Inscription</p>
                <?php
                endif;
                ?>
            </div>
        </div>


        <div class="row mb-2">
            <div class="col-12 white-block">
                <h3 class="text-center text-muted">Fond Social</h3>
                <hr>

                <?php
                $members = \app\models\Member::find()->where(['<','social_crown', \app\managers\SettingManager::getSocialCrown()])->all();
                if (count($members)):
                ?>
                <table class="table table-hover">
                    <thead class="blue-grey lighten-4">
                    <tr>
                        <th>#</th>
                        <th>Membre</th>
                        <th>montant réglé</th>
                        <th>Montant restant à payer</th>
                        <th>action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($members as $index => $member): ?>
                        <?php
                        $memberUser = $member->user();
                        ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td class="text-capitalize"><?= $memberUser->name . " " . $memberUser->first_name ?></td>
                                <td class="blue-text"><?=  $member->inscription ?> XAF</td>

                                <td class="red-text"><?=  \app\managers\SettingManager::getSocialCrown()- $member->inscription ?></td>
                                    <td><button class="btn btn-primary p-2 m-0" data-target="#modalS<?= $member->id ?>" data-toggle="modal">payer </button></td>

                            </tr>


                        <div class="modal fade" id="modalS<?= $member->id?>" tabindex="-1" role="dialog"
                             aria-labelledby="myModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">


                                    <?php                                    $model = new  \app\models\forms\FixInscriptionForm();

                                     $form = \yii\widgets\ActiveForm::begin([
                                'errorCssClass' => 'text-secondary',
                                'method' => 'post',
                                'action' => ['@administrator.fix_social_crown', 'id'=>$member->id],
                                'options' => ['class' => 'col-12 white-block']
                                ]) ?>

                            <h3> Veuillez entrer le montant à payer</h3>
                                <?= $form->field($model, 'amount')->input('number', ['required' => 'required', 'min' =>1])->label("montant") ?>
                                <?= $form->field($model,'id')->hiddenInput(['value'=>$member->id])->label(false) ?>
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary" >valider </button>
                                
                            </div>
                            <?php \yii\widgets\ActiveForm::end(); ?>

                                    
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                else:
                ?>
                <p class="text-center blue-text">Aucune dette de règlement de fond social</p>
                <?php
                endif;
                ?>
            </div>
        </div>



    <div class="row">
        <div class="col-12 white-block">
            <h3 class="text-muted text-center">Dettes d'exercices</h3>
            <hr>
            <p class="warning-block text-center">
                Attention ! Il s'agit des dettes d'exercices qui n'ont pas été remboursées.
                
            </p>

            <?php
            if (count($refunds)):
            ?>
            <table class="table table-hover">
                <thead class="blue-grey lighten-4">
                <tr>
                    <th>#</th>
                    <th>Membre</th>
                    <th>Montant</th>
                    <th>Année de l'exercice</th>
                    <th></th>
                </tr>

                </thead>
                <tbody>
                <?php foreach ($refunds as $index => $refund): ?>
                    <?php $member = \app\models\Member::findOne((\app\models\Borrowing::findOne($refund->borrowing_id))->member_id);
                    $memberUser = \app\models\User::findOne($member->user_id);
                    $exercise = \app\models\Exercise::findOne($refund->exercise_id);
                    ?>
                    <tr>
                        <th scope="row"><?= $index + 1 ?></th>
                        <td class="text-capitalize"><?= $memberUser->name . " " . $memberUser->first_name ?></td>
                        <td class="blue-text"><?= $refund->amount ?> XAF</td>
                        <td class="text-capitalize"><?= $exercise->year ?></td>
                        <td><button class="btn btn-primary m-0 p-2" data-toggle="modal" data-target="#modal<?= $index?>">Regler</button></td>
                    </tr>


                <div class="modal  fade" id="modal<?= $index ?>" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-body">

                                <p class="text-center">Êtes-vous sûr(e) de vouloir régler la dette de ce membre?
                                </p>

                                <div class="form-group text-center">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                                    <a href="<?= Yii::getAlias("@administrator.treat_debt")."?q=".$refund->id?>" class="btn btn-primary">Oui</a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            else:
            ?>
            <h3 class="text-muted text-center">Aucune dette d'exercice enregistrée</h3>

            <?php
            endif;
            ?>
        </div>

    </div>
</div>
