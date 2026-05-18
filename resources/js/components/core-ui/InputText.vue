<template>
    <div>
        <div v-if="type !== 'textarea'">
            <div
                class="flex items-stretch"
                :class="hasActions && 'border border-gray-300 rounded-[0.25rem] overflow-hidden'"
            >
                <Field
                    :type="type"
                    :name="name"
                    :id="name"
                    :rules="rules"
                    v-model="modelValue"
                    class="w-full px-2 py-1 text-sm focus:outline-none"
                    :class="hasActions
                        ? 'border-0 rounded-none'
                        : 'border border-gray-300 rounded-[0.25rem] focus:border-primary'
                    "
                    :placeholder="placeholder"
                    :maxlength="maxlength"
                    :tabindex="tabindex"
                    :disabled="isDisabled"
                    :readonly="readOnly"
                    @input="updateValue($event.target.value)"
                />

                <!-- Spinner -->
                <div v-if="loading" class="flex items-center justify-center">
                    <div class="border-2 border-transparent rounded-full size-4 border-t-green-500 custom-spinner"></div>
                </div>

                <!-- Lock Icon -->
                <span
                    v-if="(currentIcon && userObj?.level <= 10 && isDisabled)"
                    @click="handleIconClick"
                    class="flex items-center justify-center px-2 text-black cursor-pointer border-l hover:bg-primary-light hover:text-white"
                >
                    <component
                        v-if="currentIcon"
                        :is="currentIcon"
                        class="p-1 size-6"
                    />
                </span>

                <!-- Copy Icon -->
                <span
                    v-if="showCopy"
                    @click="copyToClipboard"
                    class="flex items-center justify-center px-2 text-primary cursor-pointer border-l hover:bg-primary-light hover:text-white"
                >
                    <component
                        v-if="copyIcon"
                        :is="copyIcon"
                        class="w-4 h-4"
                    />
                </span>
                <!-- Open Icon -->
                <span
                    v-if="showOpen"
                    @click="openLink"
                    class="flex items-center justify-center px-2 text-primary cursor-pointer border-l hover:bg-primary-light hover:text-white"
                >
                    <component
                        v-if="openIcon"
                        :is="openIcon"
                        class="w-4 h-4"
                    />
                </span>
            </div>

            <ErrorMessage class="text-red-600 text-xs italic underline" :name="name" />
        </div>

        <!-- TEXTAREA -->
        <div v-else-if="type == 'textarea'">
            <Field
                :id="name"
                :name="name"
                :rules="rules"
                v-model="modelValue"
            >
                <textarea
                    v-model="modelValue"
                    class="block w-full px-1 py-1 tracking-wide border border-gray-300 rounded-[0.25rem] form-input"
                    cols="30"
                    :placeholder="placeholder"
                    :rows="rows"
                    :disabled="disabled"
                ></textarea>
            </Field>
            <ErrorMessage class="text-red-600 text-xs italic underline" :name="name" />
        </div>

        <!-- MODAL -->
        <TheModal
            :open="showConfirm"
            title="Modificar"
            :hasFooter="true"
            :hasHeader="true"
            :actionButton="null"
            @close="cancelToggle"
        >
            <template #body>
                <p class="pr-8 leading-7 tracking-wide text-gray-800">
                    Estás a punto de desbloquear el campo <strong>{{ fieldName }}</strong>. ¿Deseas continuar?
                </p>
            </template>

            <template #action>
                <ButtonAction
                    :label="'Sí'"
                    size="sm"
                    color="green"
                    @click="confirmToggle"
                />
            </template>
        </TheModal>
    </div>
</template>

<script>
import { ref, defineComponent, computed } from 'vue';
import { Field, Form, ErrorMessage } from 'vee-validate';

export default defineComponent({
    name: 'InputText',
    components: { Field, Form, ErrorMessage },

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
        rows: {
            type: String,
            default: '5',
        },
        maxlength: {
            type: String,
            default: '',
        },
        tabindex: {
            type: String,
            default: '0',
        },
        loading: {
            type: Boolean,
            default: false,
        },
        currentIcon: {
            type: [String, Array],
            default: '',
        },
        iconRightClass: {
            type: String,
            default: '',
        },
        userObj: {
            type: Object,
            default: null
        },
        fieldName: {
            type: String,
            default: ''
        },
        showCopy: {
            type: Boolean,
            default: false
        },
        copyIcon: {
            type: String,
            default: ''
        },
        readOnly: {
            type: Boolean,
            default: false
        },
        showOpen: {
            type: Boolean,
            default: false
        },
        openIcon: {
            type: String,
            default: ''
        }

    },

    setup(props, { emit }) {
        const modelValue = computed({
            get: () => props.modelValue,
            set: (value) => emit('update:modelValue', value),
        });

        const updateValue = (value) => emit('update:modelValue', value);

        const currentIcon = ref(
            Array.isArray(props.currentIcon) ? props.currentIcon[0] : props.currentIcon
        );

        const isDisabled = computed(() => {
            if (!currentIcon) return props.disabled;
            const isCurrentMatch =
                Array.isArray(props.currentIcon) &&
                props.currentIcon[0] === currentIcon.value;
            return props.disabled && isCurrentMatch;
        });

        const hasActions = computed(() => (
            props.showCopy ||
            props.showOpen ||
            (currentIcon.value && isDisabled.value && props.userObj?.level <= 10)
        ));

        const showConfirm = ref(false);
        const pendingEvent = ref(null);

        const handleIconClick = (event) => {
            const userProp = props.userObj;
            if (props.disabled && userProp?.level > 10) return;
            pendingEvent.value = event;
            if (currentIcon.value == props.currentIcon[0]) {
                showConfirm.value = true;
            } else {
                confirmToggle();
            }
        };

        const confirmToggle = () => {
            if (Array.isArray(props.currentIcon) && props.currentIcon.length === 2) {
                currentIcon.value =
                    currentIcon.value === props.currentIcon[0]
                        ? props.currentIcon[1]
                        : props.currentIcon[0];
            }
            showConfirm.value = false;
            if (pendingEvent.value) {
                emit('right-icon-click', pendingEvent.value);
                pendingEvent.value = null;
            }
        };

        const cancelToggle = () => {
            showConfirm.value = false;
            pendingEvent.value = null;
        };

        const copyToClipboard = async () => {
            try {
                await navigator.clipboard.writeText(modelValue.value || '');
                emit('copy-click', modelValue.value);
            } catch (err) {
                console.error('Error al copiar:', err);
            }
        };
        const openLink = () => {
             if (!modelValue.value) {
                emit('open-click', null);
                return;
            }
            let url = modelValue.value;
            if (!/^https?:\/\//i.test(url)) {
                url = `https://${url}`;
            }
            window.open(url, '_blank', 'noopener,noreferrer');
             emit('open-click', url);
        };


        return {
            modelValue,
            updateValue,
            handleIconClick,
            currentIcon,
            isDisabled,
            hasActions,
            showConfirm,
            confirmToggle,
            cancelToggle,
            copyToClipboard,
            openLink
        };
    },
});
</script>

<style>
.custom-spinner {
    animation: spinning 2.4s cubic-bezier(0.41, 0.26, 0.2, 0.62) infinite;
}
@keyframes spinning {
    0% { transform: rotate(0); }
    100% { transform: rotate(2turn); }
}
</style>
