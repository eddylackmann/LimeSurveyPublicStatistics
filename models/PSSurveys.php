<?php
/**
 * PSSurveys class
 * Abstract class for PSSurveys model
 * this class handles the settings of the statistics for each survey
 * 
 * 
 * @author Markus Flür | LimeSurvey Team <support@limeSurvey.org>
 * @license GPL 2.0 or later
 * @category Plugin 
 * 
 * ORM for the plugin data
 * fields
 *   'sid' => 'int NOT NULL',
 *   'activated' => 'int DEFAULT 1',
 *   'token' => 'string NULL DEFAULT NULL',
 *   'begin' => 'datetime NULL DEFAULT NULL',
 *   'expire' => 'datetime NULL DEFAULT NULL'
 */

class PSSurveys extends LSActiveRecord
{
    
    /**
     * @inheritdoc
     * @return PSSurveys
     */
    public static function model($class = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($class);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{PSSurveys}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'sid';
    }

    /** @inheritdoc */
    public function rules()
    {
        $rules = parent::rules();
        return $rules;
    }

    /** @inheritdoc */
    public function relations()
    {
        return array(
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
            'logins' => array(self::HAS_MANY, 'PSLogins', 'sid'),
        );
    }

    /**
     * Format expire date 
     *
     * @return string
     */
    public function getFormattedExpiry() {
        $dateformatdetails = getDateFormatForSID($this->sid);
        Yii::import('application.libraries.Date_Time_Converter');
        $datetimeobj = new Date_Time_Converter($this->expires, 'Y-m-d H:i:s');
        return $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }

    /**
     * Format begin date 
     *
     * @return string
     */
    public function getFormattedBegin() {        
        $dateformatdetails = getDateFormatForSID($this->sid);
        Yii::import('application.libraries.Date_Time_Converter');
        $datetimeobj = new Date_Time_Converter($this->begin, 'Y-m-d H:i:s');
        return $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }

    /**
     * Checks if survey statistics has login 
     *
     * @return string
     */
    public function getHasLogins() {
        return safecount($this->logins)>0;
    }

    /**
     * Creates buttons for a gridView
     *
     * @return string
     */
    public function getButtons()
    {
        return '';
    }

    /**
     * Erstellt den Spaltenarray für die Benutzeranzeige
     *
     * @return array
     */
    public function getColums()
    {
        return [];
    }
}
