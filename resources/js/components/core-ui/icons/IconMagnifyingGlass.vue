<template>
<svg
    class=" ml-2 fill-current outline-none"
    viewBox="0 0 24 24"
    xmlns="http://www.w3.org/2000/svg"
>
    <path
        d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"
    />
</svg>
</template>

<script>
export default {
  name: "IconMagnifyingGlass",
  props: {
    strokeWidth: {
      type: String,
      default: "1"
    }
  }
}
</script>
