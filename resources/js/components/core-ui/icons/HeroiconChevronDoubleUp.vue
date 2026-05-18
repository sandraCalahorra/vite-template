<template>
  <svg
    fill="none"
    class=""
    viewBox="0 0 24 24"
    stroke="currentColor"
    @click.stop="$emit('click')"
  >
    <path
      d="M4.5 12.75l7.5-7.5 7.5 7.5m-15 6l7.5-7.5 7.5 7.5"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script>
export default {
  name: "HeroiconChevronDoubleUp",
}
</script>
