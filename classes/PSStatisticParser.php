<?php
class PSStatisticParser {

    public $sid;
    
    private $oSurvey;
    private $oSurveyResponses;

    public function __construct($sid) {
        $this->sid = $sid;
        $this->oSurvey = Survey::model()->findByPk($sid);
        $this->oSurveyResponses = SurveyDynamic::model($sid)->findAll();
        $this->aQuestionList = $this->_loadQuestionList();
    }

    public function createParsedDataBlock() 
    {
        $aQuestionGroupList = $this->oSurvey->groups;

        uasort(
            $aQuestionGroupList, 
            function ($oGroupA, $oGroupB) { 
                return $oGroupA->group_order < $oGroupB->group_order ? -1 : 1;
            }
        );

        $aDataBlock = [
            'questiongroups' => [],
            'data' => [
                'surveyname' => $this->oSurvey->correct_relation_defaultlanguage->surveyls_title,
                'questions' => 0,
                'questiongroups' => safecount($aQuestionGroupList),
                'responses' => safecount($this->oSurveyResponses),
            ]
        ];
        foreach ($aQuestionGroupList as $oQuestionGroup) {
            $aSubDataBlock = [];
            $aQuestions = $oQuestionGroup->questions;
            uasort(
                $aQuestions, 
                function ($oQuestionA, $oQuestionB) { 
                    return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
                }
            );
            $aDataBlock['data']['questions'] = $aDataBlock['data']['questions']+safecount($aQuestions);
            foreach ( $aQuestions as $oQuestion) {
                $aSubDataBlock = array_merge($aSubDataBlock, $this->_getQuestionFieldMapEntries($oQuestion));
            }

            $aDataBlock['questiongroups'][$oQuestionGroup->gid] = $aSubDataBlock;
        }

        return $aDataBlock;
    }

    private function _getQuestionFieldMapEntries($oQuestion) {
        $parseFunction = $this->_getParserForType($oQuestion->type);
        $aQuestionDataArray = $this->$parseFunction($oQuestion);
        $this->_applyResponseDataToQuestionData($aQuestionDataArray);
        return $aQuestionDataArray;
    }

    private function _getParserForType($type) 
    {
        switch($type) {
            case 'A':
            case 'B':
            case 'C':
            case 'E':
            case 'F':
            case 'H':
            case 'K':
            case 'Q': return "_parse_simple_subquestion_types";
            
            case 'M': return "_parse_multiple_choice";

            case 'P': return "_parse_multiple_choice_with_comments";
            
            case '1': return "_parse_array_multiscale";
            
            case ':': 
            case ';': return "_parse_array_multi_flex";
            
            case '!':
            case 'L':
            case 'Z': return "_parse_list_dropdown";
            
            case 'O': return "_parse_list_with_comment";

            case 'R': return "_parse_ranking";
            
            case '|': return "_parse_fileupload";

            case 'D': 
            case 'G': 
            case 'N': 
            case 'X': 
            case 'Y': 
            case '5': 
            case 'S': 
            case 'T': 
            case 'U': 
            case '*': 
            case 'I': return "_parse_to_base";
        }
    }

    private function _parse_list_dropdown($oQuestion) {
        $aQuestionData = [];
        $aAnswers = array_map( function($oA) { return $oA->attributes; }, $oQuestion->answers);

        $baseFieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}";
        $aQuestionData[$baseFieldname] = [
            "fieldname"=>$baseFieldname,
            "question"=>$oQuestion->question,
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title,
            'answeroptions' =>  $aAnswers
        ];
        
        if ($oQuestion->other == "Y") {
            $otherFieldname = "{$baseFieldname}other";
            $aQuestionData[$otherFieldname] = [
                "fieldname"=>$otherFieldname,
                "question"=>$oQuestion->question.' (other)',
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oQuestion->title."other"
            ];
        }

