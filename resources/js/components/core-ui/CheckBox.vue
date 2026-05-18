<template>
    <div class="flex flex-row gap-2">
        <label
            class="flex items-center gap-2 select-none"
            :class="{ 'opacity-60 cursor-not-allowed': disabled }"
        >
            <input
                type="checkbox"
                :id="id"
                :checked="modelValue"
                :disabled="disabled"
                @change="$emit('update:modelValue', $event.target.checked)"
                class="hidden cursor-pointer"
            />

            <div
                class="relative flex items-center justify-center transition-all duration-200 border rounded-[0.25rem]"
                :class="[
                    modelValue
                        ? 'bg-gradient-to-bl from-primary to-primary-700 border-primary-700'
                        : 'bg-white border-gray-400 hover:border-primary/60',
                    sizeClass.box
                ]"
            >
                <!-- Check con animación -->
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    class="text-white transition-transform duration-200 transform"
                    :class="[
                        modelValue ? 'scale-100 opacity-100' : 'scale-0 opacity-0',
                        sizeClass.icon
                    ]"
                >
                    <path
                        d="M5 13l4 4L19 7"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="checkmark-path"
                        :class="{ active: modelValue }"
                    />
                </svg>
            </div>
        </label>
        <span class="text-sm font-medium tracking-wide">
            <slot />
        </span>
    </div>
</template>

<script>
export default {
    name: "BaseCheckbox",
    props: {
        id: {
            type: [String, Number],
            default: null,
        },
        modelValue: {
            type: [ Boolean, String, Number ],
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        size: {
            type: String,
            default: 'md',
        },
    },
    emits: ["update:modelValue"],
    computed: {
        sizeClass() {
            return {
                sm: { box: 'w-4 h-4', icon: 'w-3 h-3' },
                md: { box: 'w-5 h-5', icon: 'w-3.5 h-3.5' },
                lg: { box: 'w-6 h-6', icon: 'w-4 h-4' }
            }[this.size] || { box: 'w-5 h-5', icon: 'w-3.5 h-3.5' };
        }
    }
};
</script>

<style scoped>
.checkmark-path {
    stroke-dasharray: 22;
    stroke-dashoffset: 22;
    transition: stroke-dashoffset 0.25s ease-in-out;
}

.checkmark-path.active {
    stroke-dashoffset: 0;
}
</style>
