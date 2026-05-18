<template>
<div class="flex h-screen w-full bg-slate-100">

    <!-- PANEL INSTITUCIONAL -->
    <div class="hidden lg:flex w-1/2 relative bg-[linear-gradient(45deg,_var(--primary)_20%,_var(--primary-light)_80%)] text-white flex-col justify-between p-16">

        <div class="flex items-center gap-4">
            <img src="/img/montearagon.png" class="h-14"/>
            <div>
                <h1 class="text-xl font-semibold">Residencia CPIFP Montearagón</h1>
                <p class="text-sm opacity-80">Sistema de gestión residencial</p>
            </div>
        </div>

        <div class="max-w-md space-y-6">
            <h2 class="text-3xl font-bold leading-tight">
                Plataforma de gestión
                para residentes,
                familiares y administración
            </h2>

            <p class="text-sm opacity-90">
                Gestiona habitaciones, asistencia a comedor,
                incidencias y datos de alumnos desde un
                entorno centralizado y seguro.
            </p>
        </div>

        <div class="text-xs opacity-70">
            © {{ new Date().getFullYear() }} CPIFP Montearagón
        </div>

    </div>


    <!-- PANEL LOGIN -->
    <div
        class="flex flex-col justify-center items-center w-full lg:w-1/2 p-10
        bg-[linear-gradient(45deg,_var(--primary)_20%,_var(--primary-light)_80%)]
        lg:bg-none lg:bg-gray-50"
    >

        <div class="w-full max-w-md bg-white p-10 rounded-2xl shadow-xl">

            <!-- HEADER -->
            <div class="flex flex-col items-center mb-8">
                <h2 class="text-lg font-bold text-slate-800 uppercase">
                    Acceso al sistema
                </h2>
                <p class="text-sm text-slate-500">
                    Introduce tus credenciales
                </p>
            </div>

            <Form
                :validation-schema="validationSchema"
                ref="form"
                @submit="login"
                @keyup.enter="login"
                class="space-y-6"
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
                    <label
                        class="text-primary hover:text-primary-light cursor-pointer flex text-sm hover:underline"
                        @click="redirectToResetPassword"
                    >
                        He olvidado mi contraseña
                    </label>
                </div>

                <p v-if="showErrors" class="text-sm font-semibold text-red-500">
                    Credenciales incorrectas
                </p>

                <button
                    type="submit"
                    class="relative block w-full py-2 px-3 border border-transparent rounded-md bg-primary hover:bg-primary-light focus:outline-none focus:shadow-outline"
                >
                    <span class="text-sm leading-6 font-semibold text-white tracking-wide">
                        INICIAR SESIÓN
                    </span>
                </button>

            </Form>

        </div>

    </div>

</div>
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
