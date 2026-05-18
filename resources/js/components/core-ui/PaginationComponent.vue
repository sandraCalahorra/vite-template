<template>
    <!-- PAGINATION -->
    <div class="grid lg:grid-cols-10 md:grid-cols-5 grid-cols-3 w-full justify-end px-4 sm:px-0">
        <div class="col-span-full flex items-center gap-4" v-if="paginatedArray?.data && (Array.isArray(paginatedArray?.data) ? paginatedArray.data.length > 0 : Object.keys(paginatedArray?.data).length > 0)">
            <div class="col-span-1 max-w-44 flex justify-center items-center ">
                <select v-model="per_page" @change="handlePageSizeChange" :disabled="showAll" class="p-1 max-w-48 w-fit rounded border border-gray-500">
                    <option v-for="size in pageSizes" :key="size" :value="size" class="w-auto">{{ size }}</option>
                </select>
            </div>
            <div v-if="enabledShowAll" class="flex item-center">
                <input type="checkbox" id="checkboxShowAll" v-model="showAll" @change="toggleShowAll" />
                <label for="checkboxShowAll" class="pl-2 whitespace-nowrap">Mostrar todos</label>
            </div>
            <div v-if="visiblePages.length > 1" class="col-span-full md:col-span-5 flex gap-4 order-1 md:order-2 w-full md:mb-0 justify-end md:justify-end ml-auto">
                <div class="flex gap-1 justify-end w-full">
                    <button
                        v-if="currentPage > 1"
                        type="button"
                        @click.prevent="changePage(1)"
                        class="block text-gray-900 hover:text-white hover:bg-primary-light p-2 rounded-sm outline-none"
                    >
                        <svg
                            class="size-6 fill-current"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Arrow -->
                            <path
                                d="M15.7 15.3a1 1 0 0 1-1.4 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 0 1 1.4 1.4L12.42 12l3.3 3.3z"
                            />
                            <!-- Vertical line -->
                            <path
                                d="M8.4 6.7a1 1 0 0 1 1 1v8.5a1 1 0 1 1-2 0v-8.5a1 1 0 0 1 1-1z"
                            />
                        </svg>
                    </button>

                    <button
                        v-if="currentPage > 1"
                        type="button"
                        @click.prevent="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="block text-gray-900 hover:text-white hover:bg-primary-light p-2 rounded-sm outline-none"
                    >
                        <svg
                            class="size-6 fill-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M14.7 15.3a1 1 0 0 1-1.4 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 0 1 1.4 1.4L11.42 12l3.3 3.3z"
                            />
                        </svg>
                    </button>

                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        type="button"
                        @click.prevent="changePage(page)"
                        class="block text-gray-900 hover:text-white p-2 rounded-sm outline-none"
                        :class="[
                            page == currentPage
                                ? 'block bg-primary text-white px-4 py-2 rounded-sm pointer-events-none'
                                : 'block text-gray-900 hover:text-white hover:bg-primary-light px-4 py-2 rounded-sm',
                        ]"
                    >
                        {{ page }}
                    </button>

                    <button
                        v-if="currentPage < totalPages"
                        type="button"
                        @click.prevent="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="block text-gray-900 hover:text-white hover:bg-primary-light p-2 rounded-sm outline-none"
                    >
                        <svg
                            class="size-6 fill-current"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M9.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4-1.4l3.29-3.3-3.3-3.3z"
                            />
                        </svg>
                    </button>

                    <button
                        v-if="currentPage < totalPages"
                        type="button"
                        @click.prevent="changePage(totalPages)"
                        class="block text-gray-900 hover:text-white hover:bg-primary-light p-2 rounded-sm outline-none"
                    >
                        <svg
                            class="size-6 fill-current"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Arrow -->
                            <path
                                d="M8.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4-1.4l3.29-3.3-3.3-3.3z"
                            />
                            <!-- Vertical line -->
                            <path
                                d="M15.5 6.7a1 1 0 0 1 1 1v8.5a1 1 0 1 1-2 0v-8.5a1 1 0 0 1 1-1z"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PaginationComponent",
    props: {
        paginatedArray: {
            type: [Array, Object],
            required: true,
            default: () => [],
        },
        enabledShowAll: {
            type: Boolean,
            default: false,
        },
        storagePreference: {
            type: Object,
            default: () => ({}),
        },
        nameStorageObj: {
            type: String,
        }
    },

    data() {
        const storage = this.storagePreference || {};
        return {
            internalpaginatedArray: [],
            totalPages: 1,
            currentPage: storage.page || 1,
            per_page: storage.per_page || 10,
            showAll: storage.show_all || false,
        };
    },

    watch: {
        paginatedArray(array) {
            this.internalpaginatedArray = array.data;
            this.totalPages = array.last_page;
            this.currentPage = array.current_page;
        },
        storagePreference: {
            handler(val) {
                if (val) {
                    if (val.page) this.currentPage = val.page;
                    if (val.per_page) this.per_page = val.per_page;
                    if (val.show_all !== undefined) this.showAll = val.show_all;
                }
            },
            deep: true
        }
    },

    computed: {
        visiblePages() {
            const range = 2; // Pages before and after the current
            const start = Math.max(1, this.currentPage - range);
            const end = Math.min(this.totalPages, this.currentPage + range);
            const visible = [];

            for (let i = start; i <= end; i++) {
                visible.push(i);
            }

            return visible;
        },

        pageSizes() {
            if (this.user?.level < 10)  {
                return [2, 10, 20, 50, 100, 500, 1000]
            } else {
                return [10, 20, 50, 100, 500, 1000]
            }
        },
    },

    mounted() {
        if (this.paginatedArray) {
            this.internalpaginatedArray = this.paginatedArray ?? this.internalpaginatedArray;
            this.currentPage = this.paginatedArray?.current_page ?? 1;
            this.totalPages = this.paginatedArray.last_page ?? 1;
        }
    },

    methods: {
        changePage(page) {
            if (page > 0 && page <= this.totalPages) {
                if (!this.storagePreference) return;
                if (!sessionStorage.getItem(this.nameStorageObj) && this.nameStorageObj) {
                    sessionStorage.setItem(this.nameStorageObj, JSON.stringify(this.storagePreference));
                }

                this.storagePreference.page = page;

                if (this.nameStorageObj) sessionStorage.setItem(this.nameStorageObj, JSON.stringify(this.storagePreference));

                this.$emit("update:pagination", { page: page });
            }
        },

        handlePageSizeChange() {
            if (!this.storagePreference) return;
            if (!sessionStorage.getItem(this.nameStorageObj) && this.nameStorageObj) {
                sessionStorage.setItem(this.nameStorageObj, JSON.stringify(this.storagePreference));
            }

            this.storagePreference.page = 1;
            this.storagePreference.per_page = this.per_page;

            if (this.nameStorageObj) sessionStorage.setItem(this.nameStorageObj, JSON.stringify(this.storagePreference));

            this.$emit("update:pagination", { per_page: this.per_page });
        },

        toggleShowAll() {
            if (!this.storagePreference) return;
            // not fixed
            if (this.showAll) {
                this.internalpaginatedArray = this.paginatedArray.all_data || [];

                this.storagePreference.page = 1;
                this.storagePreference.show_all = true;
                this.$emit("update:pagination", { showAll: true });
            } else {
                this.storagePreference.page = 1;
                this.storagePreference.per_page = 10;

                this.$emit("update:pagination", { showAll: this.showAll });
            }
        },
    },
};
</script>

<style scoped>
</style>
