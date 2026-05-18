<template>
        <!-- toolbar="essential"        -->
    <div :class="[customClass, invalid && 'text-editor-invalid']">
        <div class="relative">

            <QuillEditor
                theme="snow"
                :toolbar="resolvedToolbar"
                :contentType="contentType"
                v-model:content="editorContent"
                @update:content="updateContent"
                :style="customQuillStyle"
                :disabled="disabled"
                ref="quillEditor"
                :class="customClass"
                :placeholder="placeholder"
            />
            <span
                v-if="invalid"
                class="pointer-events-none absolute right-2 top-2 z-10 text-sm font-bold text-red-600"
            >
                !
            </span>
        </div>
        <p v-if="invalid && errorMessage" class="mt-1 text-xs italic text-red-600 underline">
            {{ errorMessage }}
        </p>
        <small
            v-if="charCount"
            class="block text-right mt-1"
            :class="{
                'text-red-500': charCount > maxLength,
                'text-gray-500': charCount <= maxLength
            }"
        >
                {{ charCount }}/{{ maxLength }} caracteres
        </small>
    </div>
</template>

<script>
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default {
    name: 'TextEditor',
    components: {
        QuillEditor
    },
    props: {
        // modelValue: String,
        content: String,
        contentType: {
            type: String,
            default: 'text', // Valor por defecto
            validator: (value) => {
                return ['text', 'html'].includes(value); // Asegúrate de que sea 'text' o 'html'
            }
        },
        height: {
            type: String,
            default: '150px', // Valor por defecto
        },
        maxHeight: {
            type: String,
        },
        maxLength: {
            type: Number,
            default: 20000
        },
        customClass: {
            type: String
        },
        disabled: {
            type: Boolean
        },
        required: {
            type: Boolean,
            default: false
        },
        invalid: {
            type: Boolean,
            default: false
        },
        errorMessage: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: ''
        },
        customToolbar: {
            type: Array,
            default: null
        }
    },
    data() {
        return {
            editorContent: this.content,
            customQuillStyle: {
                height: this.height,
                maxHeight: this.maxHeight,
                minHeight: '150px',
                fontSize: '16px',
                width: '100%',
                fontFamily: 'ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
                whiteSpace: 'pre-wrap',
                wordBreak: 'break-word',
                overflowWrap: 'break-word',
            },
            defaultToolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                [{ 'list': 'ordered' }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    },
    computed: {
        resolvedToolbar() {
            return this.customToolbar ?? this.defaultToolbar;
        },

        charCount() {
            if (!this.editorContent) return 0;

            if (this.contentType === 'html') {
                const stripped = this.editorContent.replace(/<[^>]*>/g, '');
                return stripped.length;
            }
            return this.editorContent.length;
        }
    },
    watch: {
        content(newVal) {
            if (newVal !== this.editorContent) {
                this.editorContent = newVal;

                // Clear the QuillEditor content explicitly if empty
                if (!newVal && this.$refs.quillEditor) {
                    this.$refs.quillEditor.setText(null);
                }
            }
        },
    },
    methods: {
        updateContent(newContent) {
            this.$emit('update:content', newContent == '<p><br></p>' ? null : newContent);
        }
    }
};
</script>

<style>
.text-editor-invalid .ql-toolbar,
.text-editor-invalid .ql-container {
    border-color: #ef4444 !important;
}

.text-editor-invalid .ql-container {
    box-shadow: 0 0 0 1px #ef4444;
}
</style>
