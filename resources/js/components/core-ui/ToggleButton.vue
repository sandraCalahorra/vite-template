<template>
    <div class="flex items-center p-1 py-2">
        <label class="flex items-center cursor-pointer select-none">
            <div class="relative transition-all" :class="{ 'mr-4': !emptySlot }">
                <input
                    type="checkbox"
                    :checked="checked"
                    :disabled="disabled"
                    hidden
                    @change="$emit('input', $event.target.checked)"
                />

                <div
                    class="w-10 h-5 transition-all duration-300 ease-in-out rounded-full shadow-inner"
                    :class="[
                        disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
                        checked ? 'bg-gradient-to-r from-primary to-primary-light' : 'bg-gray-300'
                    ]"
                />

                <div
                    class="absolute top-0 left-0 transition-all duration-300 ease-in-out transform bg-white rounded-full shadow-md size-5"
                    :class="[
                        checked ? 'translate-x-5' : 'translate-x-0',
                        disabled ? 'opacity-70' : 'hover:scale-110'
                    ]"
                    :style="{
                        boxShadow: checked
                        ? '0 0 6px rgba(59, 130, 246, 0.6)'
                        : '0 0 3px rgba(0, 0, 0, 0.2)'
                    }"
                />
            </div>

            <span
                v-if="!emptySlot"
                class="ml-2 text-sm font-medium tracking-wide transition-colors duration-200"
                :class="checked ? 'text-primary' : 'text-gray-600'"
            >
                <slot />
            </span>
        </label>
    </div>
</template>

<script>
export default {
    name: "ToggleButton",
    props: {
        checked: {
        type: [Number, Boolean],
        default: false
        },
        disabled: {
        type: [Number, Boolean],
        default: false
        },
        emptySlot: {
        type: [Number, Boolean],
        default: false
        }
    },
    emits: ["input"]
};
</script>

<style scoped>
input:focus-visible + .toggle__line {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}
</style>
