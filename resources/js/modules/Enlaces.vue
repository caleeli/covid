<template>
  <panel
    name="Fuentes de datos"
    icon="fa fa-link"
    class="panel-success"
  >
    <div>
      <a class="btn btn-info btn-sm w-100" href="https://www.vaccinestoday.eu/stories/100-years-spanish-flu-world-ready-next-pandemic/" target="_blank">
        100 years after Spanish Flu
      </a>
    </div>
    <div>
      <a class="btn btn-info btn-sm w-100" href="https://es.wikipedia.org/wiki/Pandemia_de_gripe_A_(H1N1)_de_2009-2010" target="_blank">
        Pandemia de gripe A (H1N1)
      </a>
    </div>
    <div>
      <a class="btn btn-info btn-sm w-100" href="https://es.m.wikipedia.org/wiki/Pandemia_de_gripe_de_1918" target="_blank">
        Pandemia de gripe de 1918
      </a>
    </div>
    <div>
      <a class="btn btn-info btn-sm w-100" href="https://pomber.github.io/covid19/timeseries.json" target="_blank">
        Covid timeline
      </a>
    </div>
    <hr>
    <div>
      <router-link to="/" class="btn btn-secondary btn-sm">Volver</router-link>
    </div>
  </panel>
</template>

<script>
export default {
  path: "/enlaces",
};
</script>
