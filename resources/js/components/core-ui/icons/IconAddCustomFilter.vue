<template>
  <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="">
  <!-- Sliders -->
  <line x1="10" y1="35" x2="90" y2="35" class="line"/>
  <rect x="45" y="27" width="10" height="16" class="slider"/>
  <line x1="10" y1="70" x2="55" y2="70" class="line"/>
  <rect x="25" y="62" width="10" height="16" class="slider"/>

  <!-- Gear (simplified circle gear) -->
  <circle cx="80" cy="70" r="20" class="gear"/>

  <!-- Plus sign inside gear -->
  <line x1="80" y1="60" x2="80" y2="80" class="plus"/>
  <line x1="70" y1="70" x2="90" y2="70" class="plus"/>
</svg>

</template>

<script>
export default {
  name: "IconAddCustomFilter",
};
</script>

  <style>
.line {
    stroke: currentColor;
  stroke-width: 6;
  stroke-linecap: round;
}
.slider,
.gear {
  fill: currentColor;
  stroke: currentColor;
}

/* Keep the plus sign white */
.plus {
  stroke: white;
  stroke-width: 6;
  stroke-linecap: round;
}

  </style>
