<template>
    <div>
        <TheTopbar :title="(id ? 'EDITAR' : 'CREAR') + ' ANUNCIO'" />

        <LoadingSpinner v-show="isLoading" />
        <Section v-show="!isLoading">
            <div class="p-4 bg-white shadow-sm rounded flex flex-col w-full gap-4">
                <div class="flex flex-row w-full col-span-full justify-between">
                    <h2 class="col-span-2 s-500:col-span-1 font-semibold text-primary flex items-center text-lg tracking-wide">
                        DATOS DEL ANUNCIO
                    </h2>
                </div>

                <Form :validation-schema="validationSchema" class="grid grid-cols-2 gap-4" ref="form" @submit="submitAnnouncements">

                    <FormSection label="Información">
                        <InputWrapper label="Título" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="title"
                                type="text"
                                v-model="newItem.title"
                                :rules="validationRules.title"
                            />
                        </InputWrapper>

                        <InputWrapper label="Estado" class="col-span-2 md:col-span-1">
                            <div class="flex items-center gap-2 mt-2">
                                <InputToggleButton
                                    :name="newItem.is_active ? 'Activo' : 'Inactivo'"
                                    :value="newItem.is_active"
                                    @change="newItem.is_active = !newItem.is_active"
                                />
                            </div>
                        </InputWrapper>

                        <InputWrapper label="Contenido" required class="col-span-2">
                            <TextEditor
                                v-model="newItem.content"
                                :rules="validationRules.content"
                            />
                        </InputWrapper>
                    </FormSection>

                    <FormSection label="Vigencia">
                        <InputWrapper label="Fecha de expiración" class="w-full">
                            <VDatePicker
                                v-model="newItem.expires_on"
                                mode="date"
                                trim-weeks
                                :popover="{ visibility: 'focus' }"
                            >
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input
                                        class="px-2 py-1 border rounded focus:outline-none focus:border-primary w-full"
                                        :value="inputValue"
                                        v-on="inputEvents"
                                    />
                                </template>
                            </VDatePicker>
                        </InputWrapper>
                    </FormSection>

                </Form>

                <FormControls
                    :newItem="newItem"
                    :saveAndExit="true"
                    @submitForm="submitAnnouncements()"
                    @submitFormAndExit="submitAnnouncements(true)"
                />
            </div>
        </Section>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { Form, Field, ErrorMessage } from 'vee-validate';
import * as yup from 'yup';
import { Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default defineComponent({
    components: {
        Form,
        ErrorMessage,
        Field,
        "VCalendar": Calendar,
        "VDatePicker": DatePicker,
    },

    mixins: [AdminMixin],

    data() {
        return {
            id: this.$route.params.id,
            validationRules: {
                title: yup.string().required('El título es obligatorio'),
                content: yup.string().required('El contenido es obligatorio'),
            },
            isLoading: true,
        };
    },

    async mounted() {
        if (this.id) {
            await this.loadAnnouncementData(this.id);
        } else {
            this.newItem = {
                is_active: true,
                title: '',
                content: '',
                expires_on: null
            };
            this.isLoading = false;
        }
    },

    computed: {
        validationSchema() {
            return yup.object().shape({
                title: this.validationRules.title,
                content: this.validationRules.content,
            });
        },
    },

    methods: {
        async loadAnnouncementData(id) {
            try {
                const response = await this.$axios.get(`/api/announcements/filter`, { params: { id: id } });
                // Note: filter might return a paginated object or the item directly depending on how it's called.
                // However, often there's a specific GET by ID. But AnnouncementsController doesn't have it.
                // Let's check if we can get it from the filter.
                if (response.data && response.data.data && response.data.data.length > 0) {
                    this.newItem = response.data.data[0];
                    if (this.newItem.expires_on) {
                        this.newItem.expires_on = new Date(this.newItem.expires_on);
                    }
                } else {
                    this.openAlert("error", "Anuncio no encontrado");
                    this.$router.push({ name: 'home' });
                }
            } catch (error) {
                console.error(error);
                this.openAlert("error", "Error al cargar el anuncio");
            } finally {
                this.isLoading = false;
            }
        },

        formatDate(date) {
            if (!date) return null;
            if (!(date instanceof Date)) date = new Date(date);
            return new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        },

        async submitAnnouncements(flag = false) {
            if (this.submitting) return;
            this.submitting = true;

            const result = await this.$refs.form.validate();
            if (!result.valid || !this.newItem.content) {
                this.submitting = false;
                if (!this.newItem.content) {
                    // Manual validation for textarea if vee-validate is tricky with it here
                    this.openAlert("info", "El campo Contenido no puede estar vacío");
                } else {
                    this.openAlert("info", "Rellene correctamente el formulario");
                }
                return;
            }

            this.isLoading = true;
            const dataToSave = { ...this.newItem };
            dataToSave.expires_on = this.formatDate(dataToSave.expires_on);

            const slug = !this.id
                ? { method: 'post', url: '/api/announcements/create' }
                : { method: 'put', url: `/api/announcements/${this.id}` };

            try {
                const response = await this.$axios[slug.method](slug.url, dataToSave);
                const id = response.data.id;

                const message = this.id ? "Anuncio actualizado con éxito." : "Anuncio añadido con éxito.";
                this.openAlert("success", message, null);

                if (flag) {
                    this.$router.push({ name: 'home' });
                } else {
                    if (!this.id) {
                         this.$router.push({ name: 'announcementsForm', params: { id: id } });
                    }
                    this.submitting = false;
                    this.isLoading = false;
                }
            } catch (error) {
                this.submitting = false;
                this.isLoading = false;
                console.error(error);
                this.openAlert("error", "Error al guardar el anuncio");
            }
        },
    },
});
</script>
