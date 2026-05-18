<template>
    <div
        class="inline-flex flex-col items-center transition-[padding] duration-200"
        :class="showProfilePictureMenu ? 'pb-36 sm:pb-0' : ''"
    >
        <div ref="profilePictureMenuWrapper" class="relative flex justify-center">
            <button
                type="button"
                class="group relative h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-slate-50 shadow-lg ring-1 ring-slate-200/70 transition duration-200 hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-primary/15"
                :class="[showProfilePictureMenu ? 'ring-4 ring-primary/15' : '', !editable && 'cursor-default hover:translate-y-0 hover:shadow-lg']"
                @click="toggleProfilePictureMenu"
            >
                <template v-if="profilePictureUrl">
                    <img :src="profilePictureUrl" alt="Foto de perfil" class="h-full w-full object-cover" />
                    <span v-if="editable" class="absolute inset-0 flex items-center justify-center bg-slate-950/45 text-[10px] font-black uppercase text-white opacity-0 transition duration-200 group-hover:opacity-100">
                        Cambiar
                    </span>
                </template>
                <template v-else>
                    <span class="flex h-full w-full flex-col items-center justify-center gap-1 bg-slate-50 text-slate-400 transition group-hover:bg-primary/5 group-hover:text-primary">
                        <IconPhotoSolid class="size-7" />
                    </span>
                </template>
            </button>


            <Transition name="profile-menu">
                <div
                    v-if="editable && showProfilePictureMenu"
                    class="absolute left-1/2 top-full z-30 mt-3 w-56 -translate-x-1/2 rounded-lg border border-slate-200 bg-white text-left shadow-xl shadow-slate-900/10 sm:left-full sm:top-1/2 sm:ml-4 sm:mt-0 sm:-translate-x-0 sm:-translate-y-1/2"
                >
                    <button
                        type="button"
                        class="group flex min-h-11 w-full items-center justify-between rounded-t-md px-3 text-left text-xs font-bold text-slate-700 transition hover:bg-primary/10 hover:text-primary"
                        @click="selectProfilePictureUpload"
                    >
                        <span>Subir imagen</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-400 transition group-hover:bg-white group-hover:text-primary">JPG/PNG</span>
                    </button>
                    <button
                        type="button"
                        class="group flex min-h-11 w-full items-center justify-between rounded-b-md px-3 text-left text-xs font-bold text-slate-700 transition hover:bg-primary/10 hover:text-primary"
                        @click="selectProfilePictureCamera"
                    >
                        <span>Hacer foto</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-400 transition group-hover:bg-white group-hover:text-primary">Cámara</span>
                    </button>
                    <div v-if="profilePictureUrl" class="mx-2 my-1 h-px bg-slate-100"></div>
                    <button
                        v-if="profilePictureUrl"
                        type="button"
                        class="flex min-h-11 w-full items-center justify-between rounded-md px-3 text-left text-xs font-bold text-rose-600 transition hover:bg-rose-50"
                        @click="selectProfilePictureDelete"
                    >
                        <span>Eliminar foto</span>
                        <IconDelete class="size-3.5" />
                    </button>
                </div>
            </Transition>
        </div>

        <!-- Uploader oculto -->
        <div class="hidden">
            <FileUploader
                ref="profilePicUploader"
                :multiple="false"
                accept=".jpeg,.jpg,.png,image/jpeg,image/png"
                :modelValue="existingProfilePicture ? [existingProfilePicture] : []"
                @files-changed="handleProfilePictureChanged"
                @remove-existing="deleteProfilePicture"
                hide-button
            />
        </div>

        <TheModal
            :open="isCameraModalOpen"
            title="HACER FOTO"
            :large="true"
            @close="closeCameraModal"
        >
            <template #body>
                <div class="flex flex-col gap-4">
                    <div
                        class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-950"
                        :class="isStartingCamera ? 'min-h-[18rem]' : ''"
                    >
                        <video
                            ref="cameraVideo"
                            autoplay
                            playsinline
                            muted
                            class="min-h-[18rem] w-full bg-slate-950 object-cover"
                        />

                        <div
                            v-if="isStartingCamera"
                            class="absolute inset-0 flex items-center justify-center bg-slate-950/60 text-sm font-semibold text-white"
                        >
                            Iniciando cámara...
                        </div>
                    </div>

                    <p v-if="cameraErrorMessage" class="text-sm font-medium text-rose-600">
                        {{ cameraErrorMessage }}
                    </p>
                </div>
            </template>

            <template #action>
                <div class="flex w-full justify-end gap-3">
                    <ButtonAction
                        label="Hacer foto"
                        color="green"
                        size="sm"
                        :disabled="isStartingCamera || !!cameraErrorMessage"
                        @click="captureProfilePictureFromCamera"
                    />
                </div>
            </template>
        </TheModal>
    </div>
