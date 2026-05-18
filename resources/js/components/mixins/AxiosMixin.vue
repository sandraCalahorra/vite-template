<script>
export default {
    methods: {
        /**
         * Simple GET Request without parameters.
         */
        /**
         * Simple GET Request without parameters.
         */
        async fetchAllData(url) {
            const response = await this.$axios.get(url);

            return response.data || [];
        },
        /**
         * Simple GET Request with parameters.
         */
        async fetchAllGetData(url, params) {
            const response = await this.$axios.get(url, {
                params: params
            });

            return response.data;
        },
        /**
         * Simple POST Request with parameters.
         */
        async fetchAllPostData(url, params) {
            const response = await this.$axios.post(url, params);

            return response.data;
        },
        /**
         * Returns the paginated data from the url with optional parameters.
         */
        async fetchPaginatedData(url, params, resetPage = false) {
            this.isLoading = true;
            // Pagination variables.
            let current_page = resetPage ? 1 : params.page;
            //   let current_page = resetPage ? 1 : this.pagination.current_page;
            let pageNum = current_page ? current_page : 1;

            // Set the default fetch url
            if (url != undefined) {
                this.fetchUrl = url;
            }

            this.fetchParams = this.fetchParams || {};

            // Append the parameters if they exist.
            if (params != null) {
                this.fetchParams = params;
            }

            // Include the current page to the params object.
            this.fetchParams.page = pageNum;

            await this.$axios.get(this.fetchUrl, {
                params: this.fetchParams
            })
            .then((response) => {
                this.pagination = response.data || {};
            });

            if (this.pagination && this.pagination.data) {
                if (Array.isArray(this.pagination.data)) {
                    this.data = this.pagination.data;
                } else {
                    this.data = Object.values(this.pagination.data);
                }
            } else {
                this.data = [];
            }

            this.isLoading = false;
        }
    }
};
</script>
