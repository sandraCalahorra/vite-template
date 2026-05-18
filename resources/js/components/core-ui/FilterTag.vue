<template>
  <div
    :class="'flex flex-row flex-shrink-0 gap-1 md:px-1 md:pr-0 text-xs text-white items-center overflow-hidden ' + customClass"
  >
    <div class="flex flex-row-reverse md:flex-row gap-1 flex-shrink-0 md:py-0 pr-2 md:pr-0 pl-0 md:pl-1 items-center">
      <div class="flex flex-row gap-1">
        <!-- Title with fallback text if not provided -->
        <span class="font-semibold cursor-default">{{ title || 'Sin título' }}</span>
        <span v-if="value" class="cursor-default">({{ value }})</span>
      </div>
      <div
        class="p-1 flex flex-row items-center"
        :class="[
          other
            ? 'cursor-default pointer-events-none text-gray-500'
            : 'hover:bg-black cursor-pointer text-white'
        ]"
        @click="!other && $emit('click:remove-filter')"
        style="height: 1.5rem;"
      >
        <!-- Heroicon with aria-label for accessibility -->
        <HeroiconXMark v-if="!other" class="w-4 max-w-4 pointer-events-none" aria-label="Eliminar filtro" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "FilterTag",

  props: {
    title: {
      type: String,
      default: '',
      required: true,
    },
    value: {
      type: [String, Boolean],
      default: '',
    },
    customClass: {
      type: String,
      default: "bg-gray-700 rounded-md", // Default classes
    },
    other: {
      type: Boolean,
      default: false,
    },
    otherObject: {
      type: Boolean,
      default: false,
    }
  },
};
</script>
