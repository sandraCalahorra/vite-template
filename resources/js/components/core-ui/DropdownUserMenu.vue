<template>
    <div
        class="relative z-40 flex h-full items-center"
        @mouseenter="isTooltipVisible = true"
        @mouseleave="isTooltipVisible = false"
    >
        <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isTooltipVisible && !isMenuOpen"
                class="pointer-events-none absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] font-medium text-white opacity-0 transition-opacity group-hover:opacity-100"
            >
                Menú de usuario
            </div>
        </transition>

        <button
            type="button"
            class="group flex items-center justify-center gap-2 p-0.5 text-primary transition-all duration-200"
            :aria-expanded="isMenuOpen ? 'true' : 'false'"
            aria-haspopup="menu"
            @click.stop="toggleMenuDropdown"
        >
            <span
                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary text-sm font-semibold text-white transition-all duration-200"
                :class="isMenuOpen ? 'ring-2 ring-primary-300 ring-offset-2' : ''"
            >
                <img
                    v-if="resolvedPhotoUrl"
                    :src="resolvedPhotoUrl"
                    alt="Foto de perfil"
                    class="h-full w-full object-cover"
                />
                <span v-else>{{ userInitials }}</span>
            </span>
        </button>

        <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isMenuOpen"
                class="absolute right-0 top-full mt-2 w-48 transform rounded-lg border border-slate-200 bg-white py-1.5 ring-1 ring-black/5 filter drop-shadow-lg before:absolute before:-top-1.5 before:right-4 before:h-3 before:w-3 before:rotate-45 before:border-l before:border-t before:border-slate-200 before:bg-white"
                role="menu"
                aria-label="Menú de usuario"
            >

                <button
                    type="button"
                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm text-slate-800 transition-all duration-200 hover:bg-slate-50 hover:pl-4 hover:text-primary"
                    role="menuitem"
                    @click="myProfile"
                >
                    <IconUser class="size-4" />
                    <span class="font-medium">Mi perfil</span>
                </button>

                <button
                    type="button"
                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm text-red-600 transition-all duration-200 hover:bg-rose-50 hover:pl-4 hover:text-rose-700"
                    role="menuitem"
                    @click="downloadReport"
                >
                    <IconBug class="size-4" />
                    <span class="font-medium">Descargar reporte</span>
                </button>

                <div class="my-1.5 border-t border-slate-100"></div>

                <button
                    type="button"
                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm text-slate-800 transition-all duration-200 hover:bg-slate-50 hover:pl-4 hover:text-slate-900"
                    role="menuitem"
                    @click="logout"
                >
                    <IconLogout class="size-4" />
                    <span class="font-medium">Cerrar sesión</span>
                </button>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    emits: ["toggle"],

    props: {
        username: {
            type: String,
            default: "",
        },
        photoUrl: {
            type: String,
            default: "",
        },
    },

    data() {
        return {
            isMenuOpen: false,
            isTooltipVisible: false,
        };
    },

    computed: {
        resolvedPhotoUrl() {
            return this.photoUrl || this.$store?.state?.auth?.user?.profile_picture_base64 || "";
        },
        userInitials() {
            const parts = String(this.username || "")
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            if (parts.length >= 2) {
                return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase();
            }

            if (parts.length === 1) {
                return parts[0].slice(0, 2).toUpperCase();
            }

            return "US";
        },
    },

    mounted() {
        document.addEventListener("click", this.handleClickOutside);
        document.addEventListener("keydown", this.handleEscape);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
        document.removeEventListener("keydown", this.handleEscape);
    },

    methods: {
        toggleMenuDropdown() {
            this.$emit("toggle");
            this.isMenuOpen = !this.isMenuOpen;
            this.isTooltipVisible = false;
        },

        closeDropdown() {
            this.isMenuOpen = false;
        },

        handleClickOutside(event) {
            if (!this.$el.contains(event.target)) {
                this.closeDropdown();
            }
        },

        handleEscape(event) {
            if (event.key === "Escape") {
                this.closeDropdown();
            }
        },

        logout() {
            const token = localStorage.getItem("token");
            axios.post("/api/logout", {}, { headers: { Authorization: `Bearer ${token}` } }).then(() => {
                sessionStorage.removeItem("storedRoute");
                this.$store.dispatch("auth/setUser", null);
                localStorage.removeItem("user");
                localStorage.removeItem("token");
                this.closeDropdown();
                this.$router.push({ name: "login" });
            });
        },

        myProfile() {
            this.closeDropdown();
            this.$router.push({ name: "myProfile" });
        },

        downloadReport() {
            if (window.errorLogger) {
                window.errorLogger.downloadErrorReport();
                this.closeDropdown();
            }
        },
    },
};
</script>
