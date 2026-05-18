<template>
    <div class="flex items-center gap-4 z-40">
        <span v-if="username" class="uppercase text-base flex flex-nowrap w-fit">{{ username }}</span>

        <div
            class="items-center inline-block cursor-pointer"
            :class="isMenuOpen ? 'hover:text-primary' : 'hover:text-primary-light'"
            @click="toggleMenuDropdown"
        >
            <IconCog6ToothSolid class="w-5" />

            <!-- <div v-if="isMenuOpen" class="w-auto absolute mt-2 right-6 bg-white hover:text-primary border-primary border rounded z-50 shadow"> -->
            <div v-if="isMenuOpen" class="w-auto absolute mt-2 right-6 bg-white hover:text-primary border rounded-lg z-50 shadow-lg">
                <span @click="myProfile" class="w-auto flex gap-2 px-3 pl-2 py-2 xl:mx-0 mx-3 outline-none items-center text-primary rounded transition-all transform duration-200 cursor-pointer hover:text-primary-light ">
                    <IconUser class="size-5" />
                    <span class="text-sm font-semibold tracking-wide">{{'Mi perfil'.toUpperCase()}}</span>
                </span>

                <span @click="logout" class="w-auto flex gap-2 px-3 pl-2 py-2 xl:mx-0 mx-3 outline-none items-center text-primary rounded transition-all transform duration-200 cursor-pointer hover:text-primary-light border-b border-primary/10">
                    <IconLogout class="size-5" />
                    <span class="text-sm font-semibold tracking-wide">{{'Cerrar sesión'.toUpperCase()}}</span>
                </span>

                <span @click="downloadReport" class="w-auto flex gap-2 px-3 pl-2 py-2 xl:mx-0 mx-3 outline-none items-center text-red-600 rounded transition-all transform duration-200 cursor-pointer hover:bg-red-50">
                    <IconBug class="w-4" />
                    <span class="text-sm font-semibold tracking-wide">{{'Descargar Reporte'.toUpperCase()}}</span>
                </span>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data() {
        return {
            isMenuOpen: false,
            expandedMessages: [],
            pollingInterval: null,
        };
    },

    props: {
        username: {
            type: String,
        },
    },

    mounted() {
        document.addEventListener("click", this.handleClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
    },

    methods: {
        toggleMenuDropdown(event) {
            event.stopPropagation();
            this.isMenuOpen = !this.isMenuOpen;
        },

        closeDropdown() {
            this.isMenuOpen = false;
        },

        handleClickOutside(event) {
            if (!this.$el.contains(event.target)) {
                this.closeDropdown();
            }
        },

        logout() {
            const token = localStorage.getItem("token");
            axios.post("/api/logout", {}, { headers: { Authorization: `Bearer ${token}` } }).then(() => {
                sessionStorage.removeItem('storedRoute');
                this.$store.dispatch("auth/setUser", null);
                localStorage.removeItem("user");
                localStorage.removeItem("token");
                this.$router.push({ name: "login" });
            });
        },

        formatRelativeDate(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diff = now - date;
            const sec = Math.floor(diff / 1000);
            const min = Math.floor(sec / 60);
            const hr = Math.floor(min / 60);
            const days = Math.floor(hr / 24);
            if (sec < 60) return `hace ${sec} segundos`;
            if (min < 60) return `hace ${min} minutos`;
            if (hr < 24) return `hace ${hr} horas`;
            if (days === 1) return "ayer";
            if (days < 7) return `hace ${days} días`;
            return new Date(dateString).toLocaleDateString("es-ES", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        myProfile() {
            this.$router.push({name: 'myProfile'});
        },

        downloadReport() {
            if (window.errorLogger) {
                window.errorLogger.downloadErrorReport();
                this.closeDropdown();
            }
        }
    },

};
</script>

<style scoped>
.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 370px;
}
</style>
