<template>
    <div v-if="isOpen" class="relative col-span-2">
        <fieldset :style="{ borderColor: this.is_enabled != 0 ? borderColor : computedColor }" class="relative col-span-2 border border-solid border-opacity-25 rounded-md p-4 pt-2">
            <legend :style="{ color: this.is_enabled != 0 ? legendColor : computedColor }" class="px-3 flex flex-row gap-3 items-center cursor-pointer font-medium tracking-tight" @click="toggle">

                <slot name="icon" class="size-5"></slot>

                <transition name="fade">
                    <h3 v-if="label" class="flex text-base uppercase font-medium">{{ label }}</h3>
                </transition>

                <transition name="fade">
                    <InfoButton v-if="info" :info="info" />
                </transition>

                <span v-if="collapsible" class="ml-auto w-5">
                    <HeroiconChevronDown v-if="!isOpen" @click="toggle"/>
                    <HeroiconChevronUp v-else @click="toggle"/>
                </span>
            </legend>

            <transition name="fade">
                <div v-if="isOpen" class="px-1 flex flex-col gap-3">
                    <slot />
                </div>
            </transition>
        </fieldset>
        <slot name="buttons" class="ml-auto" />
    </div>

    <div v-else :style="{ borderColor: this.is_enabled != 0 ? borderColor : computedColor }" class="col-span-2">

        <div :style="{ color: this.is_enabled != 0 ? legendColor : computedColor }" class="px-1 rounded-md flex flex-row gap-1 items-start cursor-pointer" @click="toggle">
            <transition name="fade">
                <span class="flex flex-row items-center gap-3 w-full">
                    <div class="w-3 border border-current"></div>

                    <slot name="icon" class="size-4"></slot>

                    <h3 v-if="label" class="flex text-base uppercase font-medium">{{ label }}</h3>

                    <transition name="fade">
                        <InfoButton v-if="info" :info="info" />
                    </transition>

                    <span v-if="collapsible" class="ml-auto w-5">
                        <HeroiconChevronDown v-if="!isOpen" @click="toggle"/>
                        <HeroiconChevronUp v-else @click="toggle"/>
                    </span>

                    <slot name="buttons" class="ml-auto" />
                    <div class="flex-grow border border-current"></div>
                </span>
            </transition>
        </div>
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
        // centered: {
        //     type: Boolean,
        //     default: false,
        // },
        // centeredCollapsed: {
        //     type: Boolean,
        //     default: false,
        // },
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
        // legendClass() {
        //     return this.centered ? 'px-1 text-center' : 'px-1';
        // },
        // collapsedLegendClass() {
        //     return this.isOpen ? this.legendClass : 'px-1';
        // },
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
</style>
