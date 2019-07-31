<?php
/**
 * ORM for the plugin data
 * fields
 *  'id' => 'pk',
 *  'sid' => 'int NOT NULL',
 *  'time' => 'datetime NULL DEFAULT NULL',
 *  'type' => 'string(128) NOT NULL',
 *  'loginid' => 'int NULL DEFAULT NULL',
 *  'token' => 'string NOT NULL'
 */

class PSAccess extends LSActiveRecord
{
    
    /**
     * @inheritdoc
     * @return PSAccess
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
        return '{{PSAccess}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'id';
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
            'survey' => array(self::BELONGS_TO, 'PSSurveys', 'sid'),
            'login' => array(self::HAS_ONE, 'PSLogins', ['loginid' => 'id']),
        );
    }

    public function getFormattedAccessTime() {
        Yii::import('application.libraries.Date_Time_Converter');
        $datetimeobj = new Date_Time_Converter($this->time, 'Y-m-d H:i:s');
        return $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
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
        return [
            'formattedAccessTime',
            'id',
            'sid',
            'type',
            'loginid',
            'token',
        ];
    }
}
