<?php

class PSTranslator
{

    const DEFAULT_LNG = 'en';

    public static $availableLanguages = ["en","de","fr"];

    public static function translate($original, $lng = '')
    {
        $lng = $lng != "" ? $lng : App()->language;

        //Retrieve all translations
        $translations = self::translations();

        $translated = $original;

        if ($translations[$original]) {

            if (isset($translations[$original][$lng])) {
                $translated = $translations[$original][$lng];
            } else {
                if (isset($translations[$original][self::DEFAULT_LNG])) {
                    $translated = $translations[$original][self::DEFAULT_LNG];
                }
            }
        }

        return $translated;
    }

    /**
     * This method returns all Translations 
     * 
     * @return array translations
     */
    public static function getAllTranslations()
    {
        return self::translations();
    }


    /**
     * Contains all translation available for the plugin
     * 
     * @return array of translation
     */
    private static function translations()
    {
        $translations = [

            "Public Statistics - Settings" => [
                "en" => "Public Statistics - Settings",
                "de" => "Öffentliche Statistik - Einstellung",
                "fr" => "Statistiques publiques - Paramètres"

            ],

            "Shareable links" => [
                "en" => "Shareable links",
                "de" => "Öffentliche Links",
                "fr" => "Liens de partage"
            ],

            "Save settings" => [
                "en" => "Save settings",
                "de" => "Einstellung speichern"
            ],

            "Setting saved." => [
                "en" => "Setting saved.",
                "de" => "Einstellung gespeichert"
            ],
            "Setting can't be saved." => [
                "en" => "Setting can't be saved.",
                "de" => "Einstellung konnte nicht gespeichert werden"
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

            "Save" => [
                "en" => "Save",
                "de" => "Speichern"
            ],

            "Close" => [
                "en" => "Close",
                "de" => "Schließen"
            ],

            "Valid from (leave empty for unlimited)" => [
                "en" => "Valid from (leave empty for unlimited)",
                "de" => "Gültig ab (Leer lassen für keine Einschränkung)"
            ],

            "Valid until (leave empty for unlimited)" => [
                "en" => "Valid until (leave empty for unlimited)",
                "de" => "Gültig bis (Leer lassen für keine Einschränkung)"
            ],

            "Add new login" => [
                "en" => "Add new login",
                "de" => "Neue Anmeldedaten hinzufügen"
            ],

            "Please type in the participation token:" => [
                "en" => "Please type in the participation token:",
                "de" => "Bitte geben Sie den Teilnahme-Token ein:"
            ],

            "Submit" => [
                "en" => "Submit",
                "de" => "Senden"
            ],

            "Password" => [
                "en" => "Password",
                "de" => "Passwort"
            ],

            "You need to log in with the credentials sent to you:" => [
                "en" => "You need to log in with the credentials sent to you:",
                "de" => "Bitte geben Sie die Zugangsdaten ein die wir Ihnen per E-Mail versendet haben:"
            ],

            "You have no permission to enter this page." => [
                "en" => "You have no permission to enter this page.",
                "de" => "Sie haben keine Berechtigung, diese Seite zu betreten."
            ],

            "If you think this is an error, please contact the person, that send you this link." => [
                "en" => "If you think this is an error, please contact the person, that send you this link.",
                "de" => "Wenn Sie glauben, dass dies ein Fehler ist, wenden Sie sich bitte an die Person, die Ihnen diesen Link gesendet hat."
            ],

            "This statistic is not yet available." => [
                "en" => "This statistic is not yet available.",
                "de" => "Diese Statistik ist noch nicht verfügbar."
            ],
        ];

        return $translations;
    }
}
