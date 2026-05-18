<template>
    <div>
        <div v-if="type !== 'textarea'">
            <div
                class="flex items-stretch"
                :class="hasActions && 'border border-gray-300 rounded-[0.25rem] overflow-hidden'"
            >
                <div class="relative flex-1 min-w-0">
                    <Field
                        :type="type"
                        :name="name"
                        :id="name"
                        :rules="effectiveRules"
                        :validate-on-input="shouldValidateOnInput"
                        :validate-on-model-update="shouldValidateOnInput"
                        v-model="modelValue"
                        class="w-full px-2 py-1 text-sm focus:outline-none"
                        :class="[
                            hasActions
                                ? 'border-0 rounded-none'
                                : 'border border-gray-300 rounded-[0.25rem] focus:border-primary',
                            inputStateClass,
                            showStatusIcon && 'pr-8'
                        ]"
                        :placeholder="placeholder"
                        :maxlength="maxlength"
                        :step="step"
                        :min="min"
                        :max="max"
                        :tabindex="tabindex"
                        :disabled="isDisabled"
                        :readonly="readOnly"
                        @input="updateValue($event.target.value)"
                        @blur="handleBlur"
                    />

                    <span
                        v-if="showStatusIcon"
                        class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-sm font-bold"
                        :class="statusIconClass"
                    >
                        {{ statusIcon }}
                    </span>
                </div>

                <!-- Spinner -->
                <div v-if="loading" class="flex items-center justify-center">
                    <div class="border-2 border-transparent rounded-full size-4 border-t-green-500 custom-spinner"></div>
                </div>

                <!-- Lock Icon -->
                <span
                    v-if="(currentIcon && userObj?.level < 30 && isDisabled)"
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
                :rules="effectiveRules"
                v-model="modelValue"
            >
                <div class="relative">
                    <textarea
                        v-model="modelValue"
                        class="block w-full px-1 py-1 tracking-wide border border-gray-300 rounded-[0.25rem] form-input"
                        :class="[inputStateClass, showStatusIcon && 'pr-8']"
                        cols="30"
                        :placeholder="placeholder"
                        :rows="rows"
                        :disabled="disabled"
                        @input="updateValue($event.target.value)"
                        @blur="handleBlur"
                    ></textarea>

                    <span
                        v-if="showStatusIcon"
                        class="pointer-events-none absolute right-2 top-2 flex items-center text-sm font-bold"
                        :class="statusIconClass"
                    >
                        {{ statusIcon }}
                    </span>
                </div>
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
import { Field, Form, ErrorMessage, useFieldError } from 'vee-validate';

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
        step: {
            type: [String, Number],
            default: null,
        },
        min: {
            type: [String, Number],
            default: null,
        },
        max: {
            type: [String, Number],
            default: null,
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
        },
        dni: {
            type: Boolean,
            default: false
        },
        validateOnInput: {
            type: Boolean,
            default: false
        },
        invalid: {
            type: Boolean,
            default: false
        }

    },

    setup(props, { emit }) {
        const modelValue = computed({
            get: () => props.modelValue,
            set: (value) => emit('update:modelValue', value),
        });
        const usesValidation = computed(() => Boolean(props.name && (props.rules || props.dni)));
        const fieldError = usesValidation.value ? useFieldError(props.name) : computed(() => '');

        const normalizeDniNie = (value) => String(value || '').replace(/[\s-]/g, '').toUpperCase();

        const getDniNieValidation = (value) => {
            const normalized = normalizeDniNie(value);
            if (!normalized) {
                return { status: 'empty', message: '' };
            }

            if (normalized.length < 9) {
                return { status: 'pending', message: 'DNI/NIE incompleto.' };
            }

            if (!/^(?:\d{8}[A-Z]|[XYZ]\d{7}[A-Z])$/.test(normalized)) {
                return { status: 'invalid', message: 'Formato de DNI/NIE inválido.' };
            }

            const letterTable = 'TRWAGMYFPDXBNJZSQVHLCKE';
            const numberPart = /^[XYZ]/.test(normalized)
                ? normalized.replace(/^X/, '0').replace(/^Y/, '1').replace(/^Z/, '2').slice(0, 8)
                : normalized.slice(0, 8);
            const expectedLetter = letterTable[Number(numberPart) % 23];
            const actualLetter = normalized.slice(-1);

            if (expectedLetter !== actualLetter) {
                return {
                    status: 'invalid',
                    message: `La letra del DNI/NIE no es correcta. La correcta sería ${expectedLetter}.`,
                };
            }

            return { status: 'valid', message: 'DNI/NIE válido.' };
        };

        const validateDniNie = (value) => {
            const validation = getDniNieValidation(value);
            if (validation.status === 'empty') return true;
            if (validation.status === 'valid') return true;

            return validation.message || 'DNI/NIE no válido.';
        };

        const validateExternalRules = async (value) => {
            if (!props.rules) return true;

            if (typeof props.rules === 'function') {
                return await props.rules(value);
            }

            if (typeof props.rules?.validate === 'function') {
                try {
                    await props.rules.validate(value);
                    return true;
                } catch (error) {
                    return error?.message || false;
                }
            }

            return true;
        };

        const dniRules = async (value) => {
            const externalResult = await validateExternalRules(value);
            if (externalResult !== true) return externalResult;

            return props.dni ? validateDniNie(value) : true;
        };

        const effectiveRules = computed(() => props.dni ? dniRules : props.rules);
        const shouldValidateOnInput = computed(() => props.dni || props.validateOnInput);

        const dniWasEdited = ref(false);
        const dniWasInteracted = ref(false);
        const updateValue = (value) => {
            if (props.dni) {
                dniWasEdited.value = true;
            }

            emit('update:modelValue', props.dni ? normalizeDniNie(value) : value);
        };
        const dniValidation = computed(() => getDniNieValidation(modelValue.value));
        const hasFieldError = computed(() => Boolean(fieldError.value));
        const showDniSuccessIcon = computed(() => (
            props.dni &&
            dniWasEdited.value &&
            dniValidation.value.status === 'valid' &&
            Boolean(normalizeDniNie(modelValue.value)) &&
            (dniWasInteracted.value || normalizeDniNie(modelValue.value).length >= 9)
        ));
        const showStatusIcon = computed(() => props.invalid || hasFieldError.value || showDniSuccessIcon.value);
        const statusIconClass = computed(() => (
            props.invalid || hasFieldError.value ? 'text-red-600' : 'text-emerald-600'
        ));
        const statusIcon = computed(() => (
            props.invalid || hasFieldError.value ? '!' : '✓'
        ));
        const inputStateClass = computed(() => {
            if (props.invalid) return '!border-red-500 focus:!border-red-500';
            if (hasFieldError.value) return '!border-red-500 focus:!border-red-500';

            if (showDniSuccessIcon.value) return '!border-emerald-500 focus:!border-emerald-500';

            return '';
        });
        const handleBlur = () => {
            if (props.dni) {
                dniWasInteracted.value = true;
            }
        };

        const currentIcon = ref(
            Array.isArray(props.currentIcon) ? props.currentIcon[0] : props.currentIcon
        );

        const isDisabled = computed(() => {
            if (!props.currentIcon) return props.disabled;
            const isCurrentMatch =
                Array.isArray(props.currentIcon) &&
                props.currentIcon[0] === currentIcon.value;
            return props.disabled && isCurrentMatch;
        });

        const hasActions = computed(() => (
            props.showCopy ||
            props.showOpen ||
            (currentIcon.value && isDisabled.value && props.userObj?.level < 30)
        ));

        const showConfirm = ref(false);
        const pendingEvent = ref(null);

        const handleIconClick = (event) => {
            const userProp = props.userObj;
            if (props.disabled && userProp?.level >= 30) return;
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
            effectiveRules,
            shouldValidateOnInput,
            inputStateClass,
            showStatusIcon,
            statusIconClass,
            statusIcon,
            handleBlur,
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
