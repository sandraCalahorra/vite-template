<template>
    <div
        class="flex justify-center items-center"
        :class="disabled ? 'opacity-30 cursor-not-allowed' : 'cursor-grab drag-handle'"
    >
        <IconDrag class="size-4" />
    </div>
</template>

<script>
export default {
    name: 'DragHandle',
    props: {
        disabled: Boolean,
    },
};
</script>
