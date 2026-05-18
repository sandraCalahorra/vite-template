<template>
    <div class="flex gap-2 items-center">
        <ToggleButton
            :checked="value"
            :disabled="disabled"
            :empty-slot="true"
            :is_nulled="is_nulled"
            :activeDotColor="activeDotColor"
            :inactiveDotColor="inactiveDotColor"
            :activeLineColor="activeLineColor"
            :inactiveLineColor="inactiveLineColor"
            @change="$emit('input', !value)"
            :style="disabled ? { opacity: 0.4 } : {}"
        />
        <label class="leading-6 font-normal text-gray-700 tracking-normal text-sm">
            {{ name }}
        </label>
    </div>
</template>

<script>
export default {
    name: "InputToggleButton",
    props: {
        value: {
            type: [Number, Boolean],
            default: false
        },
        name: {
            type: String,
            required: true
        },
        disabled: {
            type: Boolean,
            default: false
        },
        activeDotColor: {
            type: String,
            default: 'var(--primary)'
        },
        inactiveDotColor: {
            type: String,
            default: 'var(--primary)'
        },
        activeLineColor: {
            type: String,
            default: 'var(--primary-light)'
        },
        inactiveLineColor: {
            type: String,
            default: 'gray'
        },
        is_nulled: {
            type: Boolean,
            default: false
        }
    },
    emits: ['input']
};
</script>
