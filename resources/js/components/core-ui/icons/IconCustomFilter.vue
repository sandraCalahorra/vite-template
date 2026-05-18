<template>
<svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="">


  <!-- First slider line and slider rectangle -->
  <line x1="10" y1="35" x2="90" y2="35" class="line"/>
  <rect x="45" y="27" width="10" height="16" class="slider"/>

  <!-- Second slider line and slider rectangle -->
  <line x1="10" y1="70" x2="55" y2="70" class="line"/>
  <rect x="25" y="62" width="10" height="16" class="slider"/>

  <!-- Number "1" in bottom right corner -->
  <text x="90" y="85" class="number" text-anchor="end" dominant-baseline="bottom">{{ number }}</text>
</svg>

</template>

<script>
export default {
  name: "IconAddCustomFilter",
  props: {
    number: {
        type: Number,
        default: 1,
    }
  },
};
</script>
<style>
    .line,
.slider,
.number {
  fill: currentColor;
  stroke: currentColor;
}

.number {
  font-size: 45px;
  font-weight: bold;
  font-family: Arial Rounded MT, Arial, sans-serif;
}
  </style>
