<template>
    <div class="top-0 overflow-hidden transition-all duration-300 ease-in-out">

        <transition name="slide-nav transition-all duration-300 ease-in-out">
            <div
                class="bg-[linear-gradient(-120deg,var(--primary-medium-dark)_20%,var(--primary-medium-light)_80%)] overflow-hidden shadow-lg transition-all duration-300 ease-in-out"
                :class="[
                    size == 'mobile' ? 'h-full relative w-full' : '',
                    size == 'mobile' && isNavbarOpen ? 'h-screen' : '',
                    size != 'mobile' ? 'h-full fixed' : '',
                    size != 'mobile' && isNavbarOpen ? 'h-full w-50' : 'h-full w-16',
                ]"
            >

                <!-- MOBILE BAR -->
                <div
                    v-if="size == 'mobile'"
                    class="relative top-0 z-50 flex items-center justify-between flex-1 h-auto px-3 py-2 sm:hidden bg-primary"
                >
                    <!-- LOGO -->
                    <router-link :to="{ name: 'home' }" class="flex justify-center not-active">
                        <img class="block h-10 w-auto xl:hidden" src="/img/vitejs.svg" alt="Logo_small">
                    </router-link>

                    <!-- HAMBURGER -->
                    <div @click="toggleSidebar" class="flex items-center justify-center cursor-pointer">
                        <component :is="isNavbarOpen ? 'IconXSolid' : 'IconHamburger'" class="text-white size-6" />
                    </div>
                </div>

                <div class="flex flex-row">
                    <!-- Desktop and Tablet View -->
                    <div
                        class="top-0 flex-col w-full gap-2 overflow-auto transition-all duration-200 transform"
                        :class="{
                            'hidden': size == 'mobile' && !isNavbarOpen,
                            'flex-col inline-flex h-full': size == 'mobile' && isNavbarOpen,
                            'flex-col h-screen': size != 'mobile',
                        }"
                    >
                        <!-- Navbar content -->
                        <div
                            class="flex flex-col h-full gap-1 z-90"
                        >
                            <div class="hidden gap-2 pt-2 sm:flex sm:flex-col z-90">
                                <!-- App logo -->
                                <div class="items-center justify-center hidden h-auto sm:flex xl:h-auto py-4">
                                    <router-link :to="{ name: 'home' }" class="flex items-center justify-center">
                                        <img
                                            :class="[size != 'mobile' && isNavbarOpen ? 'h-20' : 'h-10']"
                                            class="w-auto object-contain transition-all duration-300"
                                            src="/img/vitejs.svg"
                                            alt="Logo_small"
                                        />
                                    </router-link>
                                </div>
                            </div>

                            <nav
                                class="flex flex-col flex-grow z-90"
                                :class="{
                                    'gap-0' : size == 'mobile' || !isNavbarOpen,
                                    'gap-2' : size != 'mobile' && isNavbarOpen,
                                }"


                            >
                                <div class="flex flex-col sidebar">
                                    <NavbarLink :to="{ name: 'home' }" :text="'Home'" active="/">
                                        <IconBriefcaseSolid class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4"  />
                                    </NavbarLink>
                                </div>

                                <component :is="size != 'mobile' && isNavbarOpen ? 'DropdownExpand' : 'div'"
                                    v-if="$can('users', 'view', { lt: 20 })"
                                    title="Usuarios"
                                    :initialExpanded="true"
                                    class="flex flex-col"
                                >

                                    <NavbarLink :to="{ name: 'users' }" :text="'Usuarios'" active="/usuarios">
                                        <IconUsersSolid class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4"  />
                                    </NavbarLink>

                                    <NavbarLink :to="{ name: 'roles' }" :text="'Roles'"  active="/roles" v-if="$can('settings', 'view', { lt: 10 })">
                                        <IconKeySolid class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4"  />
                                    </NavbarLink>
                                </component>

                                <component :is="size != 'mobile' && isNavbarOpen ? 'DropdownExpand' : 'div'"
                                    title="Administración"
                                    :initialExpanded="true"
                                    class="flex flex-col"

                                >
                                    <NavbarLink :to="{ name: 'userManual'}" :text="'Manual del usuario'" active="/manual-del-usuario">
                                        <IconQuestionDocument class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4" />
                                    </NavbarLink>

                                    <NavbarLink :to="{ name: 'tickets'}" :text="'Centro de soporte'" active="/centro-de-soporte" v-if="$can('settings', 'view', { lt: 20 })">
                                        <IconSupport class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4" />
                                    </NavbarLink>
                                </component>

                                <component :is="size != 'mobile' && isNavbarOpen ? 'DropdownExpand' : 'div'"
                                    title="Ajustes" :initialExpanded="true"
                                    class="flex flex-col"
                                    v-if="$can('settings', 'view', { lt: 10 })"
                                >
                                    <NavbarLink :to="{ name: 'settings' }" text="Ajustes" active="/ajustes">
                                        <IconWrenchSolid class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4"/>
                                    </NavbarLink>

                                    <NavbarLink :to="{ name: 'permissions' }" :text="'Permisos'" active="/permisos">
                                        <IconPermission class="w-5 h-5 min-w-5 xl:w-4 xl:h-4 xl:min-w-4"/>
                                    </NavbarLink>
                                </component>

                                <!-- Logout -->
                                <div
                                    class="flex-col justify-end flex-1 w-full h-full z-90"
                                    :class="[
                                        size == 'mobile' && isNavbarOpen ? 'flex' : 'hidden',
                                    ]"
                                >
                                    <Separator class="w-full bg-white opacity-30" />

                                    <NavbarLink @click="logout" :to="{ name: '' }" text="Cerrar sesión" active="logout">
                                        <IconLogout class="w-6 h-6 min-w-6 xl:w-5 xl:h-5 xl:min-w-5"/>
                                    </NavbarLink>
                                </div>
                            </nav>

                                <AppVersion
                                    :allParams="allParams"
                                    @openModal="openModal"
                                    class="justify-end z-90"
                                    :class="
                                    [
                                        size != 'mobile' && isNavbarOpen ? 'flex pb-2' : 'hidden',
                                    ]"
                                />

                            <div
                                class="w-full px-1 py-2 text-center z-90"
                                :class="
                                [
                                    size != 'mobile' && !isNavbarOpen ? 'flex' : 'hidden'
                                ]"
                            >
                                <div class="flex flex-row justify-center w-full">
                                    <a href="https://www.ecomputer.es" target="_blank">
                                        <img src="/img/raton-ecomputer.png" class="block text-center size-8"
                                            alt="Logo Ecomputer">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                       v-if="size != 'mobile'"
                       @click="toggleSidebar"
                       :class="{
                           'left-[11.25rem]': isNavbarOpen,
                           'left-11': !isNavbarOpen,
                       },
                       [
                           size != 'mobile' && isNavbarOpen ? 'hover:animate-wiggle-left' : '-rotate-0 hover:animate-wiggle-right',
                        ]"
                       class="fixed flex items-center justify-center p-1 text-white transition-all duration-300 ease-in-out rounded-full shadow-lg z-80 bottom-14 size-10 bg-gradient-to-bl from-primary-300 to-primary-500 hover:brightness-highlight"
                   >
                       <HeroiconChevronRight class="pointer-events-none size-5"
                            :class="{
                                '-rotate-180': isNavbarOpen,
                            }"
                       />
                   </button>
                </div>
            </div>
        </transition>

        <ChangelogModal
            v-if="isModalOpen && hasChangelog"
            :open="isModalOpen"
            :title="'Notas de la versión '"
            :date="formattedDate  || 'N/A'"
            :version="changelog.version"
            @close="isModalOpen = false"
            :changelog="changelog"
        />
    </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { mapState, mapMutations, mapGetters } from "vuex";
