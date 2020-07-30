<template>
  <div class="d-flex flex-column h-100 w-100">
    <div class="text-white bg-dark d-flex w-100 p-0">
      <div class="flex-grow-1 p-1">
        {{ variable == 'deaths' ? 'MUERTES' : 'CASOS' }} {{ title }} {{porMillon ? 'POR MILLON DE HABITANTES' : ''}}
      </div>
      <div class="btn-group" role="group">
        <button
          class="btn btn-sm m-0 p-1"
          :class="{'btn-primary': !porMillon, 'btn-dark': porMillon}"
          style="text-transform: none;"
          @click="porMillon=false"
        >
          TOTAL
        </button>
        <button
          class="btn btn-sm m-0 p-1"
          :class="{'btn-primary': porMillon, 'btn-dark': !porMillon}"
          style="text-transform: none;"
          @click="porMillon=true"
        >
          POR MILLON DE HABITANTES
        </button>
      </div>
      &nbsp;_&nbsp;
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
      <line
        v-for="p in escalaX"
        :key="`escalaX-${p.x}`"
        :x1="`${p.x}%`" :x2="`${p.x}%`" y1="99%" y2="100%" stroke="gray"></line>
      <template v-for="p in escalaY">
        <line
          :key="`escalaY-${p.y}`"
          :y1="`${100-p.y}%`" :y2="`${100-p.y}%`" x1="0%" x2="4px" stroke="gray"></line>
        <text
          :key="`escalaY-text-${p.y}`"
          :y="`${100-p.y}%`" x="4px">{{ p.l }}</text>
      </template>
      <text
        v-for="p in labels"
        :key="`chart-labels-${p.name}`"
        :fill="p.color"
        :x="`${p.x}%`" :y="`${p.y}%`">{{ p.name }}</text>
    </svg>
    <svg :style="{height: '2em', width}" xmlns="http://www.w3.org/2000/svg">
      <text
        v-for="p in escalaMes"
        :key="`escalaX-${p.x}`"
        :x="`${p.x}%`" y="60%">{{ p.l }}</text>
    </svg>
    <div style="border-bottom: 2px solid black" class="d-flex w-100">
      <div class="d-flex flex-column">
        <textarea v-model="paises" class="flex-grow-1"></textarea>
        <div class="d-flex">
          <select v-model="variable" class="flex-grow-1">
            <option>confirmed</option>
            <option>deaths</option>
            <option>mortalidad</option>
          </select>
          <button class="btn btn-info" @click="refresh"><i class="fas fa-play"></i></button>
        </div>
      </div>
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
        SEMANA = {{ Math.round(currentX) }}<br>
        Fecha = {{ moment(currentDate).format('YYYY-MM-DD') }}
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
    const colors = [
      'blue', //1 2 3 4 5 6 7
      'lime',
      'cyan',
      'salmon',
      'fuchsia',
      'yellow',
      'gray',
    ];
    const variable = (localStorage.variable || 'confirmed');
    const porMillon = (localStorage.porMillon == '1');
    const countries = (localStorage.countries || 'Bolivia\nPeru\nChile').split('\n');
    const datas = [];
    let paises = [];
    countries.forEach((c,i) => {
      if (c = c.trim()) {
        if (i < colors.length) {
          paises.push(c);
          datas.push(this.loadData(colors[i], c, 'covid', variable, c, porMillon));
        }
      }
    });
    const values = [];
    datas.forEach(d => values.push(0));
    const allweeks = [];
    for(let w=0; w < 60; w+=4) if (w> 0) allweeks.push(w);
    return {
      title: ' ACUMULADO POR SEMANA',
      porMillon,
      paises: paises.join('\n'),
      variable,
      changeUrl: '/derivada',
      ruleX: 0,
      currentX: 0,
      today: window.moment().format(),
      values,
      datas,
      allweeks,
      weeks: global.weeks || 32,
      maxY: global.maxY || 1000,
      escalasY: [
        { name: 'unidad', value: 0.1 },
        { name: 'decenas', value: 1 },
        { name: 'cientos', value: 10 },
        { name: 'miles', value: 100 },
        { name: '10xmiles', value: 1000 },
        { name: '100xmiles', value: 10000 },
        { name: 'millones', value: 100000 },
        { name: '10xmillones', value: 1000000 },
      ]
    };
  },
  computed: {
    labels() {
      const labels = [];
      this.datas.forEach(serie => {
        if (serie.data.length) {
          const zx = 100 / this.weeks;
          const zy = 1 / this.maxY;
          const x = serie.data[serie.data.length - 1].x2 * zx;
          const y = 100 - serie.data[serie.data.length - 1].y2 * zy;
          labels.push({
            name: serie.name,
            color: serie.color,
            x,
            y,
          });
        }
      });
      return labels;
    },
    escalaMes() {
      const points = [];
      const zx = 100 / this.weeks;
      const zy = 1 / this.maxY;
      let last = '';
      for(let i=0, l=this.weeks; i<l; i+=0.5) {
        const date = new Date('2020-01-22');
        date.setDate(date.getDate() + i * 7);
        const mes = moment(date).format('MMMM');
        if (mes!==last) {
          points.push({
            x: i * zx,
            l: mes,
          });
          last = mes;
        }
      }
      return points;
    },
    escalaX() {
      const points = [];
      const zx = 100 / this.weeks;
      const zy = 1 / this.maxY;
      for(let i=0, l=this.weeks; i<l; i++) {
        points.push({
          x: i * zx,
          l: i,
        });
      }
      return points;
    },
    escalaY() {
      const points = [];
      const zx = 100 / this.weeks;
      const zy = 1 / this.maxY;
      const step = this.maxY / 10 * 100;
      for(let i=0, l=this.maxY * 100; i<l; i+=step) {
        points.push({
          y: i * zy,
          l: i < 1 ? `0.${String(Math.round(i * 100))}` : i,
        });
      }
      return points;
    },
    currentDate() {
      const date = new Date('2020-01-22');
      date.setDate(date.getDate() + this.currentX * 7);
      return date;
    },
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
    refresh() {
      localStorage.countries = this.paises;
      localStorage.variable = this.variable;
      location.reload();
    },
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
    loadData(color, name, source, type, country = 'total', porMillon) {
      return this.loadData0(color, name, source, { type, country, porMillon });
    },
    loadData0(color, name, fn, params) {
      const data = [];
      this.$api.user.call(1, fn, params).then(plots => {
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
    porMillon(v) {
      localStorage.porMillon = v ? '1' : '0';
      location.reload();
    },
    datas: {
      deep: true,
      handler() {
        this.moverRegla(50);
      },
    },
    weeks(value) {
      global.weeks = value;
    },
    maxY(value) {
      global.maxY = value;
    },
  }
};
</script>
