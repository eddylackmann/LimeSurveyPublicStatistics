<?php
spl_autoload_register(function ($class_name) {
    if (preg_match("/^PS.*/", $class_name)) {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class_name . '.php';
        } elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $class_name . '.php';
        } elseif (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $class_name . '.php')) {
            include __DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . $class_name . '.php';
        }
    }
});

class PublicStatistics extends PluginBase
{
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

    public function saveinsurveysettings($oEvent, $request)
    {
        $sid = $request->getPost('sid');

        $oSurvey = $this->getPSSurveyModel($sid);

        $activated = $request->getPost('activated', 1);
        $token = $request->getPost('token', NULL);
        $expire = $request->getPost('expire', NULL);
        $begin = $request->getPost('begin', NULL);
        $data = $request->getPost('data', []);

        $oSurvey->activated = $activated;
        $oSurvey->token = $token;
        $oSurvey->expire = $expire == '' ? null : $expire;
        $oSurvey->begin = $begin == '' ? null : $begin;
        $oSurvey->data = json_encode($data);


        $oSurvey->save();
        Yii::app()->getController()->redirect(
            Yii::app()->createUrl(
                'admin/pluginhelper/sa/sidebody',
                ['surveyid' => $sid, 'plugin' => 'PublicStatistics', 'method' => 'insurveysettings']
            )
        );
    }

    public function insurveysettings()
    {
        $sid = Yii::app()->request->getParam('surveyid');
        $aData = PSSurveyController::model()->prepareSettingsForRendering($sid);

        $this->registerScript('assets/publicstatisticsettings.js', LSYii_ClientScript::POS_END);

        return $this->renderPartial('insurveysettings', $aData, true);
    }

    public function storeNewLogin($oEvent, $request)
    {
        $sid = $request->getPost('sid');
        $email = $request->getPost('email');
        $begin = $request->getPost('begin');
        $expire = $request->getPost('expire');
        $data = $request->getPost('data');
        $oLogin = new PSLogins();
        $oLogin->sid = $sid;
        $oLogin->email = $email;
        $oLogin->begin = $begin == '' ? NULL : $begin;
        $oLogin->expire = $expire == '' ? NULL : $expire;
        $clearpass = $oLogin->generatePassword();
        if ($oLogin->save()) {
            $this->_sendEmail($oLogin, $clearpass);
            return $this->renderPartial('toJson', ['data' => array_merge($oLogin->attributes, ['clearpass' => $clearpass])]);
        };
        return $this->renderPartial('toJson', ['data' => ['success' => false]]);
    }

    public function getDataDirect($oEvent, $request)
    {
        if (Yii::app()->user->isGuest || $oEvent->getEventName() !== 'newDirectRequest') {
            return $this->renderPartial('toJson', ['data' => []]);
        }
        $sid = $request->getParam('surveyid');

        $oParser = new PSStatisticParser($sid);
        $aResponseDataList = $oParser->createParsedDataBlock();

        return $this->renderPartial('toJson', ['data' => $aResponseDataList]);
    }

    public function viewdirect($oEvent, $request)
    {
        if (Yii::app()->user->isGuest || $oEvent->getEventName() !== 'newDirectRequest') {
            return $this->renderPartial('nopermissionerror', []);
        }

        $sid = $request->getParam('surveyid');

        $oSurvey = PSSurveys::model()->findByPk($sid);
        if ($oSurvey == null || $oSurvey->survey->active !== 'Y') {
            return $this->errorSurveyNotActive();
        }
        $output = $this->renderPartial(
            'viewstats',
            [
                'getDataUrl' => Yii::app()->createUrl(
                    'plugins/direct',
                    [
                        "plugin" => "PublicStatistics",
                        'method' => "getDataDirect",
                        'surveyid' => $sid
                    ]
                ),
                'theme' => $oSurvey->survey->template,
                'wordCloudSettings' => PSWordCloudSettings::getSettings(),
                'surveyData' => PSSurveyController::generateData($oSurvey->data)
            ],
            true
        );

        Yii::app()->getClientScript()->registerPackage('jspdf');
        $oTemplate = Template::model()->getInstance($oSurvey->survey->template);
        Yii::app()->getClientScript()->registerPackage($oTemplate->sPackageName, LSYii_ClientScript::POS_BEGIN);
        $this->registerCss('assets/viewstats/build.min/css/main.css');
        $this->registerScript('assets/viewstats/build/js/viewstats.js', null, LSYii_ClientScript::POS_END);
        Yii::app()->getClientScript()->render($output);
        echo $output;
        return;
    }

