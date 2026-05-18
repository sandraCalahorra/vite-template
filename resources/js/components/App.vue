<template>
    <div class="relative h-dvh w-dvw text-gray-900 max-w-dvw">

        <!-- Modals -->
        <TheModal class="z-50" :blockModal="true" :open="isUpdating" title="ACTUALIZANDO EKD..." :hasFooter="false"
            :hasHeader="false">
            <template #body>
                <div class="flex flex-col justify-center">
                    <div class="flex flex-col gap-5">
                        Estamos actualizando la plataforma a la última versión. Por favor, espera un momento mientras se completa
                        el proceso de actualización.
                    </div>
                </div>
            </template>

        </TheModal>

        <TheModal class="z-50" :blockModal="true" :open="oops" title="HA OCURRIDO UN PROBLEMA" :hasFooter="false">
            <template #body>
                <div class="flex flex-col justify-center">
                    <div class="flex flex-col gap-2 ">
                        <p>Parece que estamos experimentando un problema técnico.</p>
                        <p>Prueba a recargar la página con <strong>Ctrl + F5</strong>.</p>
                        <p>Si el problema persiste, inténtalo de nuevo más tarde o contacta con el soporte técnico.</p>
                    </div>
                </div>
            </template>

        </TheModal>

        <TheModal class="z-50" :blockModal="true" :open="justUpdated === true ? true : false"
            title="EKD SE HA ACTUALIZADO 🎉" :actionButton="{
                text: '¡Entendido!',
                action: acknowledgeUpdate,
                disabled: false,
            }">
            <template #body>
                <div class="flex flex-col justify-center">
                    <p class=""><strong>Notas de la versión {{ changelog?.version }}</strong></p>
                    <div class="flex flex-col gap-5 mt-3 text-sm">
                        <FormSection label="Novedades" borderColor="#ad8ce6" legendColor="#5a3b8f"
                            v-if="changelog?.news">
                            <template #icon>
                                <IconAutoAwesoneFilled class="size-5" />
                            </template>
                            <div class="ps-8" v-html="changelog?.news || 'Sin novedades.'"></div>
                        </FormSection>
                        <FormSection label="Mejoras" borderColor="#97e68c" legendColor="#428a38"
                            v-if="changelog?.improvements">
                            <template #icon>
                                <IconSettings class="size-5" />
                            </template>
                            <div class="ps-8" v-html="changelog?.improvements || 'Sin mejoras.'"></div>
                        </FormSection>
                        <FormSection label="Arreglos" borderColor="#e68c8c" legendColor="#8a2c2c"
                            v-if="changelog?.bugfixes">
                            <template #icon>
                                <IconBug class="size-5" />
                            </template>
                            <div class="ps-8" v-html="changelog?.bugfixes || 'Sin arreglos.'"></div>
                        </FormSection>
                    </div>
                </div>
            </template>

        </TheModal>

        <TheModal class="z-90" :blockModal="true" :open="isConsoleModalOpen" title="PANEL CONTROL"
            @close="isConsoleModalOpen = false">
            <template #body>
                <div class="flex flex-col justify-center">
                    <h2>Programadores</h2>
                    <ul class="gap-2 pl-5 list-disc">
                        <li>Paula Béjar</li>
                        <li>Liviu-Adrian Dodan</li>
                        <li>Sandra Calahorra</li>
                        <li>Daniel Martinez Lorda</li>
                        <li>Tomás Generelo</li>
                    </ul>
                </div>
            </template>
            <template #action>
                <ButtonAction
                    :label="'Aceptar'"
                    color="green"
                    size="sm"
                    @click="isConsoleModalOpen = false"
                />
            </template>
        </TheModal>
        <!-- App body -->
        <div class="relative max-w-dvw max-h-dvh"
            :class="{
                'flex flex-col' : size == 'mobile',
                'flex flex-row' : size == 'tablet' || size == 'desktop',
            }"
        >
            <!-- Sidebar, fixed -->
            <TheNavbar
                v-if="showNavbar"
                class="overflow-hidden z-50 transition-all duration-300 ease-in-out"
                :class="[
                    size == 'mobile' ? 'fixed top-0 left-0 w-full' : 'relative sm:h-28 h-full',
                    size == 'mobile' && isNavbarOpen ? 'h-screen' : (size == 'mobile' ? 'h-12' : ''),
                    size != 'mobile' && isNavbarOpen ? 'w-50' : (size != 'mobile' ? 'w-16' : ''),
                ]"
                :allParams="appParams"
                :changelog="changelog"
            />

            <!-- Main content, scrollable -->
            <div class="relative flex-1 w-full max-w-full overflow-x-hidden min-h-dvh"
                :class="{
                    'hidden' : size == 'mobile' && isNavbarOpen,
                    'pt-12': size == 'mobile' && !isNavbarOpen && showNavbar
                }"
            >
                <Announcements
                    v-if="announcements.length > 0"
                    :announcements="announcements"
                    @close="closeAnnouncement"
                />

                <router-view v-slot="{ Component }">
                    <transition mode="out-in" enter-active-class="transition-all duration-100 ease-linear"
                        leave-active-class="transition-all duration-100 ease-linear" enter-class="opacity-0"
                        enter-to-class="opacity-100" leave-class="opacity-100" leave-to-class="opacity-0">
                        <component :is="Component" />
                    </transition>
                </router-view>

                <Alert :color="alert.color" :open="isAlertOpen" :title="alert.title" :description="alert.description"
                    @close="CLOSE_ALERT" />
            </div>
        </div>

    </div>
