<template>
  <div class="d-flex flex-column h-100 w-100">
    <div class="text-white bg-dark d-flex w-100 p-0">
      <div class="flex-grow-1 p-1">
        {{ title }}
      </div>
      <div class="btn-group" role="group">
        <router-link class="btn btn-sm m-0 p-1" :class="{'btn-primary': !esDerivada, 'btn-dark': esDerivada}" style="text-transform: none;" :to="changeUrl">
          f(t)
        </router-link>
        <router-link class="btn btn-sm m-0 p-1" :class="{'btn-primary': esDerivada, 'btn-dark': !esDerivada}" style="text-transform: none;" :to="changeUrl">
          f'(t)
        </router-link>
      </div>
    </div>
    <svg :style="{height, width}" xmlns="http://www.w3.org/2000/svg" @click="regla" @touchmove="touchmove" @mousemove="mousemove">
      <template v-for="(data, id) in datas">
        <template v-for="(linea, index) in lineas(data.data, id)">
          <line :key="`lines-${id}-${index}`" v-bind="linea" :stroke="data.color" />
        </template>
      </template>
      <line :x1="`${ruleX}%`" :x2="`${ruleX}%`" y1="0" y2="100%" stroke="black" stroke-dasharray="5 5"></line>
    </svg>
    <div style="border-bottom: 2px solid black" class="d-flex w-100">
      <div class="flex-grow-1">
        <div v-for="(data, id) in datas" :key="`label-${id}`" class="text-black">
          <label :style="{background: data.color, width: '3em'}" class="mr-2">&nbsp;</label> {{ data.name }}
          = {{ formatNumber(Math.round(values[id])) }}
        </div>
      </div>
      <div>
        <div>
          eje x: 
          <select v-model="weeks">
            <option v-for="w in allweeks" :key="`o-${w}`" :value="w">{{ w }}</option>
          </select>
          semanas
        </div>
        <div>
          eje y: 
          <select v-model="maxY">
            <option v-for="w in escalasY" :key="`o-${w.value}`" :value="w.value" :selected="w.value == maxY">{{ w.name }}</option>
          </select>
          semanas
        </div>
        <hr>
        SEMANA = {{ Math.round(currentX) }}
        <div>
          <router-link to="/enlaces" class="btn btn-info btn-sm">Ver fuentes</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const format=require('format-number');
const formatNumber = format({prefix: '', suffix: ''});

export default {
  path: "/",
  mixins: [window.ResourceMixin],
  data() {
    const datas = [
        this.loadData('gray', 'gripe española (muertes)', 'spfluint'),
        this.loadData('salmon', 'covid (casos)', 'covid', 'confirmed'),
        this.loadData('lightblue', 'A H1N1 (casos)', 'ah1n1', 'confirmed'),
        this.loadData('red', 'covid (muertes)', 'covid', 'deaths'),
        this.loadData('blue', 'A H1N1 (muertes)', 'ah1n1', 'deaths'),
      ];
    const values = [];
    datas.forEach(d => values.push(0));
    const allweeks = [];
    for(let w=0; w < 60; w+=4) if (w> 0) allweeks.push(w);
    return {
      title: 'CASOS O MUERTES ACUMULADOS POR SEMANA',
      changeUrl: '/derivada',
      ruleX: 0,
      currentX: 0,
      today: window.moment().format(),
      values,
      datas,
      allweeks,
      weeks: 12,
      maxY: 100000,
      escalasY: [
        { name: 'cientos', value: 100 },
        { name: 'miles', value: 1000 },
        { name: '10xmiles', value: 10000 },
        { name: '100xmiles', value: 100000 },
        { name: 'millones', value: 1000000 },
      ]
    };
  },
  computed: {
    esDerivada() {
      return this.changeUrl === '/';
    },
    width() {
      return '100%';
    },
    height() {
      return '100%';
    },
  },
  methods: {
    formatNumber,
    mousemove(event) {
      if (event.target.nodeName !== "svg") {
        return;
      }
      if (event.buttons > 0) {
        this.moverRegla(event.offsetX / event.target.clientWidth * 100);
      }
    },
    touchmove(event) {
      if (event.target.nodeName !== "svg") {
        return;
      }
      this.moverRegla(event.changedTouches[0].clientX / event.target.clientWidth * 100);
    },
    regla(event) {
      if (event.target.nodeName !== "svg") {
        return;
      }
      this.moverRegla(event.offsetX / event.target.clientWidth * 100);
    },
    moverRegla(x) {
      this.ruleX = Math.max(0, x);
      const zx = 100 / this.weeks;
      const zy = 1 / this.maxY;
      this.currentX = this.ruleX / zx;
      this.datas.forEach((data, i) => {
        this.values[i] = 0;
        data.data.forEach(line => {
          if (this.currentX >= line.x2) {
            this.values[i] = line.y2;
          }
        });
      });
    },
    lineas(data, id) {
      return this.transform(data, id);
    },
    transform(lines, id) {
      const res = [];
      lines.forEach(l => {
        const line  = {...l};
        const zx = 100 / this.weeks;
        const zy = 1 / this.maxY;
        const valor = line.y2;
        line.x1 = line.x1 * zx;
        line.x2 = line.x2 * zx;
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
      return this.loadData0(color, name, source, { type });
    },
    loadData0(color, name, ...params) {
      const data = [];
      this.$api.user.call(1, ...params).then(plots => {
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
  watch: {
    datas: {
      deep: true,
      handler() {
        this.moverRegla(50);
      },
    },
  }
};
</script>
