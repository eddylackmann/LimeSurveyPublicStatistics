<?php

/**
 * PSHelper class
 * 
 * Collections of some helper functionalities
 * 
 * 
 * @author LimeSurvey Team <support@limeSurvey.org>
 * @license GPL 2.0 or later
 * @category Plugin 
 * 
 */

class PSHooksHelper
{

    public static $availableHooks = ["addRelatedSurveyResponses"];

    /**
     * this function check and append hooks data settings from some addon
     *
     * @param string $hookName
     * @param int $surveyId
     * @param array $hooksData
     * @return array
     */
    public static function appendHooks($hookName, $surveyId, $hooksData)
    {

        $result = false;

        if (self::isHookValid($hookName, $hooksData)) {
            $hook = PSHooks::model()->findByAttributes(["sid" => $surveyId, "hook" => $hookName]);
            if (!$hook) {
                $hook = new PSHooks();
                $hook->hook = $hookName;
                $hook->sid = $surveyId;
            }

            $hook->hook_data = json_encode($hooksData);

            $result = $hook->save();
        }

        return $result;
    }


    /**
     * This function delete hook data 
     *
     * @param string $hookName
     * @param int $surveyID
     * @return bool
     */
    public static function deleteHooks($hookName, $surveyId = 0)
    {

        if (self::isHookValid($hookName)) {
            if ($surveyId != 0) {
                $hook = PSHooks::model()->findByAttributes(["sid" => $surveyId, "hook" => $hookName]);
                if ($hook) {
                    return $hook->delete();
                }
            } else {
                $hook = PSHooks::model()->findAllByAttributes(["hook" => $hookName]);
                if ($hook) {
                    return $hook->deleteAll();
                }
            }
        }

        return false;
    }

    /**
     * This function retrieve and format hook data for specified survey
     * 
     *
     * @param int $sid
     * @return array
     */
    public static function prepareHookData($sid)
    {
        $result = [];
        $parsed = [];
        $fields = [];

        $hook = PSHooks::model()->findByAttributes(["sid" => $sid, "hook" => "addRelatedSurveyResponses", "active" => 1]);
        $data = [];
        if ($hook) {
            $data = json_decode($hook->hook_data);
            foreach ($data as $survey) {
                $oParser = new PSStatisticParser($survey->surveyId);
                $aResponseDataList = $oParser->createParsedDataBlockWithHook();
                $parsed[] = $aResponseDataList;
                foreach ($survey->common as $common) {
                    $fields[$common->fieldname] = [
                        "fieldname" => $common->fieldname,
                        "origin" => $common->origin_fieldname
                    ];
                }
            }
        }


        if ($parsed) {
            foreach ($parsed as $parse) {
                if ($parse["questiongroups"]) {

                    foreach ($parse["questiongroups"] as $group) {
                        if ($group) {
                            foreach ($group as $g) {
                                if (isset($fields[$g["fieldname"]])) {
                                    $g["origin_fieldname"] = $fields[$g["fieldname"]]['origin'];
                                    $result[] = $g;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }


    /**
     *This function merge some hook data into the actual statistics data 
     *
     * @param [type] $surveyData
     * @param [type] $hookData
     * @return void
     */
    public static function mergeHookdata($surveyData, $hookData)
    {
        $result = [];
        foreach ($surveyData as $group) {
            if ($group) {
                foreach ($group as $gp) {
                }
            }
        }
        $result["hookdata"] = $hookData;
        return $result;
    }

    /**
     * Check if Hook is valid 
     *
     * @param string $hook
     * @return boolean
     */
    private static function isHookValid($hook)
    {
        //Todo: check array index definition for each hook
        return in_array($hook, self::$availableHooks);
    }
}
