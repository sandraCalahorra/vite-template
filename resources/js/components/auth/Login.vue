<template>
<AuthTemplate
    title="Iniciar sesión"
    subtitle="Accede con tu cuenta."
>
    <Form
        :validation-schema="validationSchema"
        ref="form"
        @submit="login"
        @keyup.enter="login"
        class="space-y-5"
    >
        <StyledInputText
            name="email"
            v-model="credentials.email"
            type="email"
            placeholder="correo@centro.es"
            :rules="validationRules.email"
        />

        <StyledInputText
            name="password"
            v-model="credentials.password"
            type="password"
            placeholder="Contraseña"
            :rules="validationRules.password"
        />

        <div class="flex justify-center">
            <button
                type="button"
                class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline"
                @click="redirectToResetPassword"
            >
                ¿Has olvidado tu contraseña?
            </button>
        </div>

        <p v-if="showErrors" class="rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-600">
            Credenciales incorrectas.
        </p>

        <ButtonAction
            label="Entrar"
            color="primary"
            size="md"
            customClass="w-full uppercase"
            @action="login"
        />
    </Form>
</AuthTemplate>
</template>

<script>
import { jwtDecode } from 'jwt-decode';
import * as yup from 'yup';
import { Form, Field, ErrorMessage } from 'vee-validate';

export default {
    name: "Login",

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
                password: ""
            },
            validationRules: {
                email: yup.string().email('Email inválido').required('Email es obligatorio'),
                password: yup.string()
                .required('Contraseña es obligatorio')
                .min(4, 'Contraseña tiene que tener al menos 4 caracteres'),
            },
        };
    },

    computed: {
        validationSchema() {
            return yup.object().shape({
                email: this.validationRules.email,
                password: this.validationRules.password,
            });
        },
    },
    mounted(){

        const token = localStorage.getItem('token');
        if (token&&!this.isValidToken(token)) {
            localStorage.removeItem('token');
            this.openAlert('error','SESIÓN EXPIRADA','Por favor vuelva a iniciar sesión');
        }
    },

    methods: {
        login() {
        this.$axios
            .post("/api/login", this.credentials)
            .then((response) => {
                const token = response.data.token;

                if (token) {
                    // Save token to Local Storage
                    localStorage.setItem('token', token);

                    // Save user to Local Storage
                    this.$store.dispatch("auth/setUser", response.data.user);

                    // Route user to Home view
                    this.$router.push({ name: "home" });
                }
            })
            .catch(() => {
                this.showErrors = true;
            });
        },

        isValidToken(token) {
            try {
                const decodedToken = jwtDecode(token);
                if (!decodedToken) {
                return false;
                }

                // Check expiration time
                const currentTime = Math.floor(Date.now() / 1000);
                return decodedToken.exp > currentTime;
            } catch (error) {
                console.error('Error decoding token:', error);
                return false;
            }
        },

        redirectToResetPassword(){
            this.$router.push({name: 'forgotPassword'});
        },
    }
};
</script>
