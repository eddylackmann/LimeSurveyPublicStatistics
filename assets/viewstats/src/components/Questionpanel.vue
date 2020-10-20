<template>
  <div class="panel panel-default selector--question-panel">
    <div class="panel-heading">
      <h3
        class="panel-title anchor--title"
        :id="`link-anchor--${question.fieldname}`"
        v-html="renderQuestionHTML(question)"
      />
    </div>
    <div class="panel-body">
      <div class="container-fluid">
        <div class="row" v-if="isPlottable && !isOther">
          <div
            class="container-center scoped-is-plotly-container"
            :id="'plotly--' + question.fieldname"
          >
            <vue-plotly
              @relayout="correctHeight('relayout')"
              :data="plotlyData"
              :layout="plotlyLayout"
              :options="plotlyConfig"
            />
          </div>
        </div>
        <div class="row selector--buttonrow" v-if="isPlottable && !isOther">
          <div class="col-xs-12 text-center">
            <div class="btn-group" role="group" aria-label="...">
              <button
                type="button"
                class="btn"
                :class="chartType == 'bar' ? 'btn-primary' : 'btn-default'"
                @click="chartType = 'bar'"
              >
                Bar
              </button>
              <button
                type="button"
                class="btn"
                :class="chartType == 'doughnut' ? 'btn-primary' : 'btn-default'"
                @click="chartType = 'doughnut'"
              >
                Doughnut
              </button>
              <button
                type="button"
                class="btn"
                :class="chartType == 'pie' ? 'btn-primary' : 'btn-default'"
                @click="chartType = 'pie'"
              >
                Pie
              </button>
              <button
                type="button"
                class="btn"
                :class="chartType == 'line' ? 'btn-primary' : 'btn-default'"
                @click="chartType = 'line'"
              >
                Line
              </button>
            </div>
          </div>
        </div>
        <div class="row" v-if="isTextType || isOther">
          <div class="col-sm-12 text-center">
            <word-cloud
              :wordlist="wordList"
              :word-cloud-settings="wordCloudSettings"
              :fieldId="question.fieldname"
            />
          </div>
        </div>
        <div class="row" v-if="isNpsType || isOther">
          <div class="col-sm-12 text-center">
            <h3 style="text-primary">Net Promoter score</h3>
            <div class="row">
              <div
                class="col-md-8 col-sm-12"
                style="float: none; margin: 0 auto"
              >
                <table class="table text-left">
                  <thead class>
                    <tr>
                      <th>{{ $t("Indicators") }}</th>
                      <th>{{ $t("Number") }}</th>
                      <th>{{ $t("Percentage") }} (%)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{ $t("Promoters") }} [9-10]</td>
                      <td>{{ calcNetPromoterScore()[0] }}</td>
                      <td>{{ calcNetPromoterScore()[1] }} %</td>
                    </tr>
                    <tr>
                      <td>{{ $t("Detractors") }} [0-6]</td>
                      <td>{{ calcNetPromoterScore()[2] }}</td>
                      <td>{{ calcNetPromoterScore()[3] }} %</td>
                    </tr>
                  </tbody>
                </table>
                <hr />
                <div>
                  <h3>
                    <strong>
                      Score:&nbsp;
                      <span class="text-danger">{{
                        calcNetPromoterScore()[6]
                      }}</span>
                    </strong>
                  </h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <hr />
          </div>
        </div>
        <div class="row">
          <ul class="list-items scoped--noBorderRadius">
            <li
              v-for="(value, key) in filteredCalculations"
              :key="key"
              class="list-group-item scoped--noBorderRadius col-md-6 col-sm-12"
            >
              <div class="row">
                <div class="col-xs-8">
                  {{ key | keydescription }}
                  <small>({{ key }})</small>
                </div>
                <div class="col-xs-4">{{ value }}</div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VuePlotly from "@statnett/vue-plotly";
import WordCloud from "./WordCloud";
import _ from "lodash";
import i18n from "../plugins/i18n";

