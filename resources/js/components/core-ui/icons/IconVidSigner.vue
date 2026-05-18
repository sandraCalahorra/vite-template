<template>
  <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 104.8 65.7" class="">
    <g>
      <g id="Capa_1">
        <path :style="pathStyle1" d="M101.3,3.4h0c3.5,3.5,3.5,9.1,0,12.7l-46.2,46.2c-3.5,3.5-9.2,3.5-12.7,0h0c-3.5-3.5-3.5-9.1,0-12.7L88.6,3.4c3.5-3.5,9.2-3.5,12.7,0Z"/>
        <path :style="pathStyle2" d="M22.2,38.2h0c-3.4-3.4-3.4-9.2,0-12.6L44,3.4c3.4-3.4,9.2-3.4,12.6,0h0c3.4,3.4,3.4,9.2,0,12.6l-21.8,22.2c-3.4,3.4-9.2,3.4-12.6,0Z"/>
        <circle :style="circleStyle" cx="9.7" cy="9.6" r="8.8" transform="translate(-1.3 17.6) rotate(-80.8)"/>
      </g>
    </g>
  </svg>
</template>

<script>
export default {
  name: "IconVidSignerSolid",
  data() {
    return {
      pathStyle1: {
        fill: 'none',
        stroke: '#00BF71',
        strokeWidth: '7',
        strokeMiterlimit: 10
      },
      pathStyle2: {
        fill: 'none',
        stroke: '#00BF71',
        strokeWidth: '7',
        strokeMiterlimit: 10
      },
      circleStyle: {
        fill: 'none',
        stroke: '#00BF71',
        strokeWidth: '7',
        strokeMiterlimit: 10
      }
    }
  }
}
</script>
