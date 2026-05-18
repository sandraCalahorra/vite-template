<template>
    <div
        class="separator"
        :class="error ? 'bg-red-900 opacity-20' : 'bg-green-900 opacity-20'"
    >
    </div>
</template>

<script>
export default {
  name: "Separator",
  props: {
        error: {
            type: Boolean,
            default: true,
        },
    },
}
</script>

<style scoped>
  .separator {
    height: 1px;
  }
</style>