export default {
  name: "QuestionPanel",
  components: { VuePlotly, WordCloud },
  props: {
    question: { type: Object, required: true },
    wordCloudSettings: { type: Object, required: true },
    initialChartType: { type: String, default: "bar" },
    basecolors: { type: Array },
  },
  data() {
    return {
      chartType: "bar",
    };
  },
  computed: {
    filteredCalculations() {
      return _.pickBy(
        this.question.calculations,
        (value, index) => value != null
      );
    },
    plotlyConfig() {
      return {
        scrollZoom: true,
        responsive: true,
        showLink: false,
        displayModeBar: true,
        displaylogo: false,
      };
    },
    wordList() {
      const allTexts = _.reduce(
        this.question.answers,
        (coll, answer) => coll + " " + answer,
        ""
      );
      const allTextArray = allTexts.split(" ");
      return _.sortBy(
        _.toPairs(this.createWordMap(allTextArray)),
        (array) => array[1]
      );
    },
    plotlyData() {
      const values = [];
      const labels = [];

      _.forEach(this.question.countedValueArray, (value, key) => {
        const label = this.getLabelFromAnswers(key);
        values.push(value);
        labels.push(label);
      });
      switch (this.chartType) {
        case "bar":
          return [
            {
              y: values,
              x: labels,
              type: "bar",
              marker: {
                color: this.basecolors,
              },
            },
          ];
        case "pie":
          return [
            {
              values,
              labels,
              type: "pie",
              marker: {
                colors: this.basecolors,
              },
            },
          ];
        case "doughnut":
          return [
            {
              values,
              labels,
              type: "pie",
              marker: {
                colors: this.basecolors,
              },
              hole: 0.4,
            },
          ];
        case "line":
          return [
            {
              y: values,
              x: labels,
              type: "line",
              marker: {
                color: this.basecolors,
              },
            },
          ];
      }
      return [];
    },
    plotlyLayout() {
      return {
        title: this.uppercaseFirst(this.chartType + "-chart"),
        legend: {
          x: 1,
        },
        xaxis: {
          tick0: 0,
          dtick: 1,
        },
        yaxis: {
          tickmode: 'auto',
          tick0: 0,
        },
      };
    },
    isOther() {
      return /.*other.*/.test(this.question.aid);
    },
    isNpsType() {
      if (this.question.type == "Net-Promoter-Score") {
        return true;
      }
      return false;
    },

    isPlottable() {
      const nonPlottableTypes = [
        "D",
        "I",
        "K",
        "N",
        "Q",
        "S",
        "T",
        "U",
        "X",
        "|",
        "*",
      ];
      return nonPlottableTypes.indexOf(this.question.type) == -1;
    },
    isTextType() {
      const textTypes = ["S", "T", "U", "X", "*"];
      return textTypes.indexOf(this.question.type) > -1;
    },
  },
  methods: {
    correctHeight() {
      $("#plotly--" + this.question.fieldname).height(
        $("#plotly--" + this.question.fieldname)
          .find("svg")
          .height()
      );
    },
    renderQuestionHTML(question) {
      return `<small>(${question.aid})</small> <br> ${question.question}`;
    },
    getLabelFromAnswers(key) {
      if (this.question.answeroptions === undefined) {
        return key;
      }
      const answerObject = _.find(
        this.question.answeroptions,
        (answeroption) => answeroption.code == key
      );

      return answerObject != undefined && answerObject.answer.trim() != ""
        ? answerObject.answer
        : key;
    },
    calcNetPromoterScore() {
      //NPS Berechnungen auf Statistik anpassen
      var answers = this.question.answers;
      var promoter = 0;
      var indifferent = 0;
      var total = answers.length;
      var critic = 0;
      var result = [];
      if (answers) {
        answers.forEach((answer) => {
          if (answer >= 9) {
            promoter += 1;
          }
          if (answer <= 6) {
            critic += 1;
          }

          if (answer == 7) {
            indifferent += 1;
          }

          if (answer == 8) {
            indifferent += 1;
          }
        });

        //Promotoren Berechnung
        result[0] = promoter;
        result[1] = Math.round((promoter / total) * 100);

        //Kritikern Berechnung
        result[2] = critic;
        result[3] = Math.round((critic / total) * 100);

        //Indifferenten Berechnung
        result[4] = indifferent;
        result[5] = Math.round((indifferent / total) * 100);

        result[6] = Math.max(0, result[1] - result[3]);
        result[7] = total;

        return result;
      }
    },

    createWordMap(wordsArray) {
      // create map for word counts
      var wordsMap = {};
      wordsArray.forEach(function (key) {
        if (!_.isEmpty(key) && key != null && key != "null") {
          if (wordsMap.hasOwnProperty(key)) {
            wordsMap[key]++;
          } else {
            wordsMap[key] = 1;
          }
        }
      });
      return wordsMap;
    },
    uppercaseFirst(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
  },
  created() {
    this.chartType = this.initialChartType;
  },
  filters: {
    keydescription(key) {
      const keyDescriptions = {
        count: i18n.t("NumberOfResponses"),
        countValid: i18n.t("NumberOfValid"),
        countInvalid: i18n.t("NumberOfInvalid"),
        median: i18n.t("Median"),
        average: i18n.t("Average"),
        variance: i18n.t("Variance"),
        std: i18n.t("Std"),
      };
      return keyDescriptions[key];
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" scoped>
.scoped-is-plotly-container {
  min-height: 25rem;
}
.scoped--noBorderRadius {
  border-radius: 0;
}
.anchor--title {
  padding-top: 80px;
  margin-top: -80px;
}
</style>
