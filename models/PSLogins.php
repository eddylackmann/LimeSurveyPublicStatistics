<?php
/**
 * ORM for the plugin data
 * fields
 *  'id' => 'pk',
 *  'sid' => 'int NOT NULL',
 *  'loginid' => 'int NOT NULL',
 *  'activated' => 'int DEFAULT 1',
 *  'email' => 'string NOT NULL',
 *  'passHash' => 'TEXT NULL DEFAULT NULL',
 *  'begin' => 'datetime NULL DEFAULT NULL',
 *  'expire' => 'datetime NULL DEFAULT NULL'
 */

class PSLogins extends LSActiveRecord
{
    
    /**
     * @inheritdoc
     * @return PSLogins
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
        return '{{PSLogins}}';
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
            'survey' => array(self::BELONGS_TO, 'PSSurveys', ['sid' => 'sid']),
        );
    }

    public function getFormattedExpire() {
        $dateformatdetails = getDateFormatForSID($this->sid);
        Yii::import('application.libraries.Date_Time_Converter');
        $datetimeobj = new Date_Time_Converter($this->expire, 'Y-m-d H:i:s');
        return $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }

    public function getFormattedBegin() {
        $dateformatdetails = getDateFormatForSID($this->sid);
        Yii::import('application.libraries.Date_Time_Converter');
        $datetimeobj = new Date_Time_Converter($this->begin, 'Y-m-d H:i:s');
        return $datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
    }

    public function cryptpass($password) {
        return CPasswordHelper::hashPassword($password);
    }

    public static function verifyLogin($sid, $email, $password) {
        $oModel = self::model()->findByAttributes([ "sid" => $sid, "email" => $email]);
        if ($oModel !== null) {
            return CPasswordHelper::verifyPassword($password, $oModel->passHash);
        }
        return false;
    }

    public function generatePassword() 
    {
        $prefixArray = [
            'a','b','c','d','e','f','g','h','i','j','k','l','m',
            'n','o','p','q','r','s','t','u','v','w','x','y','z'
        ];
        $rand = rand( 0, (count($prefixArray)-1) );
        $randString = $prefixArray[$rand].$this->getRandomString(7);

        $this->passHash = $this->cryptpass($randString);
        return $randString;

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

    /**
     * Erstellt einen zufälligen String aus einem hash
     *
     * @return string
     */
    private function getRandomString($length=8)
    {
        if (is_callable('openssl_random_pseudo_bytes')) {
            $uiq = openssl_random_pseudo_bytes(128);
        } else {
            $uiq = decbin(rand(1000000, 9999999)*(rand(100, 999).rand(100, 999).rand(100, 999).rand(100, 999)));
        }
        
        $hashstring =  hash('sha256', bin2hex($uiq));
        $randstart = rand(0,(strlen($hashstring)-$length));

        return substr($hashstring, $randstart, $length);
    }
}
