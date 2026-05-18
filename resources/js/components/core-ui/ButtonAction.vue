<template>
    <button
        @click.prevent="emitAction"
        class="tracking-wide rounded-md focus:outline-none hover:shadow-md focus:shadow-outline transform transition-all duration-300 ease-in-out flex items-center justify-center font-medium"
        :class="[
            baseClass,        // base transition & hover styles
            sizeClass,        // size-based styles
            colorClass,       // gradient color or empty if using customStyle
            customClass,      // user-provided additional Tailwind classes
            { 'opacity-50 cursor-not-allowed': disabled }
        ]"
        :style="customStyle"
        :disabled="disabled"
    >
        <!-- Slot allows full customization: text, icons, or both -->
        <span class="flex flex-row gap-1 items-center">
            <slot />
            <span v-if="label">{{ label }}</span>
        </span>
    </button>
</template>

<script>
export default {
    name: "ButtonAction",

    props: {
        label: String,

        disabled: {
            type: Boolean,
            default: false
        },

        /**
         * Preset color gradient (used only if no customStyle is provided)
         * Available options: 'primary', 'gray', 'green', 'red'
         */
        color: {
            type: String,
            default: "primary"
        },

        /**
         * Controls the button's size (width, height, font size, and padding)
         * Available options: 'xxs', 'xs', 'sm', 'md', 'lg'
         */
        size: {
            type: String,
            default: "md",
            validator: val => ["xxs", "xs", "sm", "md", "lg"].includes(val)
        },

        /**
         * Inline custom style overrides for advanced color/border control.
         * Example:
         * {
         *   backgroundColor: '#fff',
         *   border: '1px solid #2563eb',
         *   color: '#2563eb'
         * }
         */
        customStyle: {
            type: Object,
            default: null
        },

        /**
         * Extra Tailwind CSS classes to customize spacing, radius, hover states, etc.
         * Example: "rounded-lg hover:bg-blue-50"
         */
        customClass: {
            type: String,
            default: ''
        }
    },

    computed: {
        // Base transitions and hover effects applied to all buttons
        baseClass() {
            return "hover:brightness-105 transition-all duration-300 ease-in-out";
        },

        // Defines background and text colors for predefined color variants. Ignored if `customStyle` is set
        colorClass() {
            if (this.customStyle) return "";

            return {
                'bg-gradient-to-bl from-primary-300 to-primary-500 text-white': this.color === 'primary',
                'bg-gradient-to-bl from-gray-500 to-gray-700 text-white': this.color === 'gray',
                'bg-gradient-to-bl from-green-600 to-green-800 text-white': this.color === 'green',
                'bg-gradient-to-bl from-red-600 to-red-700 text-white': this.color === 'red',
                'bg-gradient-to-bl from-yellow-600 to-yellow-700 text-white': this.color === 'yellow',
                'bg-gradient-to-bl from-orange-600 to-orange-700 text-white': this.color === 'orange',
                'bg-gradient-to-bl from-sky-600 to-sky-700 text-white': this.color === 'sky',
                'bg-gradient-to-bl from-white to-white text-black border': this.color === 'white',
            };
        },

        // Sets the button’s overall size (height, width, text, and padding)
        sizeClass() {
            switch (this.size) {
                case 'xxs': return 'min-w-[50px] h-[20px] text-xs px-2';
                case 'xs': return 'min-w-[70px] h-[24px] text-xs px-2';
                case 'sm': return 'min-w-[90px] h-[32px] text-xs px-2';
                case 'lg': return 'min-w-[150px] h-[48px] text-base px-5';
                default:   return 'min-w-[120px] h-[40px] text-sm px-3';
            }
        }
    },

    emits: ['action'],

    methods: {
        emitAction() {
            if (!this.disabled) this.$emit("action");
        }
    }
};
</script>