        return $aQuestionData;
    }

    private function _parse_list_with_comment($oQuestion) {
        $aQuestionData = [];
        $aAnswers = $oQuestion->answers;

        $baseFieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}";
        $aQuestionData[$baseFieldname] = [
            "fieldname"=>$baseFieldname,
            "question"=>$oQuestion->question,
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title,
            'answeroptions' =>  $aAnswers
        ];

        $otherFieldname = "{$baseFieldname}comment";
        $aQuestionData[$otherFieldname] = [
            "fieldname"=>$otherFieldname,
            "question"=>$oQuestion->question.' (comment)',
            "title"=>$oQuestion->title.'-comment',
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title."other"
        ];

        return $aQuestionData;
    }

    private function _parse_to_base($oQuestion) {
        $aQuestionData = [];
        $aAnswers = $oQuestion->answers;

        $baseFieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}";
        $aQuestionData[$baseFieldname] = [
            "fieldname"=>$baseFieldname,
            "question"=>$oQuestion->question,
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title,
            'answeroptions' =>  $aAnswers
        ];

        return $aQuestionData;
    }

    private function _parse_array_multi_flex($oQuestion) {
        $aQuestionData = [];
        $aAnswers = $oQuestion->answers;

        $aSubquestions = $oQuestion->subquestions;
        uasort(
            $aSubquestions, 
            function ($oQuestionA, $oQuestionB) { 
                return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
            }
        );
        $answerscale0 = array();
        $answerscale1 = array();
        $answerList = array();

        foreach ($aSubquestions as $oSubquestion) {
            if($oSubquestion->scale_id == 1) {
                $answerscale1[] = $oSubquestion;
                $answerList[] = array(
                'code'=>$oSubquestion->title,
                'answer'=>$oSubquestion->question,
                );
            } else if($oSubquestion->scale_id == 0) {
                $answerscale0[] = $oSubquestion;
            }
        }

        foreach($answerscale0 as $oScale0Question) {
            foreach($answerscale1 as $oScale1Question) {
                $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oScale0Question->title}_{$oScale1Question->title}";
                $aQuestionData[$fieldname] = [
                    "fieldname"=>$fieldname,
                    "question"=>$oQuestion->question.'| '.$oScale0Question->question.' -> '.$oScale1Question->question,
                    'type'=>$oQuestion->type,
                    'sid'=>$oQuestion->sid,
                    "gid"=>$oQuestion->gid,
                    "qid"=>$oQuestion->qid,
                    "aid"=>$oScale0Question->title."_".$oScale1Question->title,
                    "sqid"=>$oScale0Question->qid,
                    'answeroptions' =>  $aAnswers
                ];
            }
        }

        return $aQuestionData;
    }

    private function _parse_array_multiscale($oQuestion) {
        $aQuestionData = [];
        $aAnswers = $oQuestion->answers;

        $aSubquestions = $oQuestion->subquestions;
        uasort(
            $aSubquestions, 
            function ($oQuestionA, $oQuestionB) { 
                return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
            }
        );

        foreach ($aSubquestions as $oSubquestion) {
            $fieldname_0 = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oSubquestion->title}#0";
            $aQuestionData[$fieldname_0] = [
                "fieldname"=>$fieldname_0, 
                "question"=>$oQuestion->question.'| '.$oSubquestion->question.' (#0)',
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title, 
                "scale_id"=>0,
                'answeroptions' =>  $aAnswers
            ];
            $fieldname_1 = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oSubquestion->title}#1";
            $aQuestionData[$fieldname_1] = [
                "fieldname"=>$fieldname_1, 
                "question"=>$oQuestion->question.'| '.$oSubquestion->question.' (#1)',
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title, 
                "scale_id"=>1,
                'answeroptions' =>  $aAnswers
            ];
        }

        return $aQuestionData;
    }

    private function _parse_ranking($oQuestion) {
        $aQuestionData = [];
        $aAnswers = $oQuestion->answers;

        $answersCount = intval(
            Answer::model()->countByAttributes(
                array(
                    'qid' => $oQuestion->qid, 
                    'language' => $oQuestion->survey->language
                )
            )
        );
        $maxDbAnswer = QuestionAttribute::model()->findByAttributes(
            ["qid" => $oQuestion->qid, 'attribute' => 'max_subquestions']
        );
        $columnsCount = (!$maxDbAnswer || intval($maxDbAnswer->value) < 1) ? $answersCount : intval($maxDbAnswer->value);
        $columnsCount = min($columnsCount,$answersCount); // Can not be upper than current answers #14899
        for ($i = 1; $i <= $columnsCount; $i++) {
            $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}$i";
            $aQuestionData[$fieldname] = [
                "fieldname"=>$fieldname, 
                "question"=>$oQuestion->question.' #'.$i,
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oQuestion->title.'#'.$i,
                'answeroptions' =>  $aAnswers
            ];
        }

        return $aQuestionData;
    }

    private function _parse_fileupload($oQuestion) {
        $aQuestionData = [];

        $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}";
        $aQuestionData[$fieldname] = [
            "fieldname"=>$fieldname, 
            "question"=>$oQuestion->question,
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title
        ];
        $fieldnameFilecount = "{$fieldname}_filecount";
        $aQuestionData[$fieldnameFilecount] = [
            "fieldname"=>$fieldnameFilecount, 
            "question"=>$oQuestion->question.' (No. of files)',
            'type'=>$oQuestion->type,
            'sid'=>$oQuestion->sid,
            "gid"=>$oQuestion->gid,
            "qid"=>$oQuestion->qid,
            "aid"=>$oQuestion->title.'filecount'
        ];

        return $aQuestionData;


    }
    
    private function _parse_multiple_choice_with_comments($oQuestion) {
        $aQuestionData = [];
        $aSubquestions = $oQuestion->subquestions;
        uasort(
            $aSubquestions, 
            function ($oQuestionA, $oQuestionB) { 
                return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
            }
        );
        foreach ($aSubquestions as $oSubquestion) {
            $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oSubquestion->title}";
            $aQuestionData[$baseFieldname] = [
                "fieldname"=>$baseFieldname, 
                "question"=>$oQuestion->question.'| '.$oSubquestion->question,
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title,
                'sqid'=>$oSubquestion->qid
            ];
            
            $fieldnameComment = "{$fieldname}comment";
            $aQuestionData[$fieldnameComment] = [
                "fieldname"=>$fieldnameComment,
                "question"=>$oQuestion->question.'| '.$oSubquestion->question.' (comment)',
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title."comment",
                'sqid'=>$oSubquestion->qid
            ];
        }

        if($oQuestion->other == 'Y') {
            $fieldnameOther = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}other";
            $aQuestionData[$fieldnameOther] = [
                "fieldname"=>$fieldnameOther,
                "question"=>$oQuestion->question,
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oQuestion->title."other",
            ];

            $fieldnameOtherComment = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}othercomment";
            $aQuestionData[$fieldnameOtherComment] = [
                "fieldname"=>$fieldnameOtherComment,
                'type'=>$oQuestion->type,
                "question"=>$oQuestion->question.' (other comment)',
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oQuestion->title."othercomment",
            ];
            
        }

        return $aQuestionData;  
    }

    private function _parse_multiple_choice($oQuestion) {
        $aQuestionData = [];
        $aSubquestions = $oQuestion->subquestions;
        uasort(
            $aSubquestions, 
            function ($oQuestionA, $oQuestionB) { 
                return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
            }
        );

        foreach ($aSubquestions as $oSubquestion) {
            $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oSubquestion->title}";
            $aQuestionData[$fieldname] = [
                "fieldname"=>$fieldname,
                "question"=>$oQuestion->question.'| '.$oSubquestion->question,
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title,
                'sqid'=>$oSubquestion->qid,
                'answeroptions' =>  []
            ];
        }

        if($oQuestion->other == 'Y') {
            $fieldnameOther = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}other";
            $aQuestionData[$fieldnameOther] = [
                "fieldname"=>$fieldnameOther,
                "question"=>$oQuestion->question.' (other)',
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oQuestion->question."other",
                'answeroptions' =>  []
            ];
        }

        return $aQuestionData;        
    }

    private function _parse_simple_subquestion_types($oQuestion) {
        $aQuestionData = [];
        $aSubquestions = $oQuestion->subquestions;
        uasort(
            $aSubquestions, 
            function ($oQuestionA, $oQuestionB) { 
                return $oQuestionA->question_order < $oQuestionB->question_order ? -1 : 1;
            }
        );
        $aAnswers = $oQuestion->answers;

        foreach ($aSubquestions as $oSubquestion) {
            $fieldname = "{$oQuestion->sid}X{$oQuestion->gid}X{$oQuestion->qid}{$oSubquestion->title}";
            $aQuestionData[$fieldname] = [
                "fieldname"=>$fieldname,
                "question"=>$oQuestion->question.'| '.$oSubquestion->question,
                'type'=>$oQuestion->type,
                'sid'=>$oQuestion->sid,
                "gid"=>$oQuestion->gid,
                "qid"=>$oQuestion->qid,
                "aid"=>$oSubquestion->title,
                'sqid'=>$oSubquestion->qid,
                'answeroptions' => $aAnswers
            ];
        }

        return $aQuestionData;        
    }

    private function _loadQuestionList() {
        $aQuestionList = Question::model()->findAllByAttributes(
            [
                'sid' => $this->sid,
                'parent_qid' => 0
            ]
        );
        return $aQuestionList;
    }

    private function _applyResponseDataToQuestionData(&$aQuestionData) {
        foreach($aQuestionData as $sSGQA => $aQuestionFieldset) {
            $aResponses = Yii::app()->db->createCommand()
                ->select($sSGQA)
                ->from('{{survey_'.$aQuestionFieldset['sid'].'}}')
                ->queryColumn();

            $aCalculations['count'] = safecount($aResponses);
            $aCalculations['countValid'] = safecount(array_filter($aResponses));
            $aCalculations['countInvalid'] = $aCalculations['count']-$aCalculations['countValid'];
            $aCountValues = $this->count_array_values_with_nullables($aResponses);

            $aCalculations['median'] = null;
            $aCalculations['average'] = null;
            $aCalculations['variance'] = null;
            $aCalculations['std'] = null;
            
            if ($this->isNumericallySafe($aQuestionFieldset['type'])) {
                
                $aCalculations['median'] = $this->calculate_median($aResponses);
                $aCalculations['average'] = $this->calculate_average($aResponses);
                $aCalculations['variance'] = $this->variance($aResponses);
                $aCalculations['std'] = $this->variance($aResponses, true);
            }
            


            $aQuestionData[$sSGQA]['answers'] = $aResponses;
            $aQuestionData[$sSGQA]['calculations'] = $aCalculations;
            $aQuestionData[$sSGQA]['countedValueArray'] = $aCountValues;
        }
    }

    public function count_array_values_with_nullables($arr)
    {
        $result = [];
        foreach($arr as $itrt) {

            if (!$itrt) {
                $result[gT('No answer/Not shown')] = isset($result[gT('No answer/Not shown')]) ? $result[gT('No answer/Not shown')]+1 : 0;
                continue;
            }

            if (!isset($result[$this->_filterForDefaultString($itrt)])) {
                $result[$itrt] = 0;
            }

            $result[$itrt] = $result[$itrt]+1;
        }
        return $result;
        
    }

    public function isNumericallySafe($type) {
        return in_array($type, [':','5','A','B','K','N']);
    }

    public function calculate_median($arr) {
        sort($arr);
        $count = count($arr); //total numbers in array
        $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
        if($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }
    
    public function calculate_average($arr) {
        $count = count($arr); //total numbers in array
        $total = array_sum($arr);
        $average = ($total/$count); // get average value
        return $average;
    }

    public function variance($aValues, $getStandardDeviation = false)
    {
        $fMean = $this->calculate_average($aValues);
        $fVariance = array_reduce(
            $aValues, 
            function ($fVariance, $value) use ($fMean) {
                $dValue = doubleval($value);
                if(!is_nan($dValue) && $value!==null) {
                    $fVariance += pow($dValue - $fMean, 2);
                }
                return $fVariance;
            },
            0.0
        );

        $fVariance /= safecount($aValues);

        if ($getStandardDeviation===true) { 
            return (double) sqrt($fVariance); 
        }

        return $fVariance;
    }

    private function _filterForDefaultString($string) {
        if(preg_match("/.*-oth.*/",$string)) {
            return gT('Other');
        }
        switch(trim($string)) {
            case 'Y': return gT('Yes');
            case 'N': return gT('No');
            case '-oth-': return gT('Other');
            default: return $string;
        }
    }

}