<template>
    <div class="container">
        <div class="row scoped-questiongrouprow" v-for="(questions,gid) in questiongroups" :key="gid" >
            <questionpanel v-for="question in questions" :key="question.fieldname" :question="question" :word-cloud-settings="wordCloudSettings"/>
        </div>
    </div>
</template>

<script>

import Questionpanel from './Questionpanel.vue'

export default {
  name: 'MainContainer',
  components: {
      Questionpanel
  },
  props: {
    questiongroups: {type: Object, required: true},
    wordCloudSettings: {type: Object, required: true}
  },
  data(){
      return {}
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss" scoped >
    .scoped-questiongrouprow {
        padding-top: 2rem;
        border-bottom: 2px solid #929292;
    }
</style>
