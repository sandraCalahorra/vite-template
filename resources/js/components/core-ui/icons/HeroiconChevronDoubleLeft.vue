<template>
  <svg
    fill="none"
    class=""
    viewBox="0 0 24 24"
    stroke="currentColor"
    @click.stop="$emit('click')"
  >
    <path
      d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script>
export default {
  name: "HeroiconChevronDoubleLeft",
}
</script>
