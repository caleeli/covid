
<script>
import Grafico from './Grafico';

export default {
  path: "/derivada",
  extends: Grafico,
  data() {
    return {
      title: 'CASE O MUERTES NUEVOS POR SEMANA',
      changeUrl: '/',
    };
  },
  methods: {
    loadData(color, name, source, type) {
      return this.loadData0(color, name, 'derivada', { source, type });
    },
  },
};
</script>
