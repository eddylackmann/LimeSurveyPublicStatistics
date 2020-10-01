<?php

/**
 * PSSurveyController class
 * 
 * 
 * 
 * @author Markus Flür | LimeSurvey Team <support@limeSurvey.org>
 * @license GPL 2.0 or later
 * @category Plugin 
 * 
 */
class PSSurveyController {

    private static $model=null;

    public static function model() {
        
        if(self::$model == null) {
            self::$model = new self();
        }

        return self::$model;
    }

    
    public function prepareSettingsForRendering($sid) {
        $oSurvey = PSSurveys::model()->findByPk($sid);
        if($oSurvey == null) {
            $oSurvey = new PSSurveys();
            $oSurvey->sid = $sid;
        }

        return [
            'PS' => $oSurvey->attributes,
            'isActive' => $oSurvey->survey->active=='Y',
            'sid' => $sid,
            'uselogins' => $oSurvey->hasLogins,
            'aLogins' => $oSurvey->logins,
            'data' => self::generateData($oSurvey->data)
        ];
    }

    ########################

    public static function generateData($dataField) {
        $aDataArray = json_decode($dataField, true);
        $aDataArray = $aDataArray == null ? [] : $aDataArray;

        $aDataArray = array_merge( 
            [
                'companyImage' => "/themes/admin/Sea_Green/images/logo.png",
                'contactinformation' => PSTranslator::translate('No contact set'),
                'groupByGroup' => false,
                'preprenderWordClouds' => false,
                'startColor' => false,
                'endColor' => false,
                'initialChartType' => 'bar'
            ],
            $aDataArray
        );
         
        return $aDataArray;
    }
    
}