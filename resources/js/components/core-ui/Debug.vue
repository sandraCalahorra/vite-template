<template>
  <div class="bg-black text-white p-2 px-3 w-full flex flex-col gap-2 rounded text-left">
    
    <div class="flex flex-col text-red-700">
      <span class="font-semibold">{{ t }}</span>
    </div>

    <code>

      <component :is="pre ? 'pre' : 'div'">
        <span v-show="c">{{ c }}</span>
        <span v-show="!c">
          <slot />
        </span> 
      </component>

    </code>
  </div>
</template>

<script>
export default {
  name: "Debug",

  props: {
    t: {
      type: String,
      default: 'Debug',
    },
    c: {
      type: [Array, Object, String, Number],
      default: undefined,
    },
    pre: {
      type: Boolean,
      default: false,
    }
  }
  
};
</script>

<style>
</style>