import Vue from 'vue'
import VueI18n from 'vue-i18n'


Vue.use(VueI18n)

export default new VueI18n({
  locale: "en",
  fallbackLocale: "en",
  messages: {
    en: {
      id: "ID",
      Survey: "Survey",
      CommonQuestions: "Common questions",

      pageTitle: "Public Statistics for {title}",
      Home: "Home",
      QuestionList: "Question list",
      Contact: "Contact",
      GroupedStatistics: "Grouped statistics",
      GroupedStatisticsNotes: "This satistic contains results from other surveys. The responses are grouped and evaluated together.",

      SummaryQuestions: "This survey contains {questionCount} questions in {questionGroupCount} question groups.",
      summaryResponses: "A total of {responsesCount} responses have been collected."
    },

    de: {
      id: "ID",
      Survey: "Umfrage",
      CommonQuestions: "Gemeinsame Fragen",

      pageTitle: "Öffentliche Statistik für {title}",
      Home: "Startseite",
      QuestionList: "Fragen",
      Contact: "Kontakt",
      GroupedStatistics: "Gruppierte Statistiken",
      GroupedStatisticsNotes: "Diese Satistik enthält Ergebnisse aus anderen Umfragen. Die Antworten werden gruppiert und zusammen ausgewertet.",

      SummaryQuestions: "Diese Umfrage enthält {questionCount} Fragen in {questionGroupCount} Fragengruppe.",
      summaryResponses: "Insgesamt wurden {responsesCount} Antworten gesammelt"
    },

    fr: {
      id: "ID",
      Survey: "Sondage",
      CommonQuestions: "Questions communes",

      pageTitle: "Statistiques publique pour {title}",
      Home: "Accueil",
      QuestionList: "Questions",
      Contact: "Contact",
      GroupedStatistics: "Statistiques groupées",
      GroupedStatisticsNotes: "Ce satistique contient des résultats d'autres sondages. Les réponses sont regroupées et évaluées ensemble.",

      SummaryQuestions: "Cet sondage contient {questionCount} questions dans {questionGroupCount} groupes de questions.",
      summaryResponses: "Un total de {responsesCount} réponses a été collecté."
    }

  }
});

