<?php
/**
 * Created by PhpStorm.
 * User: medric
 * Date: 23/12/18
 * Time: 20:05
 */

namespace app\controllers;

use app\managers\MemberSessionManager;
use app\models\BankAccount;
use app\models\forms\UpdateSocialInformationForm;
use app\models\forms\UpdatePasswordForm;
use app\models\forms\NewMemberForm;
use app\managers\RedirectionManager;
use app\models\Member;
use app\models\User;
use app\models\Saving;
use app\models\forms\IdForm;
use app\models\forms\NewBorrowingForm;
use app\models\forms\NewRefundForm;
use app\models\forms\NewSavingForm;
use app\models\forms\NewSessionForm;
use app\models\Administrator;
use app\models\Help_type;
use app\models\Contribution;
use app\models\Session;
use app\models\Help;
use app\models\Exercise;
use app\models\Borrowing;
use Stripe\StripeClient;
use yii\web\Controller;
use yii\web\UploadedFile;
use DateTime;
use Yii;
use yii\base\Security;
use yii\data\Pagination;
use app\managers\FileManager;
use app\managers\MailManager;
use app\models\ContributionTontine;
use app\models\forms\NewTontineForm;
use app\models\Tontine;
use app\models\TontineType;
use yii\web\Response;
use Stripe\Stripe;
use Stripe\Charge;

class MemberController extends Controller
{

    public $layout = "member_base";
    public $defaultAction = "accueil";
    public $user;
    public $member;
//*********************************************************************************** */
    public function beforeAction($action)
    {
        if (!\Yii::$app->user->getIsGuest()) {
            $user = User::findOne(\Yii::$app->user->getId());

            if ($user->type === "MEMBER")
            {
                $member = Member::findOne(['user_id'=> $user->id]);

                $this->user = $user;
                $this->member = $member;
                $this->view->params = ['user'=> $this->user,'member'=> $this->member];

                return parent::beforeAction($action);
            } // TODO: Change the autogenerated stub
            elseif ( $user->type === "ADMINISTRATOR") {
                {
                    \Yii::$app->response->redirect("@administrator.home");
                }
            }
            else
                return RedirectionManager::abort($this);
        }
        else
        {
            \Yii::$app->response->redirect("@guest.connection");
        }

    }
/***************************action accueil************************************************* */
    public function actionAccueil() {
        MemberSessionManager::setHome();
        $session = Session::findOne(['active' => true]);
        $idModel = new IdForm();
        if ($session)
            $idModel->id = $session->id;
        $model = new NewSessionForm();
        return $this->render('home',compact('session','model','idModel'));
    }
/*********************************action de deconnexion à modifier ************************************************* */
    public function actionDeconnexion() {
        if (\Yii::$app->request->post()) {
            \Yii::$app->user->logout();
            return $this->redirect('@guest.connection');
        }
        {
            return $this->redirect('@member.home');
        }
    }
/********************************action profil *************************************************** */
    public function actionProfil() {
        
        MemberSessionManager::setProfil();
            $user = User::findOne(\Yii::$app->user->getId());
                $member = Member::findOne(['user_id'=> $user->id]);

                $this->user = $user;
                $this->member = $member;
                $this->view->params = ['user'=> $this->user,'member'=> $this->member];
                return $this->render('profil',['member'=> $member, 'user'=> $user]);
            
        
    }
/*******************************profil Membre ******************************************************* */
    public function actionProfilMembre($m=0, $n=0) {
        MemberSessionManager::setMembers();
       if($m){
            $member = Member::findOne($n);
            $user = User::findOne($m);
            
            return $this->render('profilmembre',['member'=> $member, 'user'=> $user]);
       }
    }
/****************************profil administrateur ******************************************************* */
    public function actionProfilAdmin($m=0, $n=0) {
        MemberSessionManager::setAdministrators();
        if($m){
             $admin = Administrator::findOne($n);
             $user = User::findOne($m);
             
             return $this->render('profiladmin',['admin'=> $admin, 'user'=> $user]);
        }
     }
/*****************************modifier Profil**************************************************** */
    public function actionModifierProfil() {
        MemberSessionManager::setProfil();
        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);
        $socialModel = new UpdateSocialInformationForm();
        $passwordModel = new UpdatePasswordForm();

