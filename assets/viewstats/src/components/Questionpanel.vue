<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title" v-html="renderQuestionHTML(question)" />
        </div>
        <div class="panel-body">
            <div class="container-fluid">
                <div class="row" v-if="isPlottable && !isOther">
                    <div class="container-center scoped-is-plotly-container" :id="'plotly--'+question.fieldname">
                        <vue-plotly @autosize="correctHeight" :data="plotlyData" :layout="plotlyLayout" :options="plotlyConfig"/>
                    </div>
                </div>
                <div class="row" v-if="isPlottable && !isOther">    
                    <div class="col-xs-12 text-center">
                        <div class="btn-group" role="group" aria-label="...">
                            <button 
                                type="button" 
                                class="btn" 
                                :class="chartType=='bar' ? 'btn-primary' : 'btn-default'"
                                @click="chartType='bar'" 
                            >
                                Bar
                            </button>
                            <button 
                                type="button" 
                                class="btn" 
                                :class="chartType=='doughnut' ? 'btn-primary' : 'btn-default'"
                                @click="chartType='doughnut'" 
                            >
                                Doughnut
                            </button>
                            <button 
                                type="button" 
                                class="btn" 
                                :class="chartType=='pie' ? 'btn-primary' : 'btn-default'"
                                @click="chartType='pie'" 
                            >
                                Pie
                            </button>
                            <button 
                                type="button" 
                                class="btn" 
                                :class="chartType=='line' ? 'btn-primary' : 'btn-default'"
                                @click="chartType='line'" 
                            >
                                Line
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row" v-if="isTextType || isOther">
                    <div class="col-sm-12">
                        <word-cloud
                            :wordlist="wordList"
                            :word-cloud-settings="wordCloudSettings"
                            :fieldId="question.fieldname"
                        />
                    </div>
                </div>
                <div class="row">
                    <ul class="list-items">
                        <li 
                            v-for="(value,key) in filteredCalculations" :key="key"
                            class="list-group-item col-md-6 col-sm-12"
                        >
                            <div class="row">
                                <div class="col-xs-8">{{key | keydescription}} <small>({{key}})</small></div>
                                <div class="col-xs-4">{{value}}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VuePlotly from '@statnett/vue-plotly';
import WordCloud from './WordCloud';
import _ from 'lodash';

export default {
  name: 'QuestionPanel',
  components: {VuePlotly, WordCloud},
  props: {
    question: {type: Object, required: true},
    wordCloudSettings: {type: Object, required: true},
  },
  data() {
      return {
        chartType: 'bar'
      };
  },
  computed: {
      filteredCalculations() {
          return _.pickBy(this.question.calculations, (value,index) => value!=null );
      },
      plotlyConfig() {
          return {
              scrollZoom: true,
              responsive: true,
              showLink: false,
              displayModeBar: false,
              displaylogo: false
          };
      },
      wordList() {
          const allTexts = _.reduce(this.question.answers, (coll,answer) => coll+' '+answer, '');
          const allTextArray = allTexts.split(' ');
          return _.sortBy(_.toPairs(this.createWordMap(allTextArray)), (array) => array[1]);
      },
      plotlyData() {
          const values = [];
          const labels = [];

          _.forEach(this.question.countedValueArray, (value, key) => {
              const label = this.getLabelFromAnswers(key);
              values.push(value);
              labels.push(label);
          });
            switch(this.chartType) {
                case 'bar':
                    return [{
                        y: values,
                        x: labels,
                        type: 'bar'
                    }];
                case 'pie':
                    return [{
                        values,
                        labels,
                        type: 'pie',
                    }];
                case 'doughnut':
                    return [{
                        values,
                        labels,
                        type: 'pie',
                        hole: .4
                    }];
                case 'line':
                    return [{
                        y: values,
                        x: labels,
                        type: 'line',
                    }];
            }
          
      },
      plotlyLayout() {
          return {
              title: '',
              showLegend: false
          };
      },
      isOther() {
          return /.*other.*/.test(this.question.aid);
      },
      isPlottable() {
          const nonPlottableTypes = ['D', 'I', 'K', 'N', 'Q', 'S', 'T', 'U', 'X', '|', '*'];
          return nonPlottableTypes.indexOf(this.question.type) == -1;
      },
      isTextType() {
          const textTypes = ['S', 'T', 'U', 'X', '*'];
          return textTypes.indexOf(this.question.type) > -1;
      }
    },
    methods: {
        correctHeight() {
            console.log('resize-called');
            $('#plotly--'+this.question.fieldname).height(
                $('#plotly--'+this.question.fieldname).find('svg').height()
            );
        },
        renderQuestionHTML(question) {
            return `<small>(${question.aid})</small> | ${question.question}`;
        },
        getLabelFromAnswers(key) {
            if(this.question.answeroptions === undefined) { return key; }
            const answerObject = _.find(this.question.answeroptions, (answeroption) => answeroption.code == key);
            return answerObject != undefined ? answerObject.answer : key;
        },
        createWordMap(wordsArray) {
            // create map for word counts
            var wordsMap = {};
            wordsArray.forEach(function (key) {
                if(!_.isEmpty(key) && key != null && key != 'null') {
                    if (wordsMap.hasOwnProperty(key)) {
                        wordsMap[key]++;
                    } else {
                        wordsMap[key] = 1;
                    }
                }
            });
            return wordsMap;
        }
    },
    filters: {
        keydescription(key) {
            const keyDescriptions = {
                count : 'Number of responses',
                countValid : 'Number of valid responses',
                countInvalid : 'Number of invalid, or empy responses',
                median : 'Median',
                average : 'Average (simple)',
                variance : 'Variance',
                std : 'Standard deviation',
            }
            return keyDescriptions[key]
        }
    }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.scoped-is-plotly-container {
    min-height: 25rem;
}
</style>
