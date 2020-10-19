<?php

/**
 * Class PSHooks
 * Additional hooks for statistics module
 * 
 * @author Eddy Lackmann <eddy.lackmann@limeSurvey.org>
 * @license GPL 2.0 or later
 *
 * 
 */

class PSHooks extends LSActiveRecord
{

    /**
     * @inheritdoc
     * @return PSHooks
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
        return '{{PSHooks}}';
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
            'survey' => array(self::BELONGS_TO, 'Survey', 'sid'),
        );
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
     * 
     *
     * @return array
     */
    public function getColums()
    {
        return [];
    }
}
