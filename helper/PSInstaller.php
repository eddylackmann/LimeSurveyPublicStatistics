<?php
class PSInstaller {

    private static $model=null;

    public static function model() {
        
        if(self::$model == null) {
            self::$model = new self();
        }

        return self::$model;
    }

    public function installTables() 
    {
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            $oDB->createCommand()->createTable('{{PSSurveys}}', array(
                'sid' => 'int NOT NULL',
                'activated' => 'int DEFAULT 1',
                'token' => 'string NULL DEFAULT NULL',
                'begin' => 'datetime NULL DEFAULT NULL',
                'expire' => 'datetime NULL DEFAULT NULL'
            ));
            $oDB->createCommand()->createTable('{{PSLogins}}', array(
                'id' => 'pk',
                'sid' => 'int NOT NULL',
                'activated' => 'int DEFAULT 1',
                'email' => 'string NOT NULL',
                'passHash' => 'TEXT NULL DEFAULT NULL',
                'begin' => 'datetime NULL DEFAULT NULL',
                'expire' => 'datetime NULL DEFAULT NULL'
            ));
            $oDB->createCommand()->createTable('{{PSAccess}}', array(
                'id' => 'pk',
                'sid' => 'int NOT NULL',
                'time' => 'datetime NULL DEFAULT NULL',
                'type' => 'string(128) NOT NULL',
                'loginid' => 'int NULL DEFAULT NULL',
                'token' => 'string NOT NULL'
            ));
            $oTransaction->commit();
            return true;
        } catch (Exception $e) {
            $oTransaction->rollback();
            throw new CHttpException(500, $e->getMessage());
        }
    }

    public function installMenues() 
    {
        $aMenuSettings1 = [
            "name" => 'publicstatssettings',
            "title" => 'publicstatssettings',
            "menu_title" => 'Public statistic - settings',
            "menu_description" => 'Settings for this surveys public statistic',
            "menu_icon" => 'gears',
            "menu_icon_type" => 'fontawesome',
            "menu_link" => 'admin/pluginhelper/sa/sidebody',
            "permission" => 'surveysecurity',
            "permission_grade" => 'update',
            "hideOnSurveyState" => false,
            "linkExternal" => false,
            "manualParams" => ['plugin' => 'PublicStatistics', 'method' => 'insurveysettings'],
            "pjaxed" => true,
            "addSurveyId" => true,
            "addQuestionGroupId" => false,
            "addQuestionId" => false,
        ];
        $aMenuSettings2 = [
            "name" => 'publicstatsshow',
            "title" => 'publicstatsshow',
            "menu_title" => 'Public statistic - view',
            "menu_description" => 'Open public statistics',
            "menu_icon" => 'binoculars',
            "menu_icon_type" => 'fontawesome',
            "menu_link" => 'plugin/direct',
            "permission" => 'surveytexts',
            "permission_grade" => 'update',
            "hideOnSurveyState" => false,
            "linkExternal" => false,
            "manualParams" => ['plugin' => 'PublicStatistics', 'method' => 'viewdirect'],
            "pjaxed" => false,
            "addSurveyId" => true,
            "addQuestionGroupId" => false,
            "addQuestionId" => false,
        ];

        $oMenu = Surveymenu::model()->findByAttributes(['name' => 'mainmenu']);
        return SurveymenuEntries::staticAddMenuEntry($oMenu->id, $aMenuSettings1)
                && SurveymenuEntries::staticAddMenuEntry($oMenu->id, $aMenuSettings2);
    }

    public function removeTables() 
    {
        $oDB = Yii::app()->db;
        $oTransaction = $oDB->beginTransaction();
        try {
            $oDB->createCommand()->dropTable('{{PSSurveys}}');
            $oDB->createCommand()->dropTable('{{PSLogins}}');
            $oDB->createCommand()->dropTable('{{PSAccess}}');
            $oTransaction->commit();
            return true;
        } catch (Exception $e) {
            $oTransaction->rollback();
            throw new CHttpException(500, $e->getMessage());
        }
    }

    public function removeMenues() 
    {
        $oSuerveymenuEntry1 = SurveymenuEntries::model()->findByAttributes(['name' => 'slssettings']);
        $oSuerveymenuEntry2 = SurveymenuEntries::model()->findByAttributes(['name' => 'slsshow']);
        return $oSuerveymenuEntry1->delete() && $oSuerveymenuEntry2->delete();
    }

}