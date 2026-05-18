<template>
<AuthTemplate
    title="Recuperar contraseña"
    subtitle="Te enviaremos un enlace de recuperación."
    eyebrow="Recuperación"
>
    <Form
        :validation-schema="validationSchema"
        ref="form"
        @submit="resetPassword"
        @keyup.enter="resetPassword"
        class="space-y-5"
    >
        <StyledInputText
            name="email"
            v-model="credentials.email"
            type="email"
            placeholder="correo@centro.es"
            :rules="validationRules.email"
        />

        <ButtonAction
            label="Enviar correo"
            color="primary"
            size="md"
            customClass="w-full uppercase"
            @action="resetPassword"
        />

        <div class="flex justify-center">
            <button
                type="button"
                class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline"
                @click="redirectToLogin"
            >
                Volver al login {{ automaticRedirectMessage }}
            </button>
        </div>
    </Form>
</AuthTemplate>
</template>

<script>
import * as yup from 'yup';
import { Form, Field, ErrorMessage } from 'vee-validate';
import moment from "moment";

export default {
    name: "ForgetPassword",

    components: {
        Form,
        ErrorMessage,
        Field,
    },

    provide() {
        return {
            validationRules: this.validationRules,
        };
    },

    data() {
        return {
            showErrors: false,

            credentials: {
                email: "",
            },
            is_enabled: false,
            validationRules: {
                email: yup.string().email('El Correo electrónico no es válido').required('El Correo electrónico es obligatorio'),
            },
            countdown: null,
            endTime: moment().add(7, "seconds"),
            interval: null,
        };
    },

    computed: {
        validationSchema() {
            return yup.object().shape({
                email: this.validationRules.email,
            });
        },
        automaticRedirectMessage(){
            return this.countdown != null ? ` (Redirigiendo automáticamente en ${this.countdown} segundos)` : ''
        }
    },

    mounted(){

    },

    methods: {
        async resetPassword(){
            const result = await this.$refs.form.validate();

            // If validation fails, return to avoid proceeding
            if (await this.validateForm(result)) return;

            // Proceed if validation is successful
            if (result.valid) {
                try {
                    await this.$axios.post('/api/forgot-password', { email: this.credentials.email });
                    this.openAlert('info', 'Se ha mandado un correo a la dirección introducida')
                    this.updateCountdown();
                    setTimeout(() => {
                        this.$router.push({name: 'login'});
                    clearInterval(this.interval);
                    }, 7000);
                } catch (error) {
                    console.error(error);

                    // Check if error is a response from the backend
                    if (error.response) {
                        const errorData = error.response.data;

                        // Handle the case where the email is not found
                        if (errorData.message === "We can't find a user with that email address.") {
                            this.openAlert('error', 'No podemos encontrar un usuario con esa dirección de correo electrónico.');
                        }
                        // Handle the case where the user needs to wait before retrying
                        else if (errorData.message === "Please wait before retrying.") {
                            this.openAlert('error', 'Por favor espere unos minutos antes de volver a intentarlo.');
                        }
                        // Handle any other generic errors
                        else {
                            this.openAlert('error', 'Ha ocurrido un error, si persiste contacte con un Administrador');
                        }
                    }
                    // Catch network or other errors
                    else {
                        this.openAlert('error', 'Ha ocurrido un error inesperado, por favor intente más tarde.');
                    }
                }
            }

        },

        updateCountdown() {
            this.endTime = moment().add(7, "seconds");
            this.interval = setInterval(() => {
                const now = moment();
                const duration = moment.duration(this.endTime.diff(now));

                this.countdown = duration.seconds();
            }, 1000);
        },

        redirectToLogin(){
            this.$router.push({name: 'login'});
        },

        async validateForm(result) {
            // Convert result.errors into an array for easier manipulation
            const errors = Object.entries(result.errors);

            if (errors.length){
                let resultText = [
                    ...errors.map(([_, msg]) => msg) // Include all errors from form validation
                ].filter(Boolean).join('<br />'); // Remove empty messages and join with line breaks

                this.openAlert("info", "Rellene correctamente el formulario", resultText);

                return true;
            }
            // Return false if no validation errors found
            return false;
        },
    }
};
</script>
