<template>
    <svg
        class="animate-spin"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
    >
        <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="2"
        ></circle>
        <circle
            class="opacity-75"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-dasharray="32"
            stroke-dashoffset="0"
        ></circle>
    </svg>
</template>

<script>
export default {
    name: "IconLoading",
};
</script>
