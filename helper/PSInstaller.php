<?php
class PSInstaller
{

    private static $model = null;

    public static function model()
    {

        if (self::$model == null) {
            self::$model = new self();
        }

        return self::$model;
    }



    public function installTables()
    {
        
        $this->createTable('PSLogins', array(
            'id' => 'pk',
            'sid' => 'int NOT NULL',
            'activated' => 'int DEFAULT 1',
            'email' => 'string NOT NULL',
            'passHash' => 'TEXT NULL DEFAULT NULL',
            'begin' => 'datetime NULL DEFAULT NULL',
            'expire' => 'datetime NULL DEFAULT NULL'
        ));

        $this->createTable('PSAccess', array(
            'id' => 'pk',
            'sid' => 'int NOT NULL',
            'time' => 'datetime NULL DEFAULT NULL',
            'type' => 'string(128) NOT NULL',
            'loginid' => 'int NULL DEFAULT NULL',
            'token' => 'string NOT NULL'
        ));
            
        $this->createTable('PSSurveys', array(
            'sid' => 'int NOT NULL',
            'activated' => 'int DEFAULT 1',
            'token' => 'string NULL DEFAULT NULL',
            'begin' => 'datetime NULL DEFAULT NULL',
            'expire' => 'datetime NULL DEFAULT NULL',
            'data' => 'text NULL DEFAULT NULL'
        ));

        $this->createTable('PSHooks', array(
            'id' => 'pk',
            'sid' => 'int NOT NULL',
            'active' => 'int DEFAULT 1',
            'hook' => 'string NULL DEFAULT NULL',
            'hook_data' => 'text',
        ));
       
    }

    public function installMenues()
    {
        $aMenuSettings1 = [
            "name" => 'publicstatssettings',
            "title" => 'publicstatssettings',
            "menu_title" =>  PSTranslator::translate('Public Statistics'),
            "menu_description" =>  PSTranslator::translate('Settings for this surveys public statistic'),
            "menu_icon" => 'line-chart',
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

        $oMenu = Surveymenu::model()->findByAttributes(['name' => 'mainmenu']);
        return SurveymenuEntries::staticAddMenuEntry($oMenu->id, $aMenuSettings1);
    }

    /**
     * Removes all tables of the plugin.. 
     *
     * @return void
     */
    public function removeTables()
    {
        $oDB = Yii::app()->db;

        if(tableExists('PSSurveys')){
            $oDB->createCommand()->dropTable('{{PSSurveys}}');
        }
        
        if(tableExists('PSLogins')){
            $oDB->createCommand()->dropTable('{{PSLogins}}');
        }
        if(tableExists('PSAccess')){
            $oDB->createCommand()->dropTable('{{PSAccess}}');
        }


        return true;
    }

    public function removeMenues()
    {
        $result = false;

        $oSuerveymenuEntry = SurveymenuEntries::model()->findByAttributes(['name' => 'publicstatssettings']);

        if($oSuerveymenuEntry){
            $result = $oSuerveymenuEntry->delete();
        }
        
        return $result;
        
    }

    /**
     * Run Plugin unpdates
     *
     * @return boolean
     */
    public function proccessUpdate()
    {
        $result = false;
        $oDB = Yii::app()->db;
        $result = $this->createTable('PSHooks', array(
            'id' => 'pk',
            'sid' => 'int NOT NULL',
            'active' => 'int DEFAULT 1',
            'hook' => 'string NULL DEFAULT NULL',
            'hook_data' => 'text',
        ));
       
        if(tableExists('PSAddons')){
            $oDB->createCommand()->dropTable('{{PSAddons}}');
        }
         
        if ($result) {

            Yii::app()->setFlashMessage("Public Statistics modules updated", 'success');
        }

        return $result;
    }


    /**
     * Create database table 
     *
     * @param string $tableName
     * @param array $arguments
     * @return bool
     */
    private function createTable(string $tableName,array $arguments){
        
        $result = false;

        if(!tableExists($tableName)){
            
            $oDB = Yii::app()->db;
            $oTransaction = $oDB->beginTransaction();
            try {

                $oDB->createCommand()->createTable('{{'.$tableName.'}}', $arguments);
                $oTransaction->commit();
                
                $result =  true;

            } catch (Exception $e) {
                $oTransaction->rollback();
                throw new CHttpException(500, $e->getMessage());
            }
        }

        return $result;
    }

}
