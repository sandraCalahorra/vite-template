<template>
    <div class="w-full" :class="{'sm:grid sm:grid-cols-2 items-start': inline}">
        <label
            class="flex items-center leading-6 text-sm font-medium relative align-bottom gap-1"
            :for="label"
            :class="{'required': required, 'grid s-500:flex': info, labelColor}"
        >
            <!-- <span v-html="label"></span> -->
            <span v-if="!html">{{ label }}</span>
            <span v-else v-html="label"></span>

            <IconCheck v-show="isValid" class="size-5 text-green-600" />

            <InfoButton v-if="$slots.infoTextSlot" class="z-30">
                <slot name="infoTextSlot" />
            </InfoButton>

            <InfoButton
                v-else-if="info"
                :info="info"
                class="z-30"
            />
        </label>

        <div class="sm:col-span-1 text-base">
            <slot />
        </div>
    </div>
</template>

<script>
    import { defineComponent } from 'vue';
    import IconCheck from './icons/IconCheck.vue'; // Import your IconCheck component

    export default defineComponent({
        name: "InputWrapper",

        props: {
            label: {
                type: String,
                required: true
            },
            labelColor: {
                type: String,
                default: 'text-gray-700'
            },
            inline: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            info: {
                type: String,
            },
            isValid: {
                type: Boolean,
                default: false
            },
            html: {
                type: Boolean,
                default: false
            },

        },

        components: {
            IconCheck
        }
    });
</script>
