/*
 *
 */

panel.plugin('bnomei/pageviewcounter', {
  fields: {
    viewcount: {
      props: {
        value: String,
        label: String
      },
      template: '<k-text-field v-model="value" name="viewcount" :label="label" :disabled="true" />'
    },
    lastvisited: {
      props: {
        value: String,
        label: String,
        format: String
      },
      computed: {
        lastvisitedFormatted: function () {
          return this.$library.dayjs(this.value).format(this.format);
        }
      },
      template: '<k-text-field :value="lastvisitedFormatted" name="lastvisited" :label="label" :disabled="true" />'
    }
  }
});
