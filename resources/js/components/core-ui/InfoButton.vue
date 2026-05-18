<template>
    <div
        class="relative flex gap-2 items-center w-5"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <!-- ICON -->
        <component
            :is="isPinned ? 'IconDismissSolid' : 'IconInfo'"
            class="text-gray-700 cursor-pointer hover:text-gray-800 transition-colors"
            :class="{ 'pointer-events-none': keepInfoIcon }"
            @click="toggleInfo"
        />

        <!-- INFO FLOATING -->
        <transition name="fade-slide">
            <small
                v-show="isShowingInfo"
                class="border bg-white p-3 absolute ml-6 rounded-lg z-50 shadow-xl text-gray-800 leading-normal tracking-normal min-w-[20em]"
                :class="[classProp, { 'pointer-events-none': keepInfoIcon }]"
            >
                <span v-if="info" v-html="`<p>${info}</p>`"></span>
                <slot v-else />
            </small>
        </transition>
    </div>
</template>

<script>
export default {
    name: "InfoButton",
    props: {
        info: String,
        defaultOpen: { type: Boolean, default: false },
        keepInfoIcon: { type: Boolean, default: false },
        classProp: { type: [String, Array, Object], default: '' },
    },
    data() {
        return {
            isShowingInfo: this.defaultOpen,
            isPinned: false,
        };
    },
    methods: {
        handleMouseEnter() {
            if (!this.keepInfoIcon && !this.isPinned) {
                this.isShowingInfo = true;
            }
        },
        handleMouseLeave() {
            if (!this.keepInfoIcon && !this.isPinned) {
                this.isShowingInfo = false;
            }
        },
        toggleInfo() {
            if (!this.keepInfoIcon) {
                this.isPinned = !this.isPinned;
                this.isShowingInfo = this.isPinned;
            }
        },
    },
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-10px) translateY(-5px);
}
.fade-slide-enter-to,
.fade-slide-leave-from {
    opacity: 1;
    transform: translateX(0) translateY(0);
}
</style>
