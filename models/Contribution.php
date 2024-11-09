<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contribution".
 *
 * @property int $id
 * @property int $member_id
 * @property string $date
 * @property int $state
 * @property string $created_at
 * @property int $help_id
 * @property int $administrator_id
 * @property string $amount
 *
 * @property Administrator $administrator
 * @property Help $help
 * @property Member $member
 */
class Contribution extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contribution';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'state', 'help_id', 'administrator_id'], 'integer'],
            [['date', 'created_at'], 'safe'],
            [['amount'], 'required'],
            [['amount'], 'number'],
            [['administrator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Administrator::className(), 'targetAttribute' => ['administrator_id' => 'id']],
            [['help_id'], 'exist', 'skipOnError' => true, 'targetClass' => Help::className(), 'targetAttribute' => ['help_id' => 'id']],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'date' => 'Date',
            'state' => 'State',
            'created_at' => 'Created At',
            'help_id' => 'Help ID',
            'administrator_id' => 'Administrator ID',
            'amount' => 'Amount',
        ];
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdministrator()
    {
        return Administrator::findOne($this->administrator_id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHelp()
    {
        return Help::findOne($this->help_id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return Member::findOne($this->member_id);
    }
}
