<?php
spl_autoload_register(function ($class_name) {
    if (preg_match("/^PS.*/", $class_name)) {
        if (file_exists(__DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$class_name . '.php')) {
            include __DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.$class_name . '.php';
        } elseif (file_exists(__DIR__.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$class_name . '.php')) {
            include __DIR__.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$class_name . '.php';
        } elseif (file_exists(__DIR__.DIRECTORY_SEPARATOR.'helper'.DIRECTORY_SEPARATOR.$class_name . '.php')) {
            include __DIR__.DIRECTORY_SEPARATOR.'helper'.DIRECTORY_SEPARATOR.$class_name . '.php';
        }
    }
});

class PublicStatistics extends PluginBase {
    protected $storage = 'DbStorage';    
    static protected $description = 'Allow either sharing of statistics with a link, a one time token, or with email and password';
    static protected $name = 'Public and shareable statistics';
    
    public function init()
    {
        /**
         * Here you should handle subscribing to the events your plugin will handle
         */
        $this->subscribe('beforeActivate');
        $this->subscribe('beforeDeactivate');
        $this->subscribe('newUnsecureRequest');
        $this->subscribe('newDirectRequest');
    }
    
    public function beforeActivate()
    {
        PSInstaller::model()->installTables();
        PSInstaller::model()->installMenues();
    }

    public function beforeDeactivate()
    {
        PSInstaller::model()->removeTables();
        PSInstaller::model()->removeMenues();
    }
    /**
     * Relay a direct request to the called method
     *
     * @return void
     */
    public function newDirectRequest()
    {
        $request = $this->api->getRequest();
        $oEvent = $this->getEvent();

        if ($oEvent->get('target') !== 'PublicStatistics') {
            return;
        }

        $action = $request->getParam('method');
        return call_user_func([$this, $action], $oEvent, $request);
    }

    /**
     * Relays an unsecure request to the called method
     *
     * @return void
     */
    public function newUnsecureRequest()
    {
        $request = $this->api->getRequest();
        $oEvent = $this->getEvent();

        if ($oEvent->get('target') !== 'PublicStatistics') {
            return;
        }

        $action = $request->getParam('method');
        return call_user_func([$this, $action], $oEvent, $request);
    }

    public function saveinsurveysettings($oEvent, $request) {
        $sid = $request->getPost('sid');
        
        $oSurvey = $this->getPSSurveyModel($sid);

        $activated = $request->getPost('activated', 1);
        $token = $request->getPost('token', NULL);
        $expire = $request->getPost('expire', NULL);
        $begin = $request->getPost('begin', NULL);
        
        $oSurvey->activated = $activated;
        $oSurvey->token = $token;
        $oSurvey->expire = $expire == '' ? null : $expire;
        $oSurvey->begin = $begin == '' ? null : $begin;


        $oSurvey->save();
        Yii::app()->getController()->redirect(
            Yii::app()->createUrl(
                'admin/pluginhelper/sa/sidebody', 
                ['surveyid'=> $sid, 'plugin'=>'PublicStatistics', 'method'=>'insurveysettings']
            )
        );

    }

    public function insurveysettings() {
        $sid = Yii::app()->request->getParam('surveyid');
        $aData = PSSurveyController::model()->prepareSettingsForRendering($sid);
        
        $this->registerScript('assets/publicstatisticsettings.js', LSYii_ClientScript::POS_END);

        return $this->renderPartial('insurveysettings', $aData, true);
    }

    public function storeNewLogin($oEvent, $request) {
        $sid = $request->getPost('sid');
        $email = $request->getPost('email');
        $begin = $request->getPost('begin');
        $expire = $request->getPost('expire');
        $oLogin = new PSLogins();
        $oLogin->sid = $sid;
        $oLogin->email = $email;
        $oLogin->begin = $begin == '' ? NULL : $begin;
        $oLogin->expire = $expire == '' ? NULL : $expire;
        $clearpass = $oLogin->generatePassword();
        if ($oLogin->save()) {
            $this->_sendEmail($oLogin, $clearpass);
            return $this->renderPartial('toJson', ['data'=>array_merge($oLogin->attributes,['clearpass' => $clearpass])]);
        };
        return $this->renderPartial('toJson', ['data'=>['success' => false]]);

    }

    public function viewdirect($oEvent, $request) {
        if (Yii::app()->user->isGuest){
            return $this->renderPartial('nopermissionerror',[]);
        }
        $sid = $request->getParam('surveyid');
        $oSurvey = Survey::model()->findByPk($sid);
        $oParser = new PSStatisticParser($sid);
        $aResponseDataList = $oParser->createParsedDataBlock();
        $output = $this->renderPartial('viewstats', $aResponseDataList, true);
        Yii::app()->getClientScript()->registerPackage('jquery');
        Yii::app()->getClientScript()->registerPackage('bootstrap');
        $this->registerScript('assets/viewstats/build.min/main.css');
        $this->registerScript('assets/viewstats/build/js/viewstats.js', null, LSYii_ClientScript::POS_END);
        Yii::app()->getClientScript()->render($output);
        echo $output;
        return;
    }

