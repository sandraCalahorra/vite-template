<template>
    <div
        class="relative col-span-full min-w-0 overflow-hidden bg-white border border-gray-200 rounded shadow-sm transition-all duration-300"
        :class="[
            isOpen ? 'mb-6' : 'mb-3',
            is_enabled == 0 ? 'border-red-200 ring-1 ring-red-50' : ''
        ]"
    >
        <!-- Header -->
        <div
            class="relative flex items-center gap-3 px-4 py-3 cursor-pointer select-none transition-all duration-200 group"
            :class="[
                isOpen ? 'bg-gray-50/70' : 'bg-white hover:bg-gray-50/50',
                is_enabled == 0 ? 'bg-red-50/30' : ''
            ]"
            @click="toggle"
        >
            <div
                v-if="isOpen"
                class="absolute left-0 top-0 bottom-0 w-1"
                :style="{ backgroundColor: computedColor }"
            ></div>

            <!-- Icon & Label Container -->
            <div
                class="flex items-center gap-3 flex-1 overflow-hidden transition-transform duration-200"
                :class="[isOpen ? '' : 'group-hover:translate-x-1']"
                :style="{ color: is_enabled != 0 ? legendColor : '#b91c1c' }"
            >
                <div class="flex items-center justify-center shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                    <slot name="icon" />
                </div>

                <h3 v-if="label" class="text-xs font-semibold uppercase tracking-normal truncate leading-none pt-0.5">
                    {{ label }}
                </h3>

                <InfoButton v-if="info" :info="info" class="shrink-0 scale-90 opacity-70 group-hover:opacity-100 transition-opacity" />
            </div>

            <!-- Buttons & Toggle -->
            <div class="flex items-center gap-3">
                <slot name="buttons" />

                <div v-if="collapsible" class="text-gray-400 group-hover:text-primary transition-all duration-300">
                    <div :class="[isOpen ? 'rotate-180' : 'rotate-0']" class="transition-transform duration-300">
                        <HeroiconChevronDown class="size-4 pointer-events-none" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <transition name="smooth-slide">
            <div v-show="isOpen" class="p-3 sm:p-6 flex flex-col gap-4 sm:gap-5 border-t border-gray-100/80">
                <slot />
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: "FormSection",
    props: {
        label: {
            type: String,
        },
        info: {
            type: String,
        },
        collapsible: {
            type: Boolean,
            default: false,
        },
        collapsed: {
            type: Boolean,
            default: false,
        },
        borderColor: {
            type: String,
            default: 'var(--primary-light)',
        },
        legendColor: {
            type: String,
            default: 'var(--primary)',
        },
        is_enabled: {
            type: [Boolean, Number],
            default: 1,
        },
    },
    data() {
        return {
            isOpen: true,
        };
    },
    mounted() {
        if (this.collapsed === true) {
            this.isOpen = false;
        }
    },
    computed: {
        computedColor() {
            if (this.is_enabled == 0) {
                return '#b81414'
            } else {
                return this.legendColor
            }
        },
    },
    methods: {
        toggle() {
            if (this.collapsible) {
                this.isOpen = !this.isOpen;
            }
        },
    },
};
</script>

<style scoped>
.smooth-slide-enter-active,
.smooth-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 2000px;
    overflow: hidden;
}

.smooth-slide-enter-from,
.smooth-slide-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
    transform: translateY(-10px);
}
</style>
