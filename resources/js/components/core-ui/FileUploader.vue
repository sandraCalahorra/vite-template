<template>
    <div class="w-full max-w-xl p-3 bg-white border border-dashed rounded-md sm:p-4">
        <ButtonAction
            v-if="!hideButton"
            :label="labelOverride || 'Subir archivo...'"
            color="primary"
            :size="sizeOverride || 'sm'"
            @click="triggerUpload"
        />
        <!-- File list -->
        <div v-if="!hideButton && (files.length > 0 || modelValue.length > 0)" class="py-2">
            <div
                v-for="(file, index) in (files.length > 0 ? files : modelValue)"
                :key="index"
                class="flex min-w-0 items-center justify-between gap-2 p-1 rounded-sm cursor-pointer group hover:bg-gray-100"
                @mouseenter="hovered = index"
                @mouseleave="hovered = null"
            >
                <div
                    class="flex min-w-0 flex-1 flex-row items-center gap-1 text-sm text-gray-500 group-hover:text-primary"
                    @click="openFile(file)"
                >
                    <template v-if="previewImage && previewImage.is_image">
                        <div class="w-14 h-14 flex-shrink-0 rounded-lg bg-slate-100 overflow-hidden flex items-center justify-center">
                            <img
                                :src="previewImage.value"
                                :alt="file.name || file.original_name || file.filename"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </template>

                    <template v-else>
                        <IconDocumentText class="w-4 h-4 min-w-4 text-gray-400" />
                    </template>

                    <span class="min-w-0 truncate font-semibold">
                        {{ file.name || file.original_name || file.filename || modelValue[0]?.split('/').pop() }}
                    </span>
                    <span class="shrink-0 text-xs italic text-gray-400">
                        {{ file.size ? `(${formatBytes(file.size)})` : '' }}
                    </span>
                </div>

                <div class="flex h-5 w-5 shrink-0 items-center justify-center transition-colors">
                    <IconLoading
                        v-if="file.uploading"
                        class="w-4 h-4 text-primary animate-spin"
                    />
                    <IconCheckmarkCircle
                        v-else-if="file.isNew"
                        class="w-4 h-4 text-green-500 min-w-4 animate-bounce"
                    />
                    <div
                        v-else
                        class="flex items-center justify-center"
                        @click.stop="removeFile(index)"
                    >
                        <HeroiconXMark
                            class="w-4 h-4 text-gray-400 cursor-pointer min-w-4 hover:text-red-500 transition-colors"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress bar -->
        <transition name="fade">
            <div class="flex flex-row items-center justify-center gap-2">
                <div v-if="isUploading" class="relative w-full h-1 mb-1 overflow-hidden bg-gray-200 rounded">
                    <div
                        class="absolute top-0 left-0 h-full transition-all duration-300 bg-green-600"
                        :style="{ width: uploadProgress + '%' }"
                    ></div>
                </div>
                <div v-if="isUploading" class="mb-4 text-sm font-medium text-gray-600">
                    {{ uploadProgress }}%
                </div>
            </div>
        </transition>
        <p v-if="hasError" class="text-xs italic text-red-500 underline"> {{ errorMessage }}</p>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default defineComponent({
    name: 'FileUploader',

    mixins: [AdminMixin],

    emits: ['files-changed', 'remove-existing', 'open-existing'],

    props: {
        accept: {
            type: String,
            default: '.pdf, .doc, .docx, .odt, .xls, .xlsx, .ppt, .pptx, .zip, .jpg, .png, .jpeg',
        },
        multiple: {
            type: Boolean,
            default: false,
        },
        required: {
            type: Boolean,
            default: false
        },
        modelValue: {
            type: Array,
            default: [],
        },
        errorMessage: {
            type: String,
            default: 'Este campo es obligatorio.'
        },
        labelOverride: {
            type: String,
            default: null
        },
        sizeOverride: {
            type: String,
            default: null
        },
        hideButton: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            files: [],
            hovered: null,
            isUploading: false,
            uploadProgress: 0,
            internalValue: false,
            previewImage: {},
        };
    },

    computed: {
        hasError() {
            return this.required && !this.internalValue && !this.modelValue.length;
        }
    },

    methods: {
        triggerUpload() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = this.accept;
            input.multiple = this.multiple;

            input.onchange = (event) => {
                const allSelectedFiles = Array.from(event.target.files);
                if (!allSelectedFiles.length) return;

                const allowedTypes = this.accept.split(',').map(t => t.trim().toLowerCase());
                const validFiles = [];
                const invalidFileNames = [];

                allSelectedFiles.forEach(file => {
                    const fileExtension = `.${file.name.split('.').pop().toLowerCase()}`;
                    const fileMimeType = file.type.toLowerCase();

                    const isAllowed = allowedTypes.some(allowedType => {
                        if (allowedType.startsWith('.')) { // It's an extension like .pdf
                            return fileExtension === allowedType;
                        }
                        if (allowedType.includes('/')) { // It's a MIME type like application/pdf
                            if (allowedType.endsWith('/*')) { // Wildcard MIME type like image/*
                                return fileMimeType.startsWith(allowedType.slice(0, -1));
                            }
                            return fileMimeType === allowedType;
                        }
                        return false;
                    });

                    if (isAllowed) {
                        validFiles.push(file);
                    } else {
                        invalidFileNames.push(file.name);
                    }
                });

                if (invalidFileNames.length > 0) {
                    const types = this.accept.split(',').map(t => t.trim());
                    const last = types.pop();
                    const formattedTypes = types.length ? types.join(', ') + ' y ' + last : last;
                    this.openAlert('error', `No está permitido este tipo de ficheros`, 'Sólo se permiten los siguientes tipos: ' + formattedTypes);
                }

                if (!validFiles.length) return;

                const processedFiles = validFiles.map(file => {
                    file.uploading = true;
                    file.isNew = false;
                    file.uid = Math.random().toString(36).substr(2, 9);
                    return file;
                });

                if (this.multiple) {
                    const newFiles = processedFiles.filter(f =>
                        !this.files.some(existing => existing.name === f.name && existing.size === f.size)
                    );
                    this.files = [...this.files, ...newFiles];
                } else {
                    this.files = [processedFiles[0]];
                }

                this.internalValue = this.files.length > 0;
                this.startProgress(processedFiles);

                if (this.files.length > 0 && this.files[0].type?.split('/').shift() === 'image') {
                    this.previewImage.is_image = true;
                    this.previewImage.value = URL.createObjectURL(this.files[0]);
                }

                this.$emit('files-changed', this.files);
            };

            input.click();
        },

        removeFile(index) {
            if (this.files.length === 0 && this.modelValue && this.modelValue.length > 0) {
                this.$emit('remove-existing', this.modelValue[index], index);
                return;
            }
            this.files.splice(index, 1);
            this.internalValue = this.files.length > 0;
            this.previewImage = {};
            this.$emit('files-changed', this.files);
        },

        startProgress(selectedFiles) {
            this.isUploading = true;
            this.uploadProgress = 0;

            const totalSize = selectedFiles.reduce((acc, f) => acc + f.size, 0);
            const uploadTime = Math.min(1500, Math.max(500, totalSize / 10000));

            const startTime = Date.now();
            const interval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(100, Math.round((elapsed / uploadTime) * 100));

                this.uploadProgress = progress;

                if (progress >= 100) {
                    clearInterval(interval);
                    this.isUploading = false;

                    selectedFiles.forEach(fileObj => {
                        const targetFile = this.files.find(f => f.uid === fileObj.uid);
                        if (targetFile) {
                            targetFile.uploading = false;
                            targetFile.isNew = true;
                        }
                    });

                    this.$forceUpdate();

                    setTimeout(() => {
                        selectedFiles.forEach(fileObj => {
                            const targetFile = this.files.find(f => f.uid === fileObj.uid);
                            if (targetFile) {
                                targetFile.isNew = false;
                            }
                        });
                        this.$forceUpdate();
                    }, 2500);
                }
            }, 50);
        },

        openFile(file) {
            if (file.url) {
                window.open(file.url, '_blank');
            } else if (file instanceof File || file instanceof Blob) {
                const blobUrl = URL.createObjectURL(file);
                window.open(blobUrl, '_blank');
            } else if (typeof file === 'string') {
                window.open(file, '_blank');
            } else {
                this.$emit('open-existing', file);
            }
        },

        formatBytes(bytes) {
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0) return '0 Bytes';
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
        },

        isValid() {
            return !this.required || !!this.internalValue || this.files.length > 0 || this.modelValue.length > 0;
        }
    },
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
