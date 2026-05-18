<template>
  <svg
    fill="none"
    :class="customClass"
    viewBox="0 0 24 24"
    stroke="currentColor"
    @click.stop="$emit('click')"
  >
    <path
      d="M15.75 19.5L8.25 12l7.5-7.5"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script>
export default {
  name: "HeroiconChevronLeft",
props: {
    customClass: {
      type: String,
      default: "", // Default classes
    },
  }
}
</script>
