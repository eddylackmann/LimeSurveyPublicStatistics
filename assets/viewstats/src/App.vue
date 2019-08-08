<template>
    <article class="PSarticlecontainer">
        <nav class="navbar navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
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
                <ul class="nav navbar-nav">
                    <li class="active"><a href="#headline">Home</a></li>
                    <li class="dropdown">
                        <a 
                            href="#" 
                            class="dropdown-toggle" 
                            data-toggle="dropdown" 
                            role="button" 
                            aria-haspopup="true" 
                            aria-expanded="false"
                        >
                            Question list <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu scrollable-menu">
                            <li v-for="(question, questionAnchor) in questionAnchors" :key="questionAnchor">
                                <a :href="`#link-anchor--${questionAnchor}`" >{{question|forIndex}}</a>
                            </li>
                        </ul>
                    </li>
                    <li><a href="#contact" @click="showContactData">Contact</a></li>
                </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="page-header">
                        <h1>Public statistics for {{data.surveyname}}</h1>
                    </div>
                    <hr/>
                    <p>
                        This survey contains <b>{{data.questions}}</b> questions in <b>{{data.questiongroups}}</b> question groups.
                    </p>
                    <p>
                        A total of <b>{{data.responses}}</b> responses have been collected.
                    </p>
                </div>
            </div>
            <div class="row loaderrow" v-show="loading">
                <div id='loader' class=" loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center"
                    style="min-height: 100%;">
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
            <div class="row">
                <div class="col-xs-12">
                    <main-container :questiongroups="questiongroups" :word-cloud-settings="wordCloudSettings" :printable="printable" :initial-chart-type="surveydata.initialChartType"/>
                </div>
            </div>
        </div>
        <div class="modal fade" id="PublicStatistic--contact-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Your contact:</h4>
                    </div>
                    <div class="modal-body">
                        <pre>{{surveydata.contactinformation | trim}}</pre>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

<script>
import MainContainer from './components/MainContainer.vue'

export default {
  name: 'app',
  components: {
    MainContainer
  },
  props: {
      data: {type: Object, required: true},
      questiongroups: {type: Object, required: true},
      wordCloudSettings: {type: Object, required: true},
      surveydata: {type: Object, required: true},
  },
  data() {
      return {
          loading: false,
          printable: false
      }
  },
  computed: {
        questionAnchors(){
            return _.reduce(this.questiongroups, (coll, questions, gid) => {
                _.forEach(questions, question => {
                    coll[question.fieldname] = question.question;
                 });
                 return coll;
            }, {});
        }
  },
  methods: {
      showContactData(){
          $('#PublicStatistic--contact-modal').modal('show');
      },
      togglePrintable() {
          this.printable = !this.printable;
      },
      exportToPDF() {
          this.loading = true;
          this.createPDFworker().then(
              (res) => {
                  this.loading = false;
              }
          ).finally( ()=> {this.loading = false;});
        },
        createPDFworker () {
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
        }   
    },
    filters: {
        trim(string) {
            return string.trim();
        },
        forIndex(string) {
            const tmp = document.createElement("DIV");
            tmp.innerHTML = string;
            let txtContent = tmp.textContent || tmp.innerText || "";
            return txtContent.length > 35 ? txtContent.substr(0, 13)+'[...]' : txtContent;
        }
    }
}
</script>

<style lang="scss" scoped>

    .navbar-brand img{
        height: 100%;
        min-height: 3rem;
    }
    .scrollable-menu {
        height: auto;
        max-height: 50vh;
        overflow-x: hidden;
        overflow-y: auto;
    }

    $size:2em;
    $color:#61a161;

    .loaderrow {
        position: absolute;
        top: 0;
        left: 0;
        height: 96vh;
        width: 96vw;
        padding: 2vh 2vw;
        background: rgba(240,240,240,0.4);
    }

    .contain-pulse {
        display: flex;
        flex-flow:row wrap;
        justify-content: center;
        align-content:bottom;
        height: $size+1;
    }

    .square {
        background: $color;
        border-radius: 0.6em;
        box-sizing: border-box;
        height: $size;
        margin: $size/10;
        overflow: hidden;
        padding: $size/4;
        width: $size;
    }

    .animate-pulse {
        .square:nth-of-type(1) {
            animation: pulse ease-in-out 1.8s infinite 0.2s;
        }
        .square:nth-of-type(2) {
            animation: pulse ease-in-out 1.8s infinite 0.6s;
        }
        .square:nth-of-type(3) {
            animation: pulse ease-in-out 1.8s infinite 1.0s;
        }
        .square:nth-of-type(4) {
            animation: pulse ease-in-out 1.8s infinite 1.4s;
        }
    }

    @keyframes pulse {  
        0% {
            box-shadow: 0 0  1em $color;
        }
        50% {
            box-shadow: 0 0 0.3em lighten($color,30%);
            height: $size*0.5;
            margin-top: $size*0.5;
            opacity: 0.8;
        }
        100% {
            box-shadow: 0 0 1em $color;
        }
    }
</style>
