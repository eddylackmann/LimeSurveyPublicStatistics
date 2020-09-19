<?php

class PSTranslator
{
    const DEFAULT_PLUGIN_LNG = 'en';

    public static function translate($original, $lng = '')
    {
        $lng = $lng != "" ? $lng : Yii::app()->getConfig("defaultlang");

        //retrieve all translations
        $translations = self::translations();

        $translated = $original;
        if ($translations[$original]) {

            if (isset($translations[$original][$lng])) {
                $translated = $translations[$original][$lng];
            } else {
                if (isset($translations[$original][self::DEFAULT_PLUGIN_LNG])) {
                    $translated = $translations[$original][self::DEFAULT_PLUGIN_LNG];
                }
            }
        }

        return $translated;
    }

    /**
     * 
     */
    public static function getAllTranslations()
    {
        return self::translations();
    }


    /**
     * 
     */
    private static function translations()
    {
        $translations = [

            "Public Statistics - Settings" => [
                "en" => "Public Statistics - Settings",
                "de" => "Öffentliche Statistik - Einstellung"

            ],

            "Shareable links" => [
                "en" => "Shareable links",
                "de" => "Öffentliche Links"
            ],

            "Save settings" => [
                "en" => "Save settings",
                "de" => "Einstellung speichern"
            ],

            "Setting saved" => [
                "en" => "Setting saved",
                "de" => "Einstellung gespeichert"
            ],

            "Basic link (for logged in users)" => [
                "en" => "Basic link (for logged in users)",
                "de" => "Basic link (für angemeldete Benutzer)"
            ],

            "Basic link (for non-logged in users)" => [
                "en" => "Basic link (for non-logged in users)",
                "de" => "Basic link (für Gast Benutzer)"
            ],

            "Open Link" => [
                "en" => "Open Link",
                "de" => "Öffnen"
            ],

            "Basic settings" => [
                "en" => "Basic settings",
                "de" => "Standard Einstellung"
            ],

            "Additional settings" => [
                "en" => "Additional settings",
                "de" => "Zusätzliche Einstellungen"
            ],

            "Activate public statistic for this survey?" => [
                "en" => "Activate public statistic for this survey?",
                "de" => "Öffentliche Statistik für diese Umfrage aktivieren?"
            ],

            "Set Token? (Leave empty for none)" => [
                "en" => "Set Token? (Leave empty for none)",
                "de" => "Token setzen? (leer lassen für Zugang ohne Token)"
            ],


            "Set expiry date? (Leave empty for none)" => [
                "en" => "Set expiry date? (Leave empty for none)",
                "de" => "Ablaufdatum festlegen? (Für nichts leer lassen)"
            ],

            "Set begin date? (Leave empty for none)" => [
                "en" => "Set begin date? (Leave empty for none)",
                "de" => "Startdatum festlegen? (Für nichts leer lassen)"
            ],

            "Use logins?" => [
                "en" => "Use logins?",
                "de" => "Anmeldedaten verwenden?"
            ],

            "Default visualisation" => [
                "en" => "Default visualisation",
                "de" => "Standardvisualisierung (Diagramm)"
            ],

            "Path to customer logo" => [
                "en" => "Path to customer logo",
                "de" => "Kundenlogo Pfad"
            ],

            "Contact to show on demand" => [
                "en" => "Contact to show on demand",
                "de" => "Kontaktdaten für Rückfragen"
            ],


            "Available logins" => [
                "en" => "Available logins",
                "de" => "Verfügbare Anmeldungen"
            ],


            "Email address" => [
                "en" => "Email address",
                "de" => "E-Mail Addresse"
            ],


            "Valid (from/to)" => [
                "en" => "Valid (from/to)",
                "de" => "Gültig (Von/Bis)"
            ],

            "Last login" => [
                "en" => "Last login",
                "de" => "Letzte Anmeldung"
            ],

            "Action" => [
                "en" => "Action",
                "de" => "Aktion"
            ],

            "None added yet" => [
                "en" => "None added yet",
                "de" => "Noch keine hinzugefügt"
            ],

        ];

        return $translations;
    }
}
