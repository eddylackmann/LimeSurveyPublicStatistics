<template>
  <article class="PSarticlecontainer">
    <transition name="fade">
      <div class="container loader-container" v-if="loading">
        <div class="row loader-row">
          <div
            id="loader"
            class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center"
            style="min-height: 100%"
          >
            <div class="ls-flex align-content-center align-items-center">
              <div class="loader-public-statistic text-center">
                <div class="contain-pulse animate-pulse">
                  <div class="square"></div>
                  <div class="square"></div>
                  <div class="square"></div>
                  <div class="square"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
    <transition name="slide">
      <nav class="navbar navbar-fixed-top" v-if="!loading">
        <div class="container">
          <div class="navbar-header">
            <button
              type="button"
              class="navbar-toggle collapsed"
              data-toggle="collapse"
              data-target="#navbar"
              aria-expanded="false"
              aria-controls="navbar"
            >
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">
              <img :src="surveydata.companyImage" />
            </a>
          </div>
          <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav stats-nav">
              <li class="active">
                <a href="#headline">{{ $t("Home") }}</a>
              </li>
              <li class="dropdown">
                <a
                  href="#"
                  class="dropdown-toggle"
                  data-toggle="dropdown"
                  role="button"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  {{ $t("QuestionList") }}

                  <span class="caret"></span>
                </a>
                <ul class="dropdown-menu scrollable-menu">
                  <li
                    v-for="(question, questionAnchor) in questionAnchors"
                    :key="questionAnchor"
                  >
                    <a :href="`#link-anchor--${questionAnchor}`">
                      {{ question | forIndex }}
                    </a>
                  </li>
                </ul>
              </li>
              <li>
                <a href="#contact" @click="showContactData">{{
                  $t("Contact")
                }}</a>
              </li>
            </ul>
          </div>
          <!--/.nav-collapse -->
        </div>
      </nav>
    </transition>
    <transition name="fade">
      <div class="container" v-if="!loading">
        <div class="row">
          <div class="col-xs-12">
            <div class="page-header" id="headline">
              <h2>{{ $t("pageTitle", { title: data.surveyname }) }}</h2>
            </div>
            <div v-if="groupedSurvey" >
              <h3>
                {{ $t("GroupedStatistics") }}
                <span class="stats-warning text-danger">&#x26a0;</span>
              </h3>
              <p class="text-danger">
                <b>{{ $t("GroupedStatisticsNotes") }} </b>
              </p>
              <table class="table">
                <thead>
                  <tr>
                    <th>
                      {{ $t("id") }}
                    </th>
                    <th>
                      {{ $t("Survey") }}
                    </th>
                    <th>
                      {{ $t("CommonQuestions") }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(addi, id) in additional" :key="id">
                    <td>{{ addi.id }}</td>
                    <td>{{ addi.title }}</td>
                    <td>{{ addi.common }}</td>
                  </tr>
                </tbody>
              </table>
              <hr />
            </div>
            <p>
              {{
                $t("SummaryQuestions", {
                  questionCount: data.questions,
                  questionGroupCount: data.questiongroups,
                })
              }}
            </p>
            <p>
              {{ $t("summaryResponses", { responsesCount: data.responses }) }}
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <main-container
              :theme="theme"
              :basecolors="basecolors"
              :questiongroups="questiongroups"
              :word-cloud-settings="wordCloudSettings"
              :printable="printable"
              :initial-chart-type="surveydata.initialChartType"
            />
          </div>
        </div>
      </div>
    </transition>
    <div
      class="modal fade"
      id="PublicStatistic--contact-modal"
      tabindex="-1"
      role="dialog"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{{ $t("Contact") }}</h4>
          </div>
          <div class="modal-body">
            <pre>{{ surveydata.contactinformation | trim }}</pre>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>

<script>
import MainContainer from "./components/MainContainer.vue";

export default {
  name: "app",
  components: {
    MainContainer,
  },
  props: {
    getDataUrl: { type: String, default: "" },
    wordCloudSettings: { type: Object, required: true },
    surveydata: { type: Object, required: true },
    theme: { type: String, default: "" },
    basecolors: {
      type: Array,
    },
    language: { type: String, default: "" },
  },
  data() {
    return {
      data: {},
      questiongroups: {},
      loading: true,
      printable: false,
      colors: this.basecolors,
      lang: this.language,
      groupedSurvey: this.groupedSurvey,
      additional: this.additional,
    };
  },
  computed: {
    questionAnchors() {
      return _.reduce(
        this.questiongroups,
        (coll, questions, gid) => {
          _.forEach(questions, (question) => {
            coll[question.fieldname] = question.aid;
          });
          return coll;
        },
        {}
      );
    },
  },
  methods: {
    test() {
      console.log(this.language);
    },

    showContactData() {
      $("#PublicStatistic--contact-modal").modal("show");
    },
    togglePrintable() {
      this.printable = !this.printable;
    },
    exportToPDF() {
      this.loading = true;
      this.createPDFworker()
        .then((res) => {
          this.loading = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    /*createPDFworker () {
            const aElementArray = $('.selector--question-panel');
            return new Promise(function (res, rej) {
                $('.selector--buttonrow').css('display','none');
                const createPDF = new CreatePDF();

                $.each(aElementArray, function (i, questionPanel) {
                    let sizes = { h: $(questionPanel).height(), w: $(questionPanel).width() };
                    let answerObject = createPDF('sendImg', { html: questionPanel, sizes: sizes });
                });

                createPDF('getParseHtmlPromise').then(function (resolve) {
                    var answerObject = createPDF('exportPdf');
                    var a = document.createElement('a');
                    if(typeof a.download != "undefined") {
                        $('body').append("<a id='exportPdf-download-link' style='display:none;' href='" + answerObject.msg + "' download='pdf-public-stats.pdf'></a>");// Must add sid and other info
                        $("#exportPdf-download-link").get(0).click();
                        $("#exportPdf-download-link").remove();
                        res('done');
                        return;
                    } 
                    var newWindow = window.open("about:blank", 600, 800);
                    newWindow.document.write("<html style='height:100%;width:100%'><iframe style='width:100%;height:100%;' src='"+answerObject.msg+"' border=0></iframe></html>");
                    $('.selector--buttonrow').css('display','');
                    res('done');
                }, function (reject) {
                    rej(reject);
                });
            });
        }  */
  },
  filters: {
    trim(string) {
      return string.trim();
    },
    forIndex(string) {
      const tmp = document.createElement("DIV");
      tmp.innerHTML = string;
      let txtContent = tmp.textContent || tmp.innerText || "";
      return txtContent.length > 35
        ? txtContent.substr(0, 13) + "[...]"
        : txtContent;
    },
  },
  created() {
    $.ajax({
      url: this.getDataUrl,
      method: "GET",
      xhrFields: {
        withCredentials: true,
      },
      success: (data) => {
        this.loading = false;
        this.questiongroups = data.questiongroups;
        this.data = data.data;
        this.groupedSurvey = data.GroupedStats;
        this.additional = data.additional;
        this.$i18n.locale = this.language;
      },
    });
  },
};
</script>

<style lang="scss" scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.slide-enter-active {
  -moz-transition-duration: 0.3s;
  -webkit-transition-duration: 0.3s;
  -o-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -moz-transition-timing-function: ease-in;
  -webkit-transition-timing-function: ease-in;
  -o-transition-timing-function: ease-in;
  transition-timing-function: ease-in;
}

.slide-leave-active {
  -moz-transition-duration: 0.3s;
  -webkit-transition-duration: 0.3s;
  -o-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -moz-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
  -webkit-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
  -o-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
  transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
}

.slide-enter-to,
.slide-leave {
  max-height: 100px;
  overflow: hidden;
}

.slide-enter,
.slide-leave-to {
  overflow: hidden;
  max-height: 0;
}
.navbar-brand img {
  height: 100%;
  min-height: 3rem;
}
.scrollable-menu {
  height: auto;
  max-height: 50vh;
  overflow-x: hidden;
  overflow-y: auto;
}

.loader-container {
  position: absolute;
  top: 0;
  left: 0;
  height: 100vh;
  width: 100vw;
  padding: 2vh 2vw;
  background: white;
}

.loader-row {
  margin-top: 35vh;
}
</style>
