<template>
  <!-- <svg
    fill="none"
    class=""
    viewBox="0 0 24 24"
    stroke="currentColor"
    @click.stop="$emit('click')"
  >
    <path
      d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg> -->
   <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24" @click.stop="$emit('click')">
        <g fill="none">
            <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2zm3.53 6.47l-.084-.073a.75.75 0 0 0-.882-.007l-.094.08L12 10.939l-2.47-2.47l-.084-.072a.75.75 0 0 0-.882-.007l-.094.08l-.073.084a.75.75 0 0 0-.007.882l.08.094L10.939 12l-2.47 2.47l-.072.084a.75.75 0 0 0-.007.882l.08.094l.084.073a.75.75 0 0 0 .882.007l.094-.08L12 13.061l2.47 2.47l.084.072a.75.75 0 0 0 .882.007l.094-.08l.073-.084a.75.75 0 0 0 .007-.882l-.08-.094L13.061 12l2.47-2.47l.072-.084a.75.75 0 0 0 .007-.882l-.08-.094l-.084-.073l.084.073z" fill="currentColor"></path>
        </g>
    </svg>
</template>

<script>
export default {
  name: "IconDismissSolid",
}
</script>
