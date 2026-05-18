<template>
    <div class="relative w-full">
        <el-select
            v-model="internalValue"
            :placeholder="placeholder"
            :multiple="multiple"
            :showCheckAll="showCheckAll"
            :collapse-tags="collapseTags"
            :collapse-tags-tooltip="collapseTagsTooltip"
            :max-collapse-tags="maxCollapseTags"
            :size="size"
            :disabled="disabled"
            :clearable="clearable"
            :filterable="filterable"
            :multiple-limit="multipleLimit"
            :loading="loading"
            :remote="enableRemote"
            :remote-method="enableRemote ? handleRemoteSearch : null"
            popper-class="new-multiselect-popper"
            placement="bottom"
            :popper-options="popperOptions"
            :class="[
                { 'multiselect-invalid': hasError },
                'w-full rounded-md cursor-pointer'
            ]"
            @change="handleChange"
            @input="handleInput"
            @visible-change="$emit('open', $event)"
        >
            <!-- Custom loading spinner -->
            <template #loading>
                <svg class="circular" viewBox="0 0 50 50">
                    <circle class="path" cx="25" cy="25" r="20" fill="none" />
                </svg>
            </template>

            <!-- HEADER: "Select All" checkbox -->
            <template v-if="showCheckAll && multiple && options.length" #header>
                <el-checkbox
                    v-model="checkAll"
                    :indeterminate="indeterminate"
                    @change="handleCheckAll"
                >
                 Todos
                </el-checkbox>
            </template>

            <!-- OPTIONS -->
            <template v-if="isGrouped">
                <el-option-group
                    v-for="group in options"
                    :key="group.label"
                    :label="group.label"
                >
                    <el-option
                        v-for="item in group.options"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"
                    :disabled="item.$isDisabled"
                >
                        <div class="flex min-w-0 w-full items-start justify-between gap-2">
                            <span class="min-w-0 flex-1 whitespace-normal break-words leading-5">{{ item.name }}</span>
                            <span v-if="item[extraLabelKey]" class="shrink-0 text-xs text-gray-400 font-normal leading-5">
                                {{ formatExtraLabel(item[extraLabelKey]) }}{{ extraLabelSuffix }}
                            </span>
                        </div>
                    </el-option>
                </el-option-group>
            </template>
            <template v-else>
                <el-option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.name"
                    :value="option.id"
                    :disabled="option.$isDisabled"
                >
                    <div class="flex min-w-0 w-full items-start justify-between gap-2">
                        <span class="min-w-0 flex-1 whitespace-normal break-words leading-5">{{ option.name }}</span>
                        <span v-if="option[extraLabelKey]" class="shrink-0 text-xs text-gray-400 font-normal leading-5">
                             {{ formatExtraLabel(option[extraLabelKey]) }}{{ extraLabelSuffix }}
                        </span>
                    </div>
                </el-option>
            </template>

            <!-- Single label -->
            <template #prefix>
                <slot name="singleLabel" :option="options.find(o => o.id === internalValue)"></slot>
            </template>

            <!-- FOOTER: Add new custom option -->
            <template v-if="taggable" #footer>
                <el-button v-if="!isAdding" text bg size="small" @click="isAdding = true">
                    Agrega una opción
                </el-button>
                <template v-else>
                    <el-input
                        v-model="optionName"
                        class="option-input"
                        placeholder="input option name"
                        size="small"
                    />
                    <el-button type="primary" size="small" @click="confirmCreate">
                        Confirmar
                    </el-button>
                    <el-button size="small" @click="clear">
                        Cancelar
                    </el-button>
                </template>
            </template>

            <template #tag="slotProps">
                <slot name="tag" v-bind="slotProps"></slot>
            </template>
        </el-select>
        <p v-if="hasError" class="text-xs italic text-red-500 underline"> {{ errorMessage }} </p>
    </div>
</template>

<script>
import { defineComponent, watch } from 'vue';
import { ElSelect, ElOption, ElInput, ElButton, ElCheckbox, ElOptionGroup } from 'element-plus';

