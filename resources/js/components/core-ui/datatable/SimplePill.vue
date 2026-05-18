<template>
    <span
        :style="{
            backgroundColor: !text ? color : lightenColor((color), 5),
            color: darkenColor((color), -30),
            borderColor: !text ? 'transparent' : darkenColor((color), -40),
            borderWidth: !text ? '1px' : '',
            borderStyle: 'solid'
        }"
        class="inline-block px-3 text-xs font-semibold leading-tight text-center break-words rounded-full"
        :class="!!text ? 'py-1': 'py-3'"
    >
        {{ text }}
    </span>
</template>

<script>
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default {
    name: "SimplePill",

    mixins: [AdminMixin],

    props: {
        text: {
            type: String,
            required: true,
        },
        color: {
            type: String,
            required: true,
        },
    },

    methods: {

        lightenColor(color, percent) {
            const num = parseInt(color?.replace("#", ""), 16),
                amt = Math.round(2.55 * percent),
                R = (num >> 16) + amt,
                G = (num >> 8 & 0x00FF) + amt,
                B = (num & 0x0000FF) + amt;

            return "#" + (
                0x1000000 +
                (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
                (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
                (B < 255 ? (B < 1 ? 0 : B) : 255)
            ).toString(16).slice(1).toUpperCase();
        },

    },
};
</script>
