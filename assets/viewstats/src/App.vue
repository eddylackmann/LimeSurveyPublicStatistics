<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-default pull-right" @click="exportToPDF">Export to pdf</button>
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
                <main-container :questiongroups="questiongroups" :word-cloud-settings="wordCloudSettings"/>
            </div>
        </div>
    </div>
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
  },
  data() {
      return {
          loading: false
      }
  },
  methods: {
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
                    rej(arguments);
                });
            });
        }   
    }
}
</script>

<style scoped lang="scss">

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
  00.000% {
    box-shadow: 0 0  1em $color;
  }
  50.000% {
    box-shadow: 0 0 0.3em lighten($color,30%);
    height: $size*0.5;
    margin-top: $size*0.5;
    opacity: 0.8;
  }
  100.00% {
    box-shadow: 0 0 1em $color;
  }
  
}
</style>
