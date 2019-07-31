<template>
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
        <div class="row">
            <div class="col-xs-12">
                <main-container :questiongroups="questiongroups"/>
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
  }
}
</script>

<style>

</style>
