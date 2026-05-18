<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" :stroke-width="w" class="w-4 h-4" @click.stop="$emit('click')">
        <path d="M7 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</template>

<script>
    export default {
        name: "HeroiconChevronRight",
props: { w: { type: [String, Number], default: 1.5 } }
    }
</script>