    public function getDataUnsecure($oEvent, $request)
    {

        $sid = $request->getParam('surveyid');
        $timeCheckParam = $request->getParam('timecheck');

        $cookienameCheck = 'LS' . hash('adler32', $sid . 'secureHash');
        $cookienameSid = 'LS' . hash('adler32', $sid . 'currentSID');

        $timecheck = isset($_COOKIE[$cookienameCheck]) ? $_COOKIE[$cookienameCheck] : null;
        $sidcheck = isset($_COOKIE[$cookienameSid]) ? $_COOKIE[$cookienameSid] : null;

        $token = $request->getParam('token');
        $oSurvey = PSSurveys::model()->findByPk($sid);

        if (($timecheck == null ||  $sidcheck == null)
            && ($timecheck != $timeCheckParam || $sid != $sidcheck)
        ) {
            return $this->renderPartial('toJson', ['data' => ['data' => [], 'questiongroups' => []]]);
        }

        if (
            $oSurvey == null
            || ($token !== $oSurvey->token && $oSurvey->token != null)
        ) {
            return $this->renderPartial('toJson', ['data' => ['data' => [], 'questiongroups' => []]]);
        }

        $oParser = new PSStatisticParser($sid);
        $aResponseDataList = $oParser->createParsedDataBlock();

        return $this->renderPartial('toJson', ['data' => $aResponseDataList]);
    }