    public function viewunsecure($oEvent, $request) {
        
        $sid = $request->getParam('surveyid');
        $oSurvey = PSSurveys::model()->findByPk($sid);

        $token = $request->getParam('token', false);

        $email = $request->getPost('email', false);
        $password = $request->getPost('password', false);
        
        Yii::app()->getClientScript()->registerPackage('jquery');
        Yii::app()->getClientScript()->registerPackage('bootstrap');

        if ($token != $oSurvey->token && $oSurvey->token != null && !$oSurvey->hasLogins ) {
            return $this->renderPartial('nopermissionerror', []);
        }

        if ((($email == false && $password == false)
            || !PSLogins::verifyLogin($sid, $email, $password)) 
            && $oSurvey->hasLogins 
        ) {
            $output = $this->renderPartial(
                'loginunsecure', 
                [
                    'surveyname' => $oSurvey->survey->correct_relation_defaultlanguage->surveyls_title,
                    'formUrl' => Yii::app()->createUrl(
                        '/plugins/unsecure', 
                        [
                            'plugin' => 'PublicStatistics',
                            'method' => 'viewunsecure',
                            'surveyid' => $sid
                        ]
                    )
                ], 
                true
            );
            Yii::app()->getClientScript()->render($output);
            echo $output;
            return;
        }

        $oParser = new PSStatisticParser($sid);
        $aResponseDataList = $oParser->createParsedDataBlock();
        $output = $this->renderPartial('viewstats', $aResponseDataList, true);
        $this->registerScript('assets/viewstats/build.min/main.css');
        $this->registerScript('assets/viewstats/build/js/viewstats.js', null, LSYii_ClientScript::POS_END);
        Yii::app()->getClientScript()->render($output);
        echo $output;
        return;
    }

    ##########################################################################################

    private function getPSSurveyModel($sid) {
        $oModel = PSSurveys::model()->findByPk($sid);
        if($oModel == null) {
            $oModel = new PSSurveys();
            $oModel->sid = $sid;
        }

        return $oModel;
    }

    private function _sendEmail($oLogin, $clearTextPass) {
        $to = $oLogin->email;
        $from = Yii::app()->getConfig("siteadminname")." <".Yii::app()->getConfig("siteadminemail").">";
        $body = $this->renderPartial(
            'inviteEmail', 
            [
                'statisticsLink' => Yii::app()->createUrl('plugins/unsecure', [
                    'plugin' => 'PublicStatistics',
                    'method' => 'viewunsecure',
                    'sid' => $oLogin->sid
                ]),
                'survey' => $oLogin->survey->survey, 
                'password' => $clearTextPass, 
                'mainAdminInfo' => Yii::app()->getConfig("siteadminname").', '.Yii::app()->getConfig("siteadminemail")
            ], 
            true
        );
    
        $success = SendEmailMessage($body, "Invitation to the see statistics on survey", $to, $from, Yii::app()->getConfig("sitename"), true, Yii::app()->getConfig("siteadminbounce"));
        return $success;
    }

    ##########################################################################################
    /**
     * Helper function for LimeService script asset loading
     *
     * @param string $relativePathToScript
     * @return void
     */
    protected function registerScript($relativePathToScript, $parentPlugin=null, $pos=LSYii_ClientScript::POS_BEGIN)
    {
        $parentPlugin = get_class($this);

        $scriptToRegister = null;
        if (file_exists(YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        } elseif (file_exists(YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        } elseif (file_exists(Yii::app()->getBasePath().'/core/plugins/'.$parentPlugin.'/'.$relativePathToScript)) {
            $scriptToRegister = Yii::app()->getAssetManager()->publish(
                Yii::app()->getBasePath().'/core/plugins/'.$parentPlugin.'/'.$relativePathToScript
            );
        }
        Yii::app()->getClientScript()->registerScriptFile($scriptToRegister, $pos);
    }
    
    /**
     * Helper function for LimeService style asset loading
     *
     * @param string $relativePathToScript
     * @return void
     */
    protected function registerCss($relativePathToCss, $parentPlugin=null)
    {
        $parentPlugin = get_class($this);
        $pathPossibilities = [
            YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToCss,
            YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToCss,
            Yii::app()->getBasePath().'/application/core/plugins/'.$parentPlugin.'/'.$relativePathToCss
        ];
        $cssToRegister = null;
        if (file_exists(YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('userdir').'/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        } elseif (file_exists(YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                YiiBase::getPathOfAlias('webroot').'/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        } elseif (file_exists(Yii::app()->getBasePath().'/core/plugins/'.$parentPlugin.'/'.$relativePathToCss)) {
            $cssToRegister = Yii::app()->getAssetManager()->publish(
                Yii::app()->getBasePath().'/core/plugins/'.$parentPlugin.'/'.$relativePathToCss
            );
        }
        Yii::app()->getClientScript()->registerCssFile($cssToRegister);
    }
}