</template>

<script>
import { ref, watch, onBeforeUnmount } from 'vue';
import { mapState, mapMutations, mapGetters } from "vuex";
import { useRoute } from 'vue-router';
import { size } from 'lodash';

export default {
    components: {
    },

    computed: {
        ...mapState(["alert", "size"]),
        ...mapGetters(["isAlertOpen", "isNavbarOpen"]),
        size() {
            return this.$store.state.size;
        },
        showNavbar() {
            const excludedRoutes = ['/login', 'forgotPassword', 'resetPassword'];
            return !excludedRoutes.includes(this.$route.path) && !excludedRoutes.includes(this.$route.name);
        }
    },
    setup() {
        const appParams = ref({});
        const currentRoute = useRoute();
        const changelog = ref(null);
        const justUpdated = ref(false);

        // Return variables and functions that you want to expose to the template
        const isConsoleModalOpen = ref(sessionStorage.getItem('altCustomAction') == 'true');

        watch(isConsoleModalOpen, (newVal) => {
            if (newVal) {
                sessionStorage.setItem('altCustomAction', 'true');
            } else {
                sessionStorage.removeItem('altCustomAction');
            }
        });
        window.addEventListener('storage', (event) => {
            if (event.key === 'altCustomAction') {
                isConsoleModalOpen.value = event.newValue === 'true';
            }
        });

        return {
            appParams,
            currentRoute,
            changelog,
            justUpdated,
            updateModalOpen: true,
            oops: false,
            isUpdating: false,
            isConsoleModalOpen,
        };
    },

    data() {
        return {
            announcements: [],
            showAnnouncements: false,
        };
    },

    async mounted() {
        this.setAppCurrentSize();
        window.addEventListener('resize', () => this.setAppCurrentSize());

        const startNavbarOpen = sessionStorage.getItem('isNavbarOpen') ?? null;

        if (startNavbarOpen === null) {
            switch (this.$store.state.size) {
                case 'desktop':
                    this.$store.state.isNavbarOpen = true;
                    break;
                case 'mobile':
                case 'tablet':
                default:
                    this.$store.state.isNavbarOpen = false;
                    break;
            }
        } else if (this.$store.state.size != 'mobile' && startNavbarOpen == 'true') {
            this.$store.state.isNavbarOpen = true;
        } else {
            this.$store.state.isNavbarOpen = false;
        }

        // Set justUpdated to true if the version has changed
        this.checkCode();
        // this.checkVersion();
        // await this.checkAppParams();
    },

    beforeUnmount() {
        try {
            window.removeEventListener('resize', this.setAppCurrentSize());
        } catch (error) {
            console.error('Error while removing event listener:', error);
        }
    },

    methods: {

        setAppCurrentSize() {
            const size = window.innerWidth < 640 ? 'mobile' : window.innerWidth < 1280 ? 'tablet' : 'desktop'
            if (this.$store.state.size !== size) {
                this.$store.commit("SET_SIZE", size);
            }
        },

        toggleSidebar() {
            this.$store.commit('TOGGLE_NAVBAR_STATE');
            console.log('toggleSidebar()', this.$store.state.isNavbarOpen);
        },

        ...mapMutations(["CLOSE_ALERT", "TOGGLE_NAVBAR_STATE", "OPEN_NAVBAR", "CLOSE_NAVBAR", "SET_SIZE"]),
        checkCode() {

            const sequences = [
                {
                    code: ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'],
                    action: () => this.handleCustomAction(),
                    index: 0
                },
                {
                    code: ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'c', 'm', 'd'],
                    action: () => this.handleAltAction(),
                    index: 0
                }
            ];

            const handleKeyUp = (event) => {
                sequences.forEach(seq => {
                    if (event.key === seq.code[seq.index]) {
                        seq.index++;
                        if (seq.index === seq.code.length) {
                            seq.action(); // Trigger action
                            seq.index = 0; // Reset after full match
                        }
                    } else {
                        seq.index = 0; // Reset if mismatch
                    }
                });
            };
            window.addEventListener('keyup', handleKeyUp);

            // Clean up
            onBeforeUnmount(() => {
                window.removeEventListener('keyup', handleKeyUp);
            });
        },
        handleAltAction() {
            const current = sessionStorage.getItem('altCustomAction');
            let message = '';

            if (current) {
                sessionStorage.removeItem('altCustomAction');
                message = 'Algo alternativo desactivado tú has (¬_¬)';
            } else {
                sessionStorage.setItem('altCustomAction', 'true');
                message = 'Algo alternativo activado tú has (°□°)';
                window.location.reload();
            }

            this.openAlert('info', message);
        },
        handleCustomAction() {
            let enabled = sessionStorage.getItem('customAction');

            let text = '';
            if (enabled) {
                sessionStorage.removeItem('customAction');
                text = 'Algo desactivado tú has <(°-°)>';
            } else {
                sessionStorage.setItem('customAction', true);
                text = 'Algo activado tú has <(°w°)>';
            }
            this.openAlert('info', text);
        },

        async checkVersion() {

            // this.justUpdated = false; // Ensure the justUpdated modal is hidden initially

            // const result = await this.fetchAllData(`/api/v2/app_params`);
            const result = await this.fetchAllGetData(`/api/retrieve_app_params`, {level: this.user?.level});
            const version = result?.app_params?.version;
            this.justUpdated = sessionStorage.getItem('justUpdated') === 'true' ? true : false;

            if (version && version != this.$package?.version) {
                console.log(`Current version: ${this.$package?.version}, New version: ${version}, they are different: ${this.$package?.version != version}`);
                this.justUpdated = false
                this.isUpdating = true;
            }

            const reloadCount = parseInt(sessionStorage.getItem('reloadCount') || '0', 10);

            if (reloadCount >= 2) {
                this.isUpdating = false; // Stop updating
                this.oops = true; // Display the "Oops" modal
                sessionStorage.removeItem('reloadCount'); // Reset the counter
                return;
            }


            if (version && version != this.$package?.version) {
                sessionStorage.setItem('justUpdated', true);
                sessionStorage.setItem('reloadCount', reloadCount + 1); // Increment the counter
                window.location.reload(true);
            } else {
                sessionStorage.removeItem('reloadCount'); // Reset the counter if versions match

                if (sessionStorage.getItem('justUpdated') === 'true') {
                    // this.changelog = await this.fetchAllData('/api/v2/changelog'); // Fetch changelog
                    this.changelog = result?.changelog;
                    this.justUpdated = true; // Show the justUpdated modal
                }

                this.isUpdating = false; // Hide the updating modal

                const permissions = result?.permissions?.length > 0 ? result?.permissions : [];

                if (permissions) this.handlePermissionsInStore(permissions);
            }
        },

        async checkAppParams() {
            try {
                this.oops = false;
                const result = await this.fetchAllGetData(`/api/retrieve_app_params`, { level: this.user?.level });

                this.appParams = result?.app_params || {};
                this.changelog = result?.changelog || null;
                const projectTypes = result?.project_types || [];

                const excludedRoutes = [
                    '/login',
                    'forgotPassword',
                    'resetPassword',
                    'webDocumentationRequest'
                ];

                if (!excludedRoutes.includes(this.$route.path) && !excludedRoutes.includes(this.$route.name)) {
                    if (result?.announcements && Array.isArray(result.announcements)) {
                        this.announcements = result.announcements.map(a => ({ ...a, dismissed: false }));
                    } else {
                        this.announcements = [];
                    }
                } else {
                    this.announcements = [];
                }

                this.checkDismissedAnnouncements();

                const version = result?.app_params?.version;
                const maintenance = result?.app_params?.maintenance;

                if (maintenance == 1) {
                    const alreadyReloaded = sessionStorage.getItem('maintenanceReloaded') === 'true';
                    if (!alreadyReloaded) {
                        sessionStorage.setItem('maintenanceReloaded', 'true');
                        window.location.reload(true);
                        return;
                    }
                    this.isUpdating = true;
                } else {
                    this.isUpdating = false;
                    sessionStorage.removeItem('maintenanceReloaded');
                }

                this.justUpdated = sessionStorage.getItem('justUpdated') === 'true';

                if (version && version !== this.$package?.version) {
                    console.log(`Versión actual: ${this.$package?.version}, nueva: ${version}`);
                    this.justUpdated = false;
                    this.isUpdating = true;

                    const reloadCount = parseInt(sessionStorage.getItem('reloadCount') || '0', 10);
                    if (reloadCount >= 2) {
                        this.isUpdating = false;
                        this.oops = true;
                        sessionStorage.removeItem('reloadCount');
                        return;
                    }
                    sessionStorage.setItem('justUpdated', true);
                    if (this.oops) return;
                    sessionStorage.setItem('reloadCount', reloadCount + 1);
                    window.location.reload(true);
                    return;
                }

                sessionStorage.removeItem('reloadCount');

                if (sessionStorage.getItem('justUpdated') === 'true') {
                    this.changelog = result?.changelog || null;
                    this.justUpdated = true;
                }

                this.isUpdating = false;

                const permissions = result?.permissions ? result?.permissions : [];
                if (permissions) this.handlePermissionsInStore(permissions);

            } catch (error) {
                console.error("Error al comprobar app_params (versión o mantenimiento)", error);
            }
        },

        checkDismissedAnnouncements() {
            if (!Array.isArray(this.announcements)) return;
                const dismissedIds = JSON.parse(localStorage.getItem('dismissedAnnouncements') || '[]');
                this.announcements.forEach(a => {
                    if (dismissedIds.includes(a.id)) a.dismissed = true;
            });
        },

        closeAnnouncement(id) {
            this.announcements = this.announcements.map(a => ({
                ...a,
                dismissed: a.id === id ? true : a.dismissed
            }));

            const dismissedIds = JSON.parse(localStorage.getItem('dismissedAnnouncements') || '[]');
            dismissedIds.push(id);
            localStorage.setItem('dismissedAnnouncements', JSON.stringify(dismissedIds));
        },

        acknowledgeUpdate(e) {
            this.justUpdated = false;
            sessionStorage.removeItem('justUpdated');
            // window.location.reload(true);
        },

    },

    watch: {
        async '$route.name'(newName, oldName) {

            if (newName != oldName) {

                if (this.size == 'mobile') {
                    this.$store.commit('CLOSE_NAVBAR');
                }

                if (
                    newName != 'login'
                    && newName != 'forgotPassword'
                    && newName != 'resetPassword'
                    && newName != 'webDocumentationRequest'
                ) {
                    const storedRoute = sessionStorage.getItem('storedRoute');

                    const shouldStoreRoute = storedRoute && newName == 'projects' && oldName == 'login';

                    // This condition is to avoid storing the route when the user is logging in and already have a stored route
                    if (!shouldStoreRoute) {
                        sessionStorage.setItem('storedRoute', window.location.pathname);
                    }
                    // this.checkVersion();
                    await this.checkAppParams();
                }
            }
        }
    },

}

</script>

<style></style>
