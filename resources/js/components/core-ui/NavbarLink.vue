<template>
    <router-link
        :to="to"
        :title="!isNavbarOpen ? text : null"
        class="flex items-center min-w-0 gap-2 font-normal uppercase transition-all duration-200 transform focus:outline-none p-3 "
        :class="[{
            'router-link-is-active text-primary': routeMatches,
            'bg-white text-primary hover:bg-primary-300 hover:text-white': isSubRoute,
            'bg-white hover:text-primary': !isSubRoute && active !== '' && active === $route.path || ($route.path === '/' && active === '/'),
            'text-white hover:text-white hover:bg-primary-300': !isSubRoute && active !== '' && active !== $route.path && !($route.path === '/' && active === '/'),
        },
        size !== 'mobile' && !isNavbarOpen ? 'justify-center py-3' : 'justify-start',
        size != 'mobile' && isNavbarOpen ? 'py-2' : '']"
    >
        <!-- Icon wrapper -->
        <div
            class="flex items-center justify-center shrink-0"
            :class="{
                'size-6' : size == 'mobile' || !isNavbarOpen,
                'size-5 min-w-5 xl:size-4 xl:min-w-4' : size != 'mobile' && isNavbarOpen
            }"
        >
            <slot />
        </div>

        <!-- Conditionally show text -->
        <span
            class="tracking-wide overflow-hidden text-base xl:text-sm"
            :class="{
                'text-sm': isNavbarOpen && size !== 'mobile',
                'hidden': !isNavbarOpen,
                'text-base': isNavbarOpen && size === 'mobile',
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
