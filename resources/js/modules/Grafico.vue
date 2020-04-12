<template>
  <svg :style="{height, width}" xmlns="http://www.w3.org/2000/svg">
    <template v-for="(data, id) in datas">
      <template v-for="(linea, index) in lineas(data.data)">
        <line :key="`lines-${id}-${index}`" v-bind="linea" :stroke="data.color" />
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
        this.loadData('gray', 'gripe española', 'spfluint'),
        this.loadData('blue', 'A H1N1 (muertes)', 'ah1n1', 'deaths'),
        this.loadData('lightblue', 'A H1N1 ()', 'ah1n1', 'confirmed'),
        this.loadData('red', 'covid (muertes)', 'covid', 'deaths'),
        this.loadData('salmon', 'covid (confirmados)', 'covid', 'confirmed'),
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
        const zx = 1;
        const zy = 2e-4;
        line.x1 = line.x1 * 4 * zx;
        line.x2 = line.x2 * 4 * zx;
        line.y1 = 100 - line.y1 * zy;
        line.y2 = 100 - line.y2 * zy;
        ///////
        line.x1 = line.x1 + '%';
        line.y1 = line.y1 + '%';
        line.x2 = line.x2 + '%';
        line.y2 = line.y2 + '%';
        res.push(line);
      });
      return res;
    },
    loadData(color, name, source, type) {
      const data = [];
      this.$api.user.call(1, source, {type}).then(plots => {
        let x1=0, y1=0;
        plots.forEach(({x,y}) => {
          data.push({
            x1, y1,
            x2:x, y2:y,
          });
          x1 = x; y1 = y;
        });
      });
      return {name, color, data};
    },
  },
  mounted() {
  },
};
</script>