    public function viewunsecure($oEvent, $request)
    {

        $sid = $request->getParam('surveyid');
        $oSurvey = PSSurveys::model()->findByPk($sid);

        if ($oSurvey == null || $oSurvey->survey->active !== 'Y') {
            return $this->errorSurveyNotActive();
        }

        $randomToken = crypt(date('YHM'), Yii::app()->getConfig('sitename'));

        // setcookie('LS'.hash('adler32', $sid.'secureHash'), $randomToken, 0, '/', $_SERVER['SERVER_NAME'], true);
        // setcookie('LS'.hash('adler32', $sid.'currentSID'), $sid, 0, '/', $_SERVER['SERVER_NAME'], true);
        setcookie('LS' . hash('adler32', $sid . 'secureHash'), $randomToken, ($_SERVER['REQUEST_TIME'] + 5 * 60), '/', "", true);
        setcookie('LS' . hash('adler32', $sid . 'currentSID'), $sid, ($_SERVER['REQUEST_TIME'] + 5 * 60), '/', "", true);

        $oTemplate = Template::getInstance($oSurvey->survey->template);
        $token = $request->getParam('token', false);

        $email = $request->getPost('email', false);
        $password = $request->getPost('password', false);

        Yii::app()->getClientScript()->registerPackage('jquery');
        Yii::app()->getClientScript()->registerPackage('bootstrap');
        Yii::app()->clientScript->registerPackage($oTemplate->sPackageName, LSYii_ClientScript::POS_BEGIN);

        if (
            (
                (
                    ($email == false && $password == false)
                    || !PSLogins::verifyLogin($sid, $email, $password))
                && $oSurvey->hasLogins)
            || ($token !== $oSurvey->token
                && $oSurvey->token != null
                && !$oSurvey->hasLogins)
        ) {

            $output = $this->renderPartial(
                'loginunsecure',
                [
                    'oSurvey' => $oSurvey,
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
        $output = $this->renderPartial(
            'viewstats',
            [
                'getDataUrl' => Yii::app()->createUrl(
                    'plugins/unsecure',
                    [
                        "plugin" => "PublicStatistics",
                        'method' => "getDataUnsecure",
                        'surveyid' => $sid,
                        'timecheck' => $randomToken,
                        'token' => $oSurvey->token
                    ]
                ),
                'theme' => $oSurvey->survey->template,
                'wordCloudSettings' => PSWordCloudSettings::getSettings(),
                'surveyData' => PSSurveyController::generateData($oSurvey->data)
            ],
            true
        );

        Yii::app()->getClientScript()->registerPackage('jspdf');
        $this->registerCss('assets/viewstats/build.min/css/main.css');
        $this->registerScript('assets/viewstats/build/js/viewstats.js', null, LSYii_ClientScript::POS_END);
        Yii::app()->getClientScript()->render($output);
        echo $output;
        return;
    }

    public function deleteLoginRow($oEvent, $oRequest)
    {
        $sid = $oRequest->getPost('sid');
        $loginId = $oRequest->getPost('loginId');

        $oLoginModel = PSLogins::model()->findByPk($loginId);

        return $this->renderPartial('toJson', ['data' => ['success' => $oLoginModel->delete()]]);
    }

    public function resetLoginPassword($oEvent, $oRequest)
    {
        $sid = $oRequest->getPost('sid');
        $loginId = $oRequest->getPost('loginId');

        $oLoginModel = PSLogins::model()->findByPk($loginId);
        $clearpass = $oLoginModel->generatePassword();
        $this->_sendEmail($oLoginModel, $clearpass);

        return $this->renderPartial('toJson', ['data' => ['success' => true, 'clearPass' => $clearpass]]);
    }

    private function errorSurveyNotActive()
    {
        return $this->renderPartial('errorNotActive', []);
    }

    ##########################################################################################

    private function getPSSurveyModel($sid)
    {
        $oModel = PSSurveys::model()->findByPk($sid);
        if ($oModel == null) {
            $oModel = new PSSurveys();
            $oModel->sid = $sid;
        }

        return $oModel;
    }

    private function _sendEmail($oLogin, $clearTextPass)
    {
        $to = $oLogin->email;
        $from = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
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
                'mainAdminInfo' => Yii::app()->getConfig("siteadminname") . ', ' . Yii::app()->getConfig("siteadminemail")
            ],
            true
        );

        $success = SendEmailMessage($body, "Invitation to the see statistics on survey", $to, $from, Yii::app()->getConfig("sitename"), true, Yii::app()->getConfig("siteadminbounce"));
        return $success;
    }

    ##########################################################################################
    /**
     * Adding a script depending on path of the plugin
     * This method checks if the file exists depending on the possible different plugin locations, which makes this Plugin LimeSurvey Pro safe.
     *
     * @param string $relativePathToScript
     * @param integer $pos See LSYii_ClientScript constants for options, default: LSYii_ClientScript::POS_BEGIN
     * @return void
     */
    protected function registerScript($relativePathToScript, $parentPlugin=null, $pos = LSYii_ClientScript::POS_BEGIN)
    {
        $parentPlugin = get_class($this);
        $pathPossibilities = [
            YiiBase::getPathOfAlias('userdir') . '/plugins/' . $parentPlugin . '/' . $relativePathToScript,
            YiiBase::getPathOfAlias('webroot') . '/plugins/' . $parentPlugin . '/' . $relativePathToScript,
            Yii::app()->getBasePath() . '/application/core/plugins/' . $parentPlugin . '/' . $relativePathToScript,
            //added limesurvey 4 compatibilities
            YiiBase::getPathOfAlias('webroot') . '/upload/plugins/' . $parentPlugin . '/' . $relativePathToScript,
        ];

        $scriptToRegister = null;
        foreach ($pathPossibilities as $path) {
            if (file_exists($path)) {
                $scriptToRegister = Yii::app()->getAssetManager()->publish($path);
            }
        }

        Yii::app()->getClientScript()->registerScriptFile($scriptToRegister, $pos);
    }

    /**
     * Adding a stylesheet depending on path of the plugin
     * This method checks if the file exists depending on the possible different plugin locations, which makes this Plugin LimeSurvey Pro safe.
     *
     * @param string $relativePathToCss
     * @return void
     */
    protected function registerCss($relativePathToCss, $parentPlugin = null)
    {
        $parentPlugin = get_class($this);

        $pathPossibilities = [
            YiiBase::getPathOfAlias('userdir') . '/plugins/' . $parentPlugin . '/' . $relativePathToCss,
            YiiBase::getPathOfAlias('webroot') . '/plugins/' . $parentPlugin . '/' . $relativePathToCss,
            Yii::app()->getBasePath() . '/application/core/plugins/' . $parentPlugin . '/' . $relativePathToCss,
            //added limesurvey 4 compatibilities
            YiiBase::getPathOfAlias('webroot') . '/upload/plugins/' . $parentPlugin . '/' . $relativePathToCss,
        ];

        $cssToRegister = null;
        foreach ($pathPossibilities as $path) {
            if (file_exists($path)) {
                $cssToRegister = Yii::app()->getAssetManager()->publish($path);
            }
        }

        Yii::app()->getClientScript()->registerCssFile($cssToRegister);
    }

}
