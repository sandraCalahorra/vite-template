<template>
    <a
        v-if="displayWrapper"
        :href="href"
        :class="className"
        class="no-underline flex items-center w-full h-full min-h-[30px] px-3 py-2"
        @click.prevent="emit"
    >
        <slot />
    </a>
    <template v-else>
        <slot />
    </template>
</template>

<script>
export default {
    name: 'LinkWrapper',
    props: {
        displayWrapper: Boolean,
        href: String,
        className: [String, Array, Object],
    },
    emits: ['interaction'],
    methods: {
        emit(e) {
            this.$emit('interaction', e);
        },
    },
};
</script>