import AdminMixin from "@/components/mixins/AdminMixin.vue";
import 'vue-multiselect/dist/vue-multiselect.css';
import { size } from 'lodash';

export default {
    mixins: [AdminMixin],

    props: {
        allParams: { type: Object, default: () => ({}) },
        changelog: { type: Object, default: () => ({}) },
    },

    data() {
        return {
            buttonWidth: 45,
            minButtonWidth: 45,
            open: false,
            isWarningUserModalOpen: false,
            // allParams: {},
            arrayTables: ['Inicio', 'Usuarios', 'Noticias'],
            isModalOpen: false,
            // isEmpty: false,
            // changelog: null,
            actionOptions: [
                { id: 1, name: 'Programadores', case: 'devs' },
            ],
            action: {}
        };
    },

    setup() {
    },

    async mounted() {
        try {
            if (this.user == null) {
                console.clear();
            }

        } catch (error) {
            if (error.response && error.response.data.logout) {
                const keysToRemove = error.response.data.actions?.remove_local_storage || ['user', 'token'];
                keysToRemove.forEach(key => localStorage.removeItem(key));
                this.$store.dispatch('auth/setUser', null);
                const redirectName = error.response.data.actions?.redirect || 'login';
                this.$router.push({ name: redirectName });
                return;
            }
            console.error('Error en mounted:', error);
        }
    },

    watch: {
        async user() {
            // const result = await this.fetchAllData(`v2/app_params`);
            // this.allParams = result;
            // this.triggerModal();
        }
    },

    created() {
        // this.fetchChangelogData();
    },

    computed: {
        ...mapState(["alert", "size"]),
        ...mapGetters(["isAlertOpen", "isNavbarOpen"]),

        size() {
            return this.$store.state.size;
        },

        isNavbarOpen() {
            return this.$store.state.isNavbarOpen;
        },

        formattedDate() {
            const date = new Date(this.changelog?.created_at);
            return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        hasChangelog() {
            return this.changelog && Object.keys(this.changelog).length > 0;
        },
    },

    emits: ["toggleSidebar"],

    methods: {
        toggleSidebar() {
            this.$store.commit('TOGGLE_NAVBAR_STATE');
        },

        async openModal(value) {
            // this.changelog = await this.fetchAllData('/api/v2/changelog');
            // this.isModalOpen = value;
            if (this.hasChangelog) {
                this.isModalOpen = true;
            }
        },

        logout() {
            const token = localStorage.getItem('token');
            axios.post('/api/logout', {}, { headers: { Authorization: `Bearer ${token}` } })
                .then(() => {
                    this.$store.dispatch("auth/setUser", null);
                    localStorage.removeItem('user');
                    localStorage.removeItem('token');
                    this.$router.push({ name: "login" });
                });
        },


        triggerModal() {
            if (this.allParams) {
                if(this.allParams?.app_params?.version && this.allParams?.app_params?.version != this.$package.version){
                    this.fetchChangelogData();
                    this.isWarningUserModalOpen = true;
                    this.isLoading = true;
                }
            }
        },

        async fetchChangelogData() {
            try {
                const response = await fetch('/api/v2/changelog');
                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }
                this.changelog = await response.json();
            } catch (error) {
                console.error('Error fetching changelog data:', error);
            }
        },

    }
};
</script>

<style scoped>

.h-4-5 {
    height: 1.1rem;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.1s ease;
}

.fade-enter, .fade-leave-to {
    opacity: 0;
}

button span {
    display: inline-block;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: clip;
    transition: opacity 0.2s ease, width 0.2s ease;
}

button .w-5.h-5 {
    flex-shrink: 0;
}

/* Navbar animations */
.slide-nav-enter-active {
    transition: transform 0.4s ease, opacity 0.4s ease;
}

.slide-nav-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-nav-enter-to {
    transform: translateX(0);
    opacity: 1;
}


.slide-nav-enter-active,
.slide-nav-leave-active {
    transition: margin-left 0.4s ease;
}

.slide-nav-enter-from {
    margin-left: -16rem; /* -w-64 */
}

.slide-nav-enter-to {
    margin-left: 0;
}

.slide-nav-leave-from {
    margin-left: 0;
}

.slide-nav-leave-to {
    margin-left: -16rem;
}

/* Hide scrollbar */
.no-scrollbar {
    scrollbar-width: none;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.sidebar {
  min-width: 0;
}

@keyframes wiggle-left {
    0%, 100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(-4px);
    }
}

.hover\:animate-wiggle-left:hover {
    animation: wiggle-left 1s infinite;
}

</style>
