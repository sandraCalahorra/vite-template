<template>
    <div>
      <!-- Header with toggle button -->
        <div
            class="flex items-center px-3 cursor-pointer"
            @click="toggleExpand"
            :class="{
                'justify-center': !title,   // centrar icono si no hay título
                'justify-between': title    // separar si hay título
            }"
        >
            <span
                v-if="title"
                class="text-xs font-medium mix-blend-multiply opacity-90 tracking-wide text-gray-200"
            >
                {{ title }}
            </span>

            <IconChevronUp
                class="size-6 text-white hover:text-primary-light transition-transform duration-200 ease-in-out"
                :class="isExpanded ? '-rotate-180' : ''"
                :title="isExpanded ? 'Contraer' : 'Expandir'"
            />

        </div>

      <!-- Content that expands or collapses with sliding and fading effect -->
        <transition
            name="dropdown"
            appear
            @before-enter="beforeEnter"
            @enter="enter"
            @after-enter="afterEnter"
            @before-leave="beforeLeave"
            @leave="leave"
            @after-leave="afterLeave"
        >
            <div v-show="isExpanded" ref="content" class="flex flex-col mt-0 overflow-hidden">
                <slot />
            </div>
        </transition>
    </div>
</template>

<script>
  export default {
    props: {
        title: {
            type: String,
            required: true,
        },
        initialExpanded: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            isExpanded: this.initialExpanded,
        };
    },
    methods: {
        toggleExpand() {
            this.isExpanded = !this.isExpanded;
        },
        beforeEnter(el) {
            el.style.transition = '';
            el.style.overflow = 'hidden';
            el.style.maxHeight = '0';
            el.style.opacity = '0.6';
        },
        enter(el) {
            el.style.transition = 'max-height 0.15s ease, opacity 0.15s ease';
            requestAnimationFrame(() => {
                el.style.maxHeight = `${el.scrollHeight}px`;
                el.style.opacity = '1';
            });
        },
        afterEnter(el) {
            el.style.transition = '';
            el.style.maxHeight = 'none';
            el.style.opacity = '';
            el.style.overflow = '';
        },
        beforeLeave(el) {
            el.style.transition = '';
            el.style.overflow = 'hidden';
            el.style.maxHeight = `${el.scrollHeight}px`;
            el.style.opacity = '1';
        },
        leave(el) {
            el.style.transition = 'max-height 0.15s ease, opacity 0.15s ease';
            requestAnimationFrame(() => {
                el.style.maxHeight = '0';
                el.style.opacity = '0.6';
            });
        },
        afterLeave(el) {
            el.style.transition = '';
            el.style.maxHeight = '';
            el.style.opacity = '';
            el.style.overflow = '';
        },
    },
  };
  </script>
