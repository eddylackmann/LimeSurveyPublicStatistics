<?php

class PSSurveyController {

    private static $model=null;

    public static function model() {
        
        if(self::$model == null) {
            self::$model = new self();
        }

        return self::$model;
    }

    public function prepareViewForRendering($sid) {

    }

    public function prepareSettingsForRendering($sid) {
        $oSurvey = PSSurveys::model()->findByPk($sid);
        if($oSurvey == null) {
            $oSurvey = new PSSurveys();
            $oSurvey->sid = $sid;
        }

        return [
            'PS' => $oSurvey->attributes,
            'sid' => $sid,
            'uselogins' => $oSurvey->hasLogins,
            'aLogins' => $oSurvey->logins
        ];
    }
    
}