export default defineComponent({
    name: 'NewMultiselect',
    components: { ElSelect, ElOption, ElInput, ElButton, ElCheckbox, ElOptionGroup },

    props: {
        modelValue: [Array, String, Number, Object],
        options: {
            type: Array,
            required: true
        },
        placeholder: {
            type: String,
            default: 'Selecciona una opción'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        showCheckAll: {
            type: Boolean,
            default: true
        },
        collapseTags: {
            type: Boolean,
            default: false
        },
        collapseTagsTooltip: {
            type: Boolean,
            default: false
        },
        maxCollapseTags: {
            type: Number,
            default: 2
        },
        size: {
            type: String,
            default: 'default'
        },
        disabled: {
            type: Boolean,
            default: false
        },
        taggable: {
            type: [Boolean, String],
            default: false
        },
        filterable: {
            type: Boolean,
            default: true
        },
        showCheckAll: {
            type: Boolean,
            default: true
        },
        loading: {
            type: [Boolean, String],
            default: false
        },
        required: {
            type: Boolean,
            default: false
        },
        errorMessage: {
            type: String,
            default: 'Este campo es obligatorio.'
        },
        enableRemote: {
            type: Boolean,
            default: false
        },
        clearable: {
            type: Boolean,
            default: true
        },
        isGrouped: {
            type: Boolean,
            default: false
        },
        extraLabelKey: {
            type: String,
            default: 'quarterly_price'
        },
        extraLabelSuffix: {
            type: String,
            default: ' €'
        },
        multipleLimit: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            internalValue: this.modelValue,
            isAdding: false,
            optionName: '',
            checkAll: false,
            indeterminate: false,
            popperOptions: {
                modifiers: [
                    {
                        name: 'preventOverflow',
                        options: {
                            boundary: 'viewport',
                            rootBoundary: 'viewport',
                            padding: 8
                        }
                    },
                    {
                        name: 'flip',
                        options: {
                            boundary: 'viewport',
                            rootBoundary: 'viewport',
                            padding: 8
                        }
                    }
                ]
            }
        };
    },

    watch: {
        modelValue(val) {
            this.internalValue = val;
        },

        internalValue(val) {
            if (!this.multiple) return;

            const total = this.options.length;
            const selected = val?.length || 0;

            if (selected === 0) {
                this.checkAll = false;
                this.indeterminate = false;
            } else if (selected === total) {
                this.checkAll = true;
                this.indeterminate = false;
            } else {
                this.checkAll = false;
                this.indeterminate = true;
        }
        }
    },

    computed: {
        hasError() {
            if (!this.required) return false;
            if (Array.isArray(this.internalValue)) {
                return this.internalValue.length === 0;
            }
            return !this.internalValue;
        }
    },

    methods: {
        handleChange(val) {
            this.$emit('update:modelValue', val);
            this.$emit('change', val);
        },

        handleInput(val) {
            if (this.filterable) {
                this.$emit('search', val);
            }
        },

        handleRemoteSearch(query) {
            if (!this.enableRemote) return;
            this.$emit('remote-search', query);
        },

        confirmCreate() {
            if (!this.optionName) return;

            const newOption = {
                name: this.optionName,
                id: this.optionName
            };

            this.$emit('tag', newOption);
            this.options.push(newOption);
            this.clear();
        },

        clear() {
            this.optionName = '';
            this.isAdding = false;
        },

        handleCheckAll(val) {
            this.indeterminate = false;
            if (val) {
                this.internalValue = this.options.map(opt => opt.id);
            } else {
                this.internalValue = [];
            }
            this.handleChange(this.internalValue);
        },

        isValid() {
            if (!this.required) return true;
            if (Array.isArray(this.internalValue)) {
                return this.internalValue.length > 0;
            }
            return !!this.internalValue;
        },

        formatMoney(value) {
            const number = Number(value || 0);
            if (Number.isNaN(number)) return '0.00';
            return number.toFixed(2);
        },

        formatExtraLabel(value) {
            if (this.extraLabelKey === 'quarterly_price') {
                return this.formatMoney(value);
            }
            return value;
        }
    }
});
</script>

<style>
.option-input {
    width: 100%;
    margin-bottom: 8px;
}

/* Loading spinner styling */
.el-select-dropdown__loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px;
    font-size: 20px;
}

/* Loading animation 1 */
.circular {
    display: inline;
    height: 20px;
    width: 20px;
    animation: loading-rotate 2s linear infinite;
}

.path {
    animation: loading-dash 1.5s ease-in-out infinite;
    stroke-dasharray: 90, 150;
    stroke-dashoffset: 0;
    stroke-width: 2;
    stroke: var(--primary);
    stroke-linecap: round;
}

@keyframes loading-rotate {
    to {
        transform: rotate(360deg);
    }
}

@keyframes loading-dash {
    0% {
        stroke-dasharray: 1, 200;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -40px;
    }
    100% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -120px;
    }
}

.el-select__tags-text {
    color: #525252;
}

.el-tag .el-tag__close {
    color: #525252;
}

.el-tag .el-tag__close:hover {
    background-color: #525252;
    color: #e5e7eb;
}

.el-select-dropdown__item.is-selected {
    color: var(--primary);
}

.new-multiselect-popper {
    max-width: calc(100vw - 1rem);
    overflow-x: hidden;
}

.new-multiselect-popper .el-select-dropdown {
    max-width: 100%;
}

.new-multiselect-popper .el-select-dropdown__wrap,
.new-multiselect-popper .el-scrollbar__wrap {
    overflow-x: hidden !important;
}

.new-multiselect-popper .el-select-dropdown__list {
    max-width: 100%;
}

.new-multiselect-popper .el-select-dropdown__item {
    box-sizing: border-box;
    height: auto;
    min-height: 34px;
    max-width: 100%;
    padding-top: 7px;
    padding-bottom: 7px;
    white-space: normal;
    line-height: 1.25rem;
}

.new-multiselect-popper .el-select-dropdown__item > span {
    display: block;
    min-width: 0;
    max-width: 100%;
}

.el-select-dropdown.is-multiple .el-select-dropdown__item.is-selected:after {
    background-color: var(--primary);
}

.el-checkbox__input.is-checked+.el-checkbox__label {
    color: var(--primary);
}

.el-checkbox__input.is-checked .el-checkbox__inner {
    background-color: var(--primary);
    border-color: var(--primary);
}

.el-checkbox__input.is-indeterminate .el-checkbox__inner {
    background-color: var(--primary);
    border-color: var(--primary);
}

.el-select__placeholder {
    color: black;
}

.el-select__wrapper {
    border: #d1d5db;
}

.el-select__wrapper.is-focused {
    box-shadow: 0 0 0 1px var(--primary);
}

.multiselect-invalid .el-select__wrapper {
    box-shadow: 0 0 0 1px #ef4444;
}

.multiselect-invalid .el-select__wrapper.is-focused {
    box-shadow: 0 0 0 1px #ef4444;
}
</style>
