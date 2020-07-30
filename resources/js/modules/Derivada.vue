
<script>
import Grafico from './Grafico';

export default {
  path: "/derivada",
  extends: Grafico,
  data() {
    return {
      title: ' POR SEMANA',
      changeUrl: '/',
    };
  },
  methods: {
    loadData(color, name, source, type, country, porMillon) {
      return this.loadData0(color, name, 'derivada', { source, type, country, porMillon });
    },
  },
};
</script>
