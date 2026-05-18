<template>
  <svg
    fill="none"
    :class="customClass"
    viewBox="0 0 24 24"
    stroke="currentColor"
    @click.stop="$emit('click')"
  >
    <path
      d="M19.5 8.25l-7.5 7.5-7.5-7.5"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<script>
export default {
  name: "HeroiconChevronDown",
props: {
    customClass: {
      type: String,
      default: "", // Default classes
    },
  }
}
</script>
