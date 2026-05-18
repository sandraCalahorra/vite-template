<template>
    <div class="bg-gray-100 border-b-2 border-gray-300 md:px-8 px-4 py-1">
        <div
            class="min-h-11 flex items-center gap-2"
            :class="title ? 'justify-between' : 'justify-end'"
        >
            <div
                v-if="title"
                class="font-semibold text-primary text-lg flex gap-3 items-center"
            >
                <IconBack
                    class="w-5 cursor-pointer"
                    v-if="$route.path.split('/').length > 2"
                    @click="goBack"
                />
                <span v-html="title" class="uppercase text-base" />
            </div>

            <div class="font-semibold text-primary flex items-center">
                <DropdownUserMenu :username="user?.name" />
            </div>
        </div>
    </div>
</template>

<script>

export default {
    components: {
    },
    props: {
        title: {
            type: String,
            // required: true
        },
        modelValue: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue', 'search-started', 'search-ended'],
    mounted() {},
    methods: {

        goBack() {
            let pathSegments = this.$route.path.split('/');
            pathSegments.pop();
            let parentPath = pathSegments.join('/');

            if (pathSegments[1] === 'proyecto') {
                this.$router.push({name: 'projects'});
            } else if (this.$router.getRoutes().find(r => r.path == parentPath)) {
                this.$router.push(parentPath);
            } else {
                this.$router.go(-1);
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
    }
    }
}
</script>
