<template>
  <svg :style="{height, width}" xmlns="http://www.w3.org/2000/svg">
    <template v-for="(data, id) in datas">
      <template v-for="(linea, index) in lineas(data)">
        <line :key="`lines-${id}-${index}`" v-bind="linea" stroke="gray" />
      </template>
    </template>
  </svg>
</template>

<script>
export default {
  path: "/",
  mixins: [window.ResourceMixin],
  data() {
    return {
      today: window.moment().format(),
      datas: [
        //this.loadData('spfluint'),
        this.loadData('ah1n1'),
        this.loadData('covid'),
      ],
    };
  },
  computed: {
    width() {
      return '100%';
    },
    height() {
      return '100%';
    },
  },
  methods: {
    lineas(data) {
      return this.transform(data);
    },
    transform(lines) {
      const res = [];
      lines.forEach(l => {
        const line  = {...l};
        line.y1 = 100 - line.y1 * 2e-5;
        line.y2 = 100 - line.y2 * 2e-5;
        line.x1 = line.x1 + '%';
        line.y1 = line.y1 + '%';
        line.x2 = line.x2 + '%';
        line.y2 = line.y2 + '%';
        res.push(line);
      });
      return res;
    },
    loadData(source) {
      const data = [];
      this.$api.user.call(1, source, {}).then(plots => {
        let x1=0, y1=0;
        plots.forEach(({x,y}) => {
          data.push({
            x1, y1,
            x2:x, y2:y,
          });
          x1 = x; y1 = y;
        });
      });
      return data;
    },
  },
  mounted() {
  },
};
</script>
