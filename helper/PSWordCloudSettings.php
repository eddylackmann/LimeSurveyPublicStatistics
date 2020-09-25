<?php 
class PSWordCloudSettings {

    private static $model = null;

    private static function getModel() {
        if (self::$model == null) {
            self::$model = new self();
        }
         return self::$model;
    }


    public static function getSettings() {
        $oModel = self::getModel();
        
        $iPluginWordCloudId = $oModel->getWordCloudPluginId();
        $aPluginWordCloudSettings = $oModel->getWordCloudPluginSettings($iPluginWordCloudId);
 
        return $aPluginWordCloudSettings;

    }

    private function getWordCloudPluginId() {
        $oModel = Plugin::model()->findByAttributes(['name' => 'WordCloud']);
        if($oModel == null) {
            return null;
        }
        return $oModel->id;
    }

    private function getWordCloudPluginSettings($pluginId) {
        $aBaseSettings = [
            'wordCount' => 50,
            'cloudWidth' => 800,
            'cloudHeight' => 500,
            'fontPadding' => 5,
            'wordAngle' => 45,
            'minFontSize' => 10,
            'badwords' => 'und als oder and if or a is to I'
        ];
        if($pluginId == null) {
            return $aBaseSettings;
        }
        $aSettings = PluginSetting::model()->findAllByAttributes(['plugin_id' => $pluginId]);
        array_walk(
            $aSettings,
            function ($oSetting) use (&$aBaseSettings) {
                $aBaseSettings[$oSetting->key] = preg_replace('/"/', '', $oSetting->value);
            }
        );
        return $aBaseSettings;
    }

}