<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title" v-html="renderQuestionHTML(question)" />
        </div>
        <div class="panel-body">
            {{question.calculations}}
        </div>
    </div>
</template>

<script>
export default {
  name: 'QuestionPanel',
  props: {
    question: {type: Object, required: true}
  },
  methods: {
      renderQuestionHTML(question) {
          return `<small>(${question.aid})</small> | ${question.question}`;
      }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
