<template>
    <router-link
        :to="to"
        :title="!isNavbarOpen ? text : null"
        class="flex min-w-0 items-center gap-2.5 px-3 font-normal uppercase leading-none transition-all duration-200 transform focus:outline-none"
        :class="{
            'router-link-is-active text-primary': routeMatches,
            'bg-white text-primary hover:bg-gradient-to-bl hover:from-primary-light hover:to-primary hover:text-white': isSubRoute,
            'text-white hover:text-white hover:bg-gradient-to-bl hover:from-primary-light hover:to-primary': !isSubRoute && !routeMatches && active !== '',
        },
        [
            size !== 'mobile' && !isNavbarOpen ? 'justify-center py-3' : 'justify-start py-2.5',
        ]"
    >
        <!-- Icon wrapper -->
        <div
            class="flex size-5 shrink-0 items-center justify-center leading-none [&>svg]:block [&>svg]:size-5"
        >
            <slot />
        </div>

        <!-- Conditionally show text -->
        <span
            class="min-w-0 overflow-hidden tracking-wide leading-none"
            :class="{
                'block text-sm': isNavbarOpen && size !== 'mobile',
                'hidden': !isNavbarOpen,
                'block text-base': isNavbarOpen && size === 'mobile',
            }"
        >
            {{ text }}
        </span>

    </router-link>
</template>

<script>
import { size } from "lodash";
import { mapState, mapMutations, mapGetters } from "vuex";

export default {
    name: "NavbarLink",
    props: {
        to: {
            type: [String, Object],
            required: true,
        },
        text: {
            type: String,
            required: true,
        },
        active: {
            type: String,
            default: "",
        },
        // isOpen: {
        //     type: Boolean,
        //     default: true,
        // },
        isTablet: {
            type: Boolean,
            default: false,
        },
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

        routeMatches() {
            if (this.active === '/proyecto' && this.$route.path === '/') return true;
            const regex = new RegExp(`^${this.active}(\/|$)`);
            return this.$route.path.match(regex) !== null;
        },

        isSubRoute() {
            const subRouteRegex = new RegExp(`^${this.active}\/.+`);
            return this.$route.path.match(subRouteRegex) !== null;
        },
    },
};
</script>

<style scoped>
</style>
