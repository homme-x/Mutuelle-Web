<?php
/**
 * Created by PhpStorm.
 * User: medric
 * Date: 31/12/18
 * Time: 14:31
 */

use app\models\Contribution;
use app\models\Member;

$this->beginBlock('title') ?>
    Aides
<?php $this->endBlock() ?>
<?php $this->beginBlock('style') ?>
    <style>
        .img-container {
            display: inline-block;
            width: 200px;
            height: 200px;
        }
        .img-container img{
            width: 100%;
            height: 100%;
            border-radius: 1000px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.51);
        }

        .objective {
            font-size: 2rem;
            margin: 0px;
        }

        .contributed {
            font-size: 2rem;
            margin: 0px;
        }

        .comments {
            background: rgba(128, 128, 128, 0.33);
            color: gray;
            padding: 10px;
            border-radius: 5px;
        }

        .contribution {
            border-radius: 5px;
            border: 2px solid blueviolet;
            color: blueviolet;
            background-color: rgba(138, 43, 226, 0.16);
            padding: 5px;
            font-size: 1.1rem;
        }

        .contribution img {
            width: 40px;
            height: 40px;
            border-radius: 50px;
        }
        .contribution span {
            margin-left: 5px;
        }
    </style>
<?php $this->endBlock() ?>
<?php
$member = $help->member();
$user = $member->user();
$helpType = $help->helpType();
?>
<div class="container mb-5 mt-5">
    <div class="row">
        <div class="col-12 white-block">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php elseif (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
            <div class="row mb-5 justify-content-center">
                <div class="col-md-4 text-center">
                    <h3 class="mb-2">Membre</h3>
                    <div class="img-container mb-2">
                        <img src="<?= \app\managers\FileManager::loadAvatar($user,"512") ?>" alt="">
                    </div>
                    <h2 class="text-primary"><?= $user->name." ".$user->first_name ?></h2>

                </div>
                <div class="col-md-8 text-center">
                    <h4 class="text-center text-muted"><?= $helpType->title ?></h4>
                    <p class="comments text-left"><?= $help->comments ?></p>
                    <h6 >Créée le : <?= $help->created_at ?></h6>
                    <p class="objective text-primary">Montant de l'aide : <?= $help->amount ?> XAF</p>
                    <h4 class="text-primary">Montant de contribution individuelle: <?= $help->unit_amount ?> XAF / membre</h4>
                    <h4 class="text-secondary m-0 mt-4">Montant de contributions perçues : </h4>
                    <p class="contributed text-secondary"><?= ($t=$help->contributedAmount())?$t:0 ?> XAF</p>
                    <p class="objective text-primary">deficit : <?= $help->deficit() ?> XAF</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h3 class="text-muted text-center">Détails</h3>
                    <?php
                    $contributions = $help->contributions();
                    if (count($contributions)):
                    ?>

                        <table class="table table-hover">
                            <thead class="blue-grey lighten-4">
                            <tr>
                                <th>#</th>
                                <th>Membre</th>
                                <th>Date</th>
                                <th>montant versé</th>
                                <th>Administrateur</th>

                            </tr>

                            </thead>
                            <tbody>
                            <?php foreach ($contributions as $index => $contribution): ?>
                                <?php $m = $contribution->getMember();
                                $u = $m->user();
                                $administrator = $contribution->getAdministrator();
                                $adminUser = $administrator->user();
                                ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    <td class="text-capitalize"><?= $u->name . " " . $u->first_name ?></td>
                                    <td class="blue-text"><?= (new DateTime($contribution->date))->format("d-m-Y")  ?></td>
                                    <td class="blue-text"><?= $contribution->amount ?></td>
                                    <td class="text-capitalize"><?= $adminUser->name. ' '.$adminUser->first_name ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php
                    else:?>
                    <p class="text-center">Aucune contribution</p>
                    <?php
                    endif;
                    ?>

                    <?php
                    if ($help->state):
                    ?>
                        <h3 class="text-muted text-center mb-3">Membres n'ayant pas contribué</h3>
                        <div class="col-12 text-center">
            
                        <div class="row">
                            <?php
                            foreach ($help->waitedContributions() as $contribution ):
                                $member = new Contribution();
                            $member =$contribution->getMember();
                            $user = $member->user();
                            ?>

                            <div class="col-3">
                                <div class="contribution">
                                    <img src="<?= \app\managers\FileManager::loadAvatar($user)?>" alt="<?= $user->name.' '.$user->first_name ?>">
                                    <span><?= $user->name.' '.$user->first_name ?></span>
                                </div>
                            </div>

                            <?php
                            endforeach;
                            ?>
                        </div>

                    <?php
                    endif;
                    ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php
if ($help->state):
?>
<a href="<?= Yii::getAlias("@administrator.new_contribution")."?q=".$help->id ?>" class="btn btn-primary btn-add"><i class="fas fa-plus"></i>Ajouter une nouvelle contribution</a>
<?php
endif;
$allContributions = Contribution::findAll([ 'help_id' => $help->id]);
$allMembers = Member::find()->where(['<>', 'id', $help->member_id])->all();
$allActiveMembers = Member::findAll(['active' => true]);

foreach ($allActiveMembers as $key => $activeMem) {
    # code...

}

?>
<div class="col-12 white-block m-4">
<table class="table table-hover">
<thead class="blue-grey lighten-4">
<tr>
    <th>#</th>
    <th>Membre</th>
    <th>statut</th>
    <th>Date</th>
    <th>montant versé</th>
    <th>montant restant</th>
    <th>action</th>
    <th>Administrateur</th>

</tr>

</thead>
<tbody>
<?php foreach ($allMembers as $index => $asMem): ?>
    <?php $m = Contribution::find()->where(['member_id'=>$asMem->id,'help_id'=>$help->id])->one();
    $u = $asMem->user();
    $asAdmin = $asMem->administrator();
    $asAdminUser = $asAdmin->user();
    $mleft = $help->unit_amount - $m->amount;
    $mAmount = $m->amount;
    $mStatus = ($mleft>=0)? true: false ;
    $params = [
        'q'=>$help->id,
        'm'=>$m->member_id,
    ]
    ?>
    <tr>
        <th scope="row"><?= $index + 1 ?></th>
        <td class="text-capitalize"><?= $u->name . " " . $u->first_name ?></td>
        <td class="text-capitalize"><?= $asMem->active? 'actif' : 'non-actif' ?></td>
        <td class="blue-text"><?= (new DateTime($m->date))->format("d-m-Y")  ?></td>
        <td class="text-capitalize"><?= $m->amount ?></td>
        <td class="text-capitalize"><?=$mStatus? $help->unit_amount - $m->amount : "already full"?></td>
        <td class="text-capitalize">
            <?= $m->member_id.'-'. $help->id?>
            <a href="<?= Yii::getAlias("@administrator.new_contribution")."?q=".$help->id."&?m=".$m->member_id.http_build_query($params) ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>contribuer</a>
            <a href="<?= Yii::getAlias("@administrator.new_contribution")."?q=".$help->id."&?m=".$m->member_id.http_build_query($params) ?>" class="btn btn-danger btn-sm">annuler</a>

    </td>
        <!-- <a href="<?= Yii::getAlias("@administrator.new_contribution")."?q=".$help->id."&?m=".$help->member_id.http_build_query($params) ?>" class="btn btn-primary btn-add"><i class="fas fa-plus"></i>Ajouter une nouvelle contribution</a> -->

        <td class="text-capitalize"><?= $asAdminUser->name. ' '.$asAdminUser->first_name ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
