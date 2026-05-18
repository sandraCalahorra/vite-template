<template>
  <router-link
    :to="disabled ? '' : to"
    class="tracking-wide rounded-md focus:outline-none hover:shadow-md focus:shadow-outline transform transition-all duration-300 ease-in-out flex items-center justify-center font-medium"
    :class="[
      baseClass,
      sizeClass,
      colorClass,
      customClass,
      { 'opacity-50 cursor-not-allowed pointer-events-none': disabled }
    ]"
    :style="customStyle"
  >
    <span class="flex flex-row gap-1 items-center">
      <slot />
      <span v-if="label || text">{{ label || text }}</span>
    </span>
  </router-link>
</template>

<script>
export default {
  name: "ButtonRedirect",
  props: {
    to: {
      type: Object,
      required: true
    },
    label: String,
    text: String,
    disabled: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: "primary"
    },
    size: {
      type: String,
      default: "md",
      validator: val => ["xxs", "xs", "sm", "md", "lg"].includes(val)
    },
    customStyle: {
      type: Object,
      default: null
    },
    customClass: {
      type: String,
      default: ''
    }
  },
  computed: {
    baseClass() {
      return "hover:brightness-105 transition-all duration-300 ease-in-out";
    },
    colorClass() {
      if (this.customStyle) return "";
      return {
        'bg-gradient-to-bl from-primary-300 to-primary-500 text-white': this.color === 'primary',
        'bg-gradient-to-bl from-gray-500 to-gray-700 text-white': this.color === 'gray',
        'bg-gradient-to-bl from-green-600 to-green-800 text-white': this.color === 'green',
        'bg-gradient-to-bl from-red-600 to-red-700 text-white': this.color === 'red',
        'bg-gradient-to-bl from-yellow-600 to-yellow-700 text-white': this.color === 'yellow',
        'bg-gradient-to-bl from-orange-600 to-orange-700 text-white': this.color === 'orange',
        'bg-gradient-to-bl from-cyan-600 to-cyan-700 text-white': this.color === 'sky',
      };
    },
    sizeClass() {
      switch (this.size) {
        case 'xxs': return 'min-w-[50px] h-[20px] text-xs px-2';
        case 'xs': return 'min-w-[70px] h-[24px] text-xs px-2';
        case 'sm': return 'min-w-[90px] h-[32px] text-xs px-2';
        case 'lg': return 'min-w-[150px] h-[48px] text-base px-5';
        default:   return 'min-w-[120px] h-[40px] text-sm px-3';
      }
    }
  }
};
</script>
