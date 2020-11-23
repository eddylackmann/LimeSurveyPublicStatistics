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
                    return PSHooks::model()->deleteAllByAttributes(["hook" => $hookName]);
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
                $aResponseDataList = $oParser->createParsedDataBlockWithHook("addRelatedSurveyResponses");
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
                                    $result[$fields[$g["fieldname"]]['origin']] = $g;
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

        //print_r($hookData);

        //die();

        foreach ($surveyData['questiongroups'] as $key => $questionGroup) {

            if ($questionGroup) {
                foreach ($questionGroup as $subKey => $question) {

                    if (isset($hookData[$question['fieldname']])) {
                        $question['additionalHook'] = true;
                        $additionalHook = $hookData[$question['fieldname']];

                        //push hook answers 
                        foreach ($additionalHook['answers'] as $answer) {
                            $question['answers'][] = $answer;
                        }

                        //update graphs data
                        foreach ($additionalHook['countedValueArray'] as $index => $countedValue) {
                            $question['countedValueArray'][$index] += $countedValue;
                        }

                        //update calculation data
                        $question['calculations']['count'] +=  $additionalHook['calculations']['count'];
                        $question['calculations']['countValid'] +=  $additionalHook['calculations']['countValid'];
                        $question['calculations']['countInvalid'] +=  $additionalHook['calculations']['countInvalid'];

                        if (isset($question['calculations']['median'])) {
                            $question['calculations']['median'] = self::calculate_median($question['answers']);
                        }

                        if (isset($question['calculations']['average'])) {
                            $question['calculations']['average'] = self::calculate_average($question['answers']);
                        }

                        if (isset($question['calculations']['variance'])) {
                            $question['calculations']['variance'] = self::variance($question['answers']);
                        }

                        if (isset($question['calculations']['std'])) {
                            $question['calculations']['std'] = self::variance($question['answers'], true);
                        }

                        //Overide Question data
                        $surveyData['questiongroups'][$key][$subKey] = $question;
                    }
                }
            }
        }

        $result = $surveyData;

        return $result;
    }

    public static function  calculate_median($arr)
    {
        sort($arr);
        $count = count($arr); //total numbers in array
        $middleval = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
        if ($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval + 1];
            $median = (($low + $high) / 2);
        }
        return $median;
    }

    public static function calculate_average($arr)
    {
        $count = count($arr); //total numbers in array
        $total = array_sum($arr);
        $average = ($total / $count); // get average value
        return $average;
    }

    public static function variance($aValues, $getStandardDeviation = false)
    {
        $fMean = self::calculate_average($aValues);
        $fVariance = array_reduce(
            $aValues,
            function ($fVariance, $value) use ($fMean) {
                $dValue = doubleval($value);
                if (!is_nan($dValue) && $value !== null) {
                    $fVariance += pow($dValue - $fMean, 2);
                }
                return $fVariance;
            },
            0.0
        );

        $fVariance /= safecount($aValues);

        if ($getStandardDeviation === true) {
            return (float) sqrt($fVariance);
        }

        return $fVariance;
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