        $socialModel->attributes = [
            'username' => $this->member->username,
            'name' => $this->user->name,
            'first_name' => $this->user->first_name,
            'tel' => $this->user->tel,
            'email' => $this->user->email,
            'address' => $this->user->address,
        ];

        return $this->render('modifier_profil',compact('socialModel','passwordModel'));
        
    }
/*****************************Enregister les modifications du profil ****************************************************** */
    public function actionEnregistrerModifierProfil() {
        MemberSessionManager::setProfil();
        if (\Yii::$app->request->getIsPost()) {
            $socialModel = new UpdateSocialInformationForm();
            $passwordModel = new UpdatePasswordForm();

            if ($socialModel->load(\Yii::$app->request->post()) &&  $socialModel->validate()) {
                $this->user->name = $socialModel->name;
                $this->user->first_name = $socialModel->first_name;
                $this->user->tel = $socialModel->tel;
                $this->user->email = $socialModel->email;
                $this->user->address = $socialModel->address;
              /*  if (UploadedFile::getInstance($socialModel,"avatar"))
                    $this->user->avatar = FileManager::storeAvatar( UploadedFile::getInstance($socialModel,"avatar"),$socialModel->username,"MEMBER");
*/
            if (UploadedFile::getInstance($socialModel,"avatar"))
                {
                     $this->user->avatar=UploadedFile::getInstance($socialModel,'avatar');
                    $this->user->avatar->saveAs('img/upload/'.$this->user->avatar->basename.'.'.$this->user->avatar->extension);
                    $socialModel->avatar=$this->user->avatar->basename.'.'.$this->user->avatar->extension;
                }
            else{
                    $this->user->avatar=null;
                }
 
                $this->user->save();
                $this->member->username = $socialModel->username;
                $this->member->save();
                return $this->redirect("@member.profil");
            }
            else
                return $this->render('modifier_profil',compact('socialModel','passwordModel'));

        }
        else
        {
            return RedirectionManager::abort($this);;
        }

    }
/*******************Modifier le mots de passe ******************************************* */
    public function actionModifierMotDePasse() {
        MemberSessionManager::setProfil();
        if (\Yii::$app->request->getIsPost()) {
            $socialModel = new UpdateSocialInformationForm();
            $socialModel->attributes = [
                'id' => $this->user->id,
                'username' => $this->member->username,
                'name' => $this->user->name,
                'first_name' => $this->user->first_name,
                'tel' => $this->user->tel,
                'email' => $this->user->email,
                'address' => $this->user->address,
            ];

            $passwordModel = new UpdatePasswordForm();
            if ($passwordModel->load(\Yii::$app->request->post()) &&  $passwordModel->validate()) {
                if ($this->user->validatePassword($passwordModel->password)) {
                    $this->user->password = Yii::$app->getSecurity()->generatePasswordHash($passwordModel->new_password);
                    $this->user->save();
                    return $this->redirect("@member.profil");
                }
                else {
                    $passwordModel->addError('password','Le mot de passe ne correspond pas');
                    return $this->render('modifier_profil',compact('socialModel','passwordModel'));
                }

            }
            else
                return $this->render('modifier_profil',compact('socialModel','passwordModel'));

        }
        else
            return RedirectionManager::abort($this);;
    }
/****************************action sur les types d'aide niveau ******************************************************* */
    public function actionTypesAide() {
        MemberSessionManager::setHelps();
        $helptype = Help_type::find()->all();
        return $this->render('types_aide',compact('helptype'));
    }
/***************************par rapport au membre ********************************************* */
    public function actionMembres() {
        MemberSessionManager::setMembers();
        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);
        $members = Member::findBySql('Select * from member where id != '.$member->id)->all();
        return $this->render('members',compact('members'));
    }
