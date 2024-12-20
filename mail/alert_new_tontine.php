<?php
$this->beginBlock('style')
?>
<style>
    body {
        font-family: 'sans-serif';
    }
    .title {
        margin-bottom: 10px;
        text-align: center;
        color: dodgerblue;
        font-size: 2rem;
    }
    .amount {
        font-size: 1.2rem;
        color: orangered;
    }

</style>
<?php $this->endBlock() ?>
<h1 class="title">Nouvelle Tontine à la mutuelle </h1>
<?php
$target = $tontine->member()->user();
$unit = $tontine->unit_amount;
?>
<div>
    Bonjour <?= $user->first_name." ".$user->name ?>, Nous vous informons qu'une nouvelle tontine a été lancé à la mutuelle de
    L'Ecole Nationale Supérieure Polytechnique de Yaoundé.
    <br>
    En effet, votre collègue <?= $target->first_name." ".$target->name ?> a crée une tontine de type <span style="color: dodgerblue">"<?=$tontineType->title?>"</span>.
    d'une valeur de <span class="amount"><?= $unit?$unit:0 ?> XAF</span>
</div>