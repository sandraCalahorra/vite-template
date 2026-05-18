<template>
    <div class="relative w-full">
        <el-select
            v-model="internalValue"
            :placeholder="placeholder"
            :multiple="multiple"
            :collapse-tags="collapseTags"
            :collapse-tags-tooltip="collapseTagsTooltip"
            :max-collapse-tags="maxCollapseTags"
            :size="size"
            :disabled="disabled"
            :clearable="clearable"
            :filterable="filterable"
            :loading="loading"
            :remote="enableRemote"
            :remote-method="enableRemote ? handleRemoteSearch : null"
            :class="[
                { 'border-red-500': hasError },
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
            <el-option
                v-for="(option, index) in options"
                :key="index"
                :label="option.name"
                :value="option.id"
                :disabled="option.$isDisabled"
            >
                <slot name="option" :option="option"></slot>
            </el-option>

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
import { ElSelect, ElOption, ElInput, ElButton, ElCheckbox } from 'element-plus';

export default defineComponent({
    name: 'NewMultiselect',
    components: { ElSelect, ElOption, ElInput, ElButton, ElCheckbox },

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
        }
    },

    data() {
        return {
            internalValue: this.modelValue,
            isAdding: false,
            optionName: '',
            checkAll: false,
            indeterminate: false
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
</style>