</template>

<script>
import { defineComponent, nextTick } from 'vue';

export default defineComponent({
    name: 'ProfilePictureManager',
    props: {
        userId: {
            type: [String, Number],
            required: true
        },
        modelValue: {
            type: Object,
            default: null
        },
        initialBase64: {
            type: String,
            default: null
        },
        editable: {
            type: Boolean,
            default: true
        }
    },
    emits: ['update:modelValue', 'picture-changed', 'refresh-session'],
    data() {
        return {
            existingProfilePicture: null,
            selectedProfilePicture: [],
            profilePictureUrl: null,
            showProfilePictureMenu: false,
            deletingProfilePicture: false,
            isCameraModalOpen: false,
            isStartingCamera: false,
            cameraErrorMessage: '',
            cameraStream: null,
            profilePictureAllowedExtensions: ['jpg', 'jpeg', 'png'],
            profilePictureAllowedMimeTypes: ['image/jpeg', 'image/png'],
            profilePictureAllowedTypesLabel: 'JPG, JPEG o PNG'
        };
    },
    computed: {
        isIOSDevice() {
            if (typeof navigator === 'undefined') return false;

            const userAgent = navigator.userAgent || '';
            const platform = navigator.platform || '';
            const maxTouchPoints = navigator.maxTouchPoints || 0;

            return /iPad|iPhone|iPod/.test(userAgent)
                || (platform === 'MacIntel' && maxTouchPoints > 1);
        },
    },
    watch: {
        userId: {
            immediate: true,
            handler() {
                this.hydrateState();
            }
        },
        modelValue: {
            immediate: true,
            handler(val) {
                this.existingProfilePicture = val;
            }
        },
        initialBase64: {
            immediate: true,
            handler(val) {
                this.profilePictureUrl = val;
            }
        }
    },
    mounted() {
        document.addEventListener('click', this.closeProfilePictureMenuOnOutsideClick);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.closeProfilePictureMenuOnOutsideClick);
        this.stopCameraStream();

        if (this.profilePictureUrl && this.profilePictureUrl.startsWith('blob:')) {
            URL.revokeObjectURL(this.profilePictureUrl);
        }
    },
    methods: {
        hydrateState() {
            if (this.profilePictureUrl && this.profilePictureUrl.startsWith('blob:')) {
                URL.revokeObjectURL(this.profilePictureUrl);
            }
            this.existingProfilePicture = this.modelValue;
            this.profilePictureUrl = this.initialBase64;
        },

        toggleProfilePictureMenu() {
            if (!this.editable) return;

            if (this.isIOSDevice) {
                this.showProfilePictureMenu = false;
                this.selectProfilePictureUpload();
                return;
            }

            this.showProfilePictureMenu = !this.showProfilePictureMenu;
        },

        closeProfilePictureMenuOnOutsideClick(event) {
            if (!this.showProfilePictureMenu) return;

            const wrapper = this.$refs.profilePictureMenuWrapper;
            if (wrapper && !wrapper.contains(event.target)) {
                this.showProfilePictureMenu = false;
            }
        },

        selectProfilePictureUpload() {
            this.showProfilePictureMenu = false;
            this.$refs.profilePicUploader.triggerUpload();
        },

        selectProfilePictureCamera() {
            this.showProfilePictureMenu = false;
            this.openCameraModal();
        },

        async requestCameraStream() {
            const constraintsList = [
                {
                    video: {
                        facingMode: { ideal: 'user' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                },
                { video: true, audio: false },
            ];

            let lastError = null;
            for (const constraints of constraintsList) {
                try {
                    return await navigator.mediaDevices.getUserMedia(constraints);
                } catch (error) {
                    lastError = error;
                }
            }
            throw lastError;
        },

        getCameraErrorMessage(error) {
            const errorName = error?.name || '';
            if (errorName === 'NotAllowedError' || errorName === 'SecurityError') {
                return 'El navegador ha bloqueado el acceso a la cámara. Revisa los permisos de este sitio.';
            }
            if (errorName === 'NotFoundError' || errorName === 'DevicesNotFoundError') {
                return 'No se ha encontrado ninguna cámara disponible en este dispositivo.';
            }
            if (errorName === 'NotReadableError' || errorName === 'TrackStartError') {
                return 'La cámara está ocupada o no se puede leer.';
            }
            return 'No se ha podido acceder a la cámara.';
        },

        async openCameraModal() {
            if (!navigator?.mediaDevices?.getUserMedia) {
                this.$axios.defaults.globalAlert('error', 'La cámara no está disponible.');
                return;
            }

            this.cameraErrorMessage = '';
            this.isStartingCamera = true;
            this.isCameraModalOpen = true;

            try {
                const stream = await this.requestCameraStream();
                this.cameraStream = stream;
                await nextTick();

                if (this.$refs.cameraVideo) {
                    this.$refs.cameraVideo.srcObject = stream;
                    await this.$refs.cameraVideo.play();
                }
            } catch (error) {
                this.cameraErrorMessage = this.getCameraErrorMessage(error);
            } finally {
                this.isStartingCamera = false;
            }
        },

        stopCameraStream() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach((track) => track.stop());
                this.cameraStream = null;
            }
            if (this.$refs.cameraVideo) {
                this.$refs.cameraVideo.srcObject = null;
            }
        },

        closeCameraModal() {
            this.isCameraModalOpen = false;
            this.isStartingCamera = false;
            this.cameraErrorMessage = '';
            this.stopCameraStream();
        },

        captureProfilePictureFromCamera() {
            if (this.isStartingCamera || this.cameraErrorMessage) return;

            const video = this.$refs.cameraVideo;
            if (!video || !video.videoWidth || !video.videoHeight) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                if (!blob) return;
                const capturedFile = new File([blob], `profile-picture-${Date.now()}.jpg`, { type: 'image/jpeg' });
                this.closeCameraModal();
                this.handleProfilePictureChanged([capturedFile]);
            }, 'image/jpeg', 0.92);
        },

        selectProfilePictureDelete() {
            this.showProfilePictureMenu = false;
            this.deleteProfilePicture();
        },

        handleProfilePictureChanged(files) {
            this.selectedProfilePicture = files;
            if (files && files.length > 0) {
                if (this.profilePictureUrl && this.profilePictureUrl.startsWith('blob:')) {
                    URL.revokeObjectURL(this.profilePictureUrl);
                }
                this.profilePictureUrl = URL.createObjectURL(files[0]);
                this.uploadProfilePicture();
            }
        },

        async uploadProfilePicture() {
            if (!this.selectedProfilePicture.length || !this.userId) return;

            try {
                const formData = new FormData();
                formData.append('file', this.selectedProfilePicture[0]);

                const response = await this.$axios.post(`/api/files/users/${this.userId}/profile-picture`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                this.$emit('update:modelValue', response.data.file);
                this.$emit('picture-changed', {
                    file: response.data.file,
                    base64: response.data.base64
                });

                this.profilePictureUrl = response.data.base64 || this.profilePictureUrl;
                this.$emit('refresh-session');

                this.$axios.defaults.globalAlert('success', 'Foto de perfil actualizada correctamente.');
            } catch (error) {
                this.profilePictureUrl = this.initialBase64;
                this.$axios.defaults.globalAlert('error', 'No se pudo subir la foto.');
            } finally {
                this.selectedProfilePicture = [];
            }
        },

        async deleteProfilePicture() {
            if (this.deletingProfilePicture || !this.userId) return;

            try {
                this.deletingProfilePicture = true;
                await this.$axios.delete(`/api/files/users/${this.userId}/profile-picture`);

                if (this.profilePictureUrl && this.profilePictureUrl.startsWith('blob:')) {
                    URL.revokeObjectURL(this.profilePictureUrl);
                }

                this.profilePictureUrl = null;
                this.$emit('update:modelValue', null);
                this.$emit('picture-changed', {
                    file: null,
                    base64: null
                });
                this.$emit('refresh-session');

                this.$axios.defaults.globalAlert('success', 'Foto de perfil eliminada.');
            } catch (error) {
                this.$axios.defaults.globalAlert('error', 'Error al eliminar la foto.');
            } finally {
                this.deletingProfilePicture = false;
            }
        }
    }
});
</script>

<style scoped>
.profile-menu-enter-active,
.profile-menu-leave-active {
  transition: opacity 0.16s ease, transform 0.16s ease;
}
.profile-menu-enter-from,
.profile-menu-leave-to {
  opacity: 0;
  transform: translate(-50%, -0.35rem) scale(0.97);
}
</style>
