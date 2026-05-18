<template>
    <div class="relative flex flex-col items-start">
        <div class="relative w-full">
            <!-- Icon container -->
            <div :class="['icon-container', { 'text-primary': isFocused }]" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <component :is="currentIcon" class="size-4" />
            </div>

            <Field
                :type="inputType"
                :name="name"
                :id="name"
                :rules="rules"
                v-model="modelValue"
                class="input-field"
                :placeholder="placeholder"
                :maxlength="maxlength"
                :tabindex="tabindex"
                :disabled="disabled"
                @input="updateValue($event.target.value)"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Eye icon for password visibility toggle -->
            <div v-if="(name === 'password' || name === 'password_confirmation')" class="absolute right-2 top-0 bottom-0 mt-1 flex items-center">
                <transition name="fade-scale" mode="out-in">
                    <component :is="passwordIcon" :key="isPasswordVisible" @click="togglePasswordVisibility" class="cursor-pointer text-primary size-5" />
                </transition>
            </div>
        </div>

        <ErrorMessage class="error-message" :name="name" />
    </div>
</template>

<script>
import { defineComponent, computed, ref } from 'vue';
import { Field, Form, ErrorMessage } from 'vee-validate';

import IconEnvelope from '@/components/core-ui/icons/IconEnvelope.vue';
import IconLockClosed from '@/components/core-ui/icons/IconLockClosed.vue';
import IconEye from '@/components/core-ui/icons/IconEye.vue';
import IconEyeSlash from '@/components/core-ui/icons/IconEyeSlash.vue';

export default defineComponent({
    name: 'StyledInputText',

    components: {
        Field,
        Form,
        ErrorMessage,
        IconEnvelope,
        IconLockClosed,
        IconEye,
        IconEyeSlash,
    },

    props: {
        modelValue: {
        type: [String, Number],
        default: '',
        },
        value: {
        type: String,
        default: '',
        },
        rules: {
        type: [String, Object],
        default: '',
        },
        name: {
        type: String,
        default: '',
        },
        type: {
        type: String,
        default: 'text',
        },
        disabled: {
        type: Boolean,
        default: false,
        },
        placeholder: {
        type: String,
        default: '',
        },
        maxlength: {
        type: String,
        default: '',
        },
        tabindex: {
        type: String,
        default: '0',
        },
    },

    setup(props, { emit }) {
        const isPasswordVisible = ref(props.type === 'text');
        const isFocused = ref(false);

        // here add as needed to assign the icon to left of the input based of the property name
        const iconMap = {
            email: IconEnvelope,
            password: IconLockClosed,
            password_confirmation: IconLockClosed,
        };

        const modelValue = computed({
            get: () => props.modelValue,
            set: (value) => emit('update:modelValue', value),
        });

        const inputType = computed(() => {
            return (props.name === 'password' || props.name == 'password_confirmation') && !isPasswordVisible.value ? 'password' : 'text';
        });

        const currentIcon = computed(() => {
            return iconMap[props.name] || null;
        });

        const passwordIcon = computed(() => {
            return isPasswordVisible.value ? IconEyeSlash : IconEye;
        });

        const togglePasswordVisibility = () => {
            isPasswordVisible.value = !isPasswordVisible.value;
        };

        const updateValue = (value) => {
            emit('update:modelValue', value);
        };

        const handleFocus = () => {
            isFocused.value = true;
        };

        const handleBlur = () => {
            isFocused.value = false;
        };

    return {
        modelValue,
        inputType,
        currentIcon,
        passwordIcon,
        togglePasswordVisibility,
        updateValue,
        isFocused,
        isPasswordVisible,
        handleFocus,
        handleBlur,
    };
  },
});
</script>

<style scoped>
.input-field {
    @apply block w-full px-10 py-2 bg-transparent border-b-2 border-gray-400 outline-none;
    transition: border-color 0.3s;
}

.input-field:focus {
    @apply border-primary;
}

.icon-container {
    color: #6f727a;
    transition: color 0.3s;
}

.icon-container.text-primary {
    color: var(--primary);
}

.error-message {
    @apply text-red-600 mt-1;
}

.relative {
    position: relative;
}

.flex {
    display: flex;
}

.flex-col {
    flex-direction: column;
}

.pointer-events-none {
    pointer-events: none;
}

</style>
