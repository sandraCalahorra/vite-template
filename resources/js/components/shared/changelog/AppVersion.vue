<template>
    <div class="text-primary-900 font-medium tracking-wide justify-center opacity-60 hover:opacity-70 mix-blend-lighten text-sm cursor-pointer" @click="checkVersion ? openModal() : ''">
        Montearagón v{{$package.version}}
    </div>

</template>

<script>
export default {
    name: "AppVersion",
    props: {
        allParams: {
            type: Object,
            default: () => ({}),
        },
    },
    computed: {
        checkVersion(){
            return this.allParams.version && this.allParams.version == this.$package.version
        },
    },
    data() {
        return {
            isModalOpen: false,
        };
    },

    methods: {
        openModal() {
            this.isModalOpen = true;
            this.$emit('openModal', true);
        },
    },
};
</script>

<style scoped>
</style>