/***************************par rapport à l'administration *********************************************************** */
    public function actionAdministrators() {
        MemberSessionManager::setAdministrators();
        $administrators = Administrator::find()->all();
        return $this->render('administrators',compact('administrators'));
    }
/************************action sur les épargnes *************************************************** */
    public function actionEpargnes() {
        MemberSessionManager::setHome("epargnes");
        $model = new NewSavingForm();
        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);
        $query = Exercise::find();
        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query->count(),
        ]);

        $exercises = $query->orderBy(['created_at'=> SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render("epargnes",compact("exercises","pagination","member"));
    }
/*********************************action emprunts************************************************************** */
    public function actionEmprunts() {
        MemberSessionManager::setHome("emprunts");
        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);
        $model = new NewBorrowingForm();

        $query = Exercise::find();
        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query->count(),
        ]);

        $exercises = $query->orderBy(['created_at'=> SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render("emprunts",compact("exercises","pagination","member"));
    }
    /*************************action sur les contributions ****************************************************** */
    public function actionContributions() {
        MemberSessionManager::setHome("contributions");
        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);
        $contributions = Contribution::find()->where(['member_id'=> $member->id])->all();
        return $this->render('contributions',compact('contributions'));
    }
/**********************************action sur les sessions *************************************************************************** */
    public function actionSessions() {
        MemberSessionManager::setHome("sessions");
        $query = Exercise::find();
        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query->count(),
        ]);

        $exercises = $query->orderBy(['created_at'=> SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        return $this->render("sessions",compact('exercises','pagination'));
    }
/*******************************detail de la session utilisateur **************************************************************** */
    public function actionDetailSession($q = 0) {
        MemberSessionManager::setHome("sessions");
        if ($q) {
            $session = Session::findOne($q);
            if ($session) {
                return $this->render("detailsession",compact('session'));
            }
            else
                return RedirectionManager::abort($this);
        }
        else
            return RedirectionManager::abort($this);
    }
/******************************action sur les exercices ********************************************************************************* */
    public function actionExercises() {
        MemberSessionManager::setHome("exercises");
        $query = Exercise::find();

        $user = User::findOne(\Yii::$app->user->getId());
        $member = Member::findOne(['user_id'=> $user->id]);

        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query->count(),
        ]);
        $exercises = $query->orderBy(['created_at'=> SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        return $this->render("exercises",compact('exercises','pagination',"member"));
    }
/***************************actions sur les Aides *************************************************************** */
    public function actionAides() {
        MemberSessionManager::setHome("helps");

        $activeHelps = Help::findAll(['state' => true]);

        $query = Help::find()->where(['state' => false])->orderBy('created_at',SORT_DESC);

        $pagination = new Pagination([
            'defaultPageSize' => 9,
            'totalCount' => $query->count(),
        ]);

        $helps = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render("helps",compact("helps",'pagination',"activeHelps"));
    }

    /*****************************detail de l'aide **************************************************************** */
    public function actionDetailAide($q=0) {
        if ($q) {
            $help = Help::findOne($q);
            if ($help) {

                return $this->render("help_details",compact("help"));
            }
            else
                return RedirectionManager::abort($this);
        }
        else
            return RedirectionManager::abort($this);
    }
/**************************payement***************************************************************/

    public function  actionPay(){

        $model = new  BankAccount();
        if($model->load(Yii::$app->request->post()) && $model->validate()) {
            Stripe::setApiKey(Yii::$app->params['stripeSecretKey']);

            try {
                $charge = Charge::create([
                    'amount' => 1000,
                    'currency' => 'xaf',
                    'source' => $model->cardNumber,
                    'description' => 'paiment pour le Inscription',
                ]);
                Yii::$app->session->setFlash('success', 'paiement reussi !');
                    return  $this ->redirect(['member/success', 'transactionNumber'=> $charge->id]);

            } catch (\Stripe\Execption\CardExecption $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                return  $this->redirect(['member/error']);
            }

        }
        return $this->render('pay', [
            'model' => $model,
        ]);
    }


    public function  actionSuccess(){
        return $this->render('success');
    }

    /*******************************************Type de Tontine********************************************************************************************/


    public function actionTypesTontine()
    {
        MemberSessionManager::setTontine();
        $tontineTypes = TontineType::find()->where(['active' => true])->all();

        return $this->render('tontine_types', compact('tontineTypes'));
    }

    /*****************************nouvelle  tontine côté membre ******************************************* */
    public function actionNouvelleTontine()
    {
        MemberSessionManager::setHome("tontine");
        $model = new NewTontineForm();
        return $this->render("new_Tontine", compact("model"));
    }


/********************************ajouter une tontine ********************************************************** */
    public function actionAjouterTontine()
    {
        if (!Yii::$app->request->getIsPost()) {
            return RedirectionManager::abort($this);
        }

        $model = new NewTontineForm();
        if (!$model->load(Yii::$app->request->post()) || !$model->validate()) {
            return $this->render("new_tontine", compact("model"));
        }

        $member = Member::findOne($model->member_id);
        $tontine_type = TontineType::findOne($model->tontine_type_id);

        if (!$member || !$tontine_type || !$member->active) {
            return RedirectionManager::abort($this);
        }

        if ($this->hasActiveBorrowing($member->id)) {
            $model->addError("member_id", "Ce membre doit rembourser son emprunt avant de bénéficier d'une aide.");
            return $this->render("new_tontine", compact("model"));
        }

        if (!$this->isDateValid($model->limit_date)) {
            $model->addError("limit_date", "Le délai minimum est d'un mois.");
            return $this->render("new_tontine", compact("model"));
        }

        $tontine = $this->createTontine($model, $tontine_type, $member);
        $this->sendNotifications($tontine, $member, $tontine_type);

        // Set the success flash message
        Yii::$app->session->setFlash('success', 'Vous avez été ajoutée a la tontine avec succès.');

        return $this->redirect("@member.tontine_types");
    }

    private function hasActiveBorrowing($member_id)
    {
        return Borrowing::findOne(['member_id' => $member_id, 'state' => true]) !== null;
    }
    
    private function isDateValid($limit_date)
    {
        $currentTimestamp = (new DateTime())->getTimestamp();
        $limitTimestamp = (new DateTime($limit_date))->getTimestamp();
        return $currentTimestamp <= $limitTimestamp + 86400000 * 30;
    }
    
    private function createTontine($model, $tontine_type, $member)
    {
        $tontine = new Tontine();
        $tontine->limit_date = $model->limit_date;
        $tontine->tontine_type_id = $tontine_type->id;
        $tontine->member_id = $member->id;
        $tontine->comments = $model->comments;
        $tontine->state = true;
        $tontine->administrator_id = 1;

        $members = Member::find()->where(['!=', 'id', $member->id])->andWhere(['active' => true])->all();
        $unit_amount = (int) ceil((double) ($tontine_type->amount) / count($members));
        $tontine->amount = $unit_amount * count($members);
        $tontine->unit_amount = $unit_amount;
        $tontine->save();

        foreach ($members as $member) {
            $contribution = new ContributionTontine();
            $contribution->state = false;
            $contribution->member_id = $member->id;
            $contribution->tontine_id = $tontine->id;
            $contribution->save();
        }

        return $tontine;
    }
    
    private function sendNotifications($tontine, $member, $tontine_type)
    {
        $help = Help::findOne(['member_id' => $member->id]);
        if ($help !== null) {
            MailManager::alert_contributeur($member->user(), $member, $help);
        }

        $members = Member::find()->where(['!=', 'id', $tontine->member_id])->andWhere(['active' => true])->all();
        foreach ($members as $member) {
            MailManager::alert_new_tontine($member->user(), $member, $tontine, $tontine_type);
            if ($help !== null) {
                MailManager::alert_contributeur($member->user(), $member, $help);
            }
        }
    }

}