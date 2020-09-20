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
                "de" => "Einstellung speichern",
                "fr" => "Sauvegarder"
            ],

            "Setting saved." => [
                "en" => "Setting saved.",
                "de" => "Einstellung gespeichert",
                "fr" => "Paramètres sauvegardé"
            ],

            "Setting can't be saved." => [
                "en" => "Setting can't be saved.",
                "de" => "Einstellung konnte nicht gespeichert werden",
                "fr" => "Paramètres non sauvegardé"
            ],

            "Basic link (for logged in users)" => [
                "en" => "Basic link (for logged in users)",
                "de" => "Basic link (für angemeldete Benutzer)",
                "fr" => "Lien de base (pour les utilisateurs connectés)"
            ],

            "Basic link (for non-logged in users)" => [
                "en" => "Basic link (for non-logged in users)",
                "de" => "Basic link (für Gast Benutzer)",
                "fr" => "Lien de base (pour les utilisateurs non connectés)"
            ],

            "Open Link" => [
                "en" => "Open Link",
                "de" => "Öffnen",
                "fr" => "Ouvrir le lien",
            ],

            "Basic settings" => [
                "en" => "Basic settings",
                "de" => "Standard Einstellung",
                "fr" => "Paramètres de base",
            ],

            "Additional settings" => [
                "en" => "Additional settings",
                "de" => "Zusätzliche Einstellungen",
                "fr" => "Paramètres additionnels",
            ],

            "Activate public statistic for this survey?" => [
                "en" => "Activate public statistic for this survey?",
                "de" => "Öffentliche Statistik für diese Umfrage aktivieren?",
                "fr" => "Activer les statistiques publiques pour ce sondage?"
            ],

            "Set Token? (Leave empty for none)" => [
                "en" => "Set Token? (Leave empty for none)",
                "de" => "Token setzen? (leer lassen für Zugang ohne Token)",
                "fr" => "Définir le mot secret? (Laisser vide pour aucun)"
            ],


            "Set expiry date? (Leave empty for none)" => [
                "en" => "Set expiry date? (Leave empty for none)",
                "de" => "Ablaufdatum festlegen? (Für nichts leer lassen)",
                "fr" => "Définir la date d'expiration? (Laisser vide pour aucun)"
            ],

            "Set begin date? (Leave empty for none)" => [
                "en" => "Set begin date? (Leave empty for none)",
                "de" => "Startdatum festlegen? (Für nichts leer lassen)",
                "fr" => "Définir la date de début? (Laisser vide pour aucun)"
            ],

            "Use logins?" => [
                "en" => "Use logins?",
                "de" => "Anmeldedaten verwenden?",
                "fr" => "Utiliser les identifiants?"
            ],

            "Default visualisation" => [
                "en" => "Default visualisation",
                "de" => "Standardvisualisierung (Diagramm)",
                "fr" => "Visualisation par défaut"
            ],

            "Path to customer logo" => [
                "en" => "Path to customer logo",
                "de" => "Kundenlogo Pfad",
                "fr" => "Logo client"
            ],

            "Contact to show on demand" => [
                "en" => "Contact to show on demand",
                "de" => "Kontaktdaten für Rückfragen",
                "fr" => "Contact"
            ],


            "Available logins" => [
                "en" => "Available logins",
                "de" => "Verfügbare Anmeldungen",
                "fr" => "Identifiantsdisponibles"
            ],


            "Email address" => [
                "en" => "Email address",
                "de" => "E-Mail Addresse",
                "fr" => "Adresse e-mail"
            ],


            "Valid (from/to)" => [
                "en" => "Valid (from/to)",
                "de" => "Gültig (Von/Bis)",
                "fr" => "Valide (Du/Au)"
            ],

            "Last login" => [
                "en" => "Last login",
                "de" => "Letzte Anmeldung",
                "fr" => "Dernière connexion"
            ],

            "Action" => [
                "en" => "Action",
                "de" => "Aktion",
                "fr" => "Action"
            ],

            "None added yet" => [
                "en" => "None added yet",
                "de" => "Noch keine hinzugefügt",
                "fr" => "Aucune donnée"
            ],

            "Save" => [
                "en" => "Save",
                "de" => "Speichern",
                "fr" => "Sauvegarder"
            ],

            "Close" => [
                "en" => "Close",
                "de" => "Schließen",
                "fr" => "Fermer"
            ],

            "Valid from (leave empty for unlimited)" => [
                "en" => "Valid from (leave empty for unlimited)",
                "de" => "Gültig ab (Leer lassen für keine Einschränkung)",
                "fr" => "Valable à partir du (laisser vide pour un durée illimitée)"
            ],

            "Valid until (leave empty for unlimited)" => [
                "en" => "Valid until (leave empty for unlimited)",
                "de" => "Gültig bis (Leer lassen für keine Einschränkung)",
                "fr" => "Valable jusqu'au (laisser vide pour un durée illimitée)"
            ],

            "Add new login" => [
                "en" => "Add new login",
                "de" => "Neue Anmeldedaten hinzufügen",
                "fr" => "Ajouter un identifiant"
            ],

            "Please type in the participation token:" => [
                "en" => "Please type in the participation token:",
                "de" => "Bitte geben Sie den Teilnahme-Token ein:",
                "fr" => "Veuillez saisir le mot secret de participation:"
            ],

            "Submit" => [
                "en" => "Submit",
                "de" => "Senden",
                "fr" => "Envoyer"
            ],

            "Password" => [
                "en" => "Password",
                "de" => "Passwort",
                "fr" => "Mot de passe"
            ],

            "You need to log in with the credentials sent to you:" => [
                "en" => "You need to log in with the credentials sent to you:",
                "de" => "Bitte geben Sie die Zugangsdaten ein die wir Ihnen per E-Mail versendet haben:",
                "fr" => "Vous devez vous connecter avec les informations d'identification qui vous ont été envoyées:"
            ],

            "You have no permission to enter this page." => [
                "en" => "You have no permission to enter this page.",
                "de" => "Sie haben keine Berechtigung, diese Seite zu betreten.",
                "fr" => "Vous n'êtes pas autorisé à accéder à cette page."
            ],

            "If you think this is an error, please contact the person, that send you this link." => [
                "en" => "If you think this is an error, please contact the person, that send you this link.",
                "de" => "Wenn Sie glauben, dass dies ein Fehler ist, wenden Sie sich bitte an die Person, die Ihnen diesen Link gesendet hat.",
                "fr" => "Si vous pensez qu'il s'agit d'une erreur, veuillez contacter la personne qui vous a envoyé ce lien."
            ],

            "This statistic is not yet available." => [
                "en" => "This statistic is not yet available.",
                "de" => "Diese Statistik ist noch nicht verfügbar.",
                "fr" => "Cette statistique n'est pas encore disponible."
            ],

            "No contact set" => [
                "en" => "No contact set",
                "de" => "Keine Information ",
                "fr" => "Pas d'information"
            ],
        ];

        return $translations;
    }
}
