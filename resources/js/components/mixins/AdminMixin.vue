<script>
export default {
  data() {

    return {
      fetchUrl: "",
      fetchParams: {},
      newItem: {},
      selectedItems: [],
      isEditing: false,
      isModalOpen: false,
      isFilterModalOpen: false,
      isDeleteModalOpen: false,
      isIconsModalOpen: false,
      isNotificationModalOpen: false,
      nif:[],
      arrayTables:[],
      isLoading: true,
      alreadyExistNif:false,
      submitting:false,
    };
  },

  methods: {
    /**
     *  @var addUrl. API Url to store an element into the database.
     *  @var editUrl. API Url to update the current element.
     *  @var callback. values: [function, "default", false]
     *
     *  In case we send a FUNCTION, it will execute it with the data returned from the previous "axiosRequest".
     *  In case we send "default", it will execute the default function which fetches all the paginated data with filters.
     *  In case we send NOTHING or FALSE, nothing will execute (no side-effects).
     */
    storeOrUpdateItem(addUrl, editUrl, callback = false) {
      let message = "";
      let axiosRequest = {};

      if (this.isEditing) {
        message = "Registro actualizado con éxito.";
        axiosRequest = this.$axios.put(editUrl,this.newItem);
      } else {
        message = "Registro añadido con éxito.";
        axiosRequest = this.$axios.post(addUrl,this.newItem);
      }

      axiosRequest
        .then(response => {
          if(response.data!=""){
            sessionStorage.setItem("id",response.data?.id);
          }
          if (callback && callback !== "default") {
            callback(response.data);
          } else if (callback === "default") {
            this.fetchPaginatedData(this.fetchUrl, this.fetchParams);
          }

          // Item is being edited after its creation.
          this.isEditing = true;
          this.isModalOpen = false;
          this.openAlert("success", message, null);

        })
        .catch((error) => {
          this.showDevError(error);
        });
    },

    storeOrUpdateObject(addUrl, editUrl, object, callback = false) {
      let message = "";
      let axiosRequest = {};

      if (this.isEditing) {
        message = "Registro actualizado con éxito.";
        axiosRequest = this.$axios.put(editUrl,object);
      } else {
        message = "Registro añadido con éxito.";
        axiosRequest = this.$axios.post(addUrl,object);
      }

      axiosRequest
        .then(response => {
          if (callback && callback !== "default") {
            callback(response.data);
          } else if (callback === "default") {
            this.fetchPaginatedData(this.fetchUrl, this.fetchParams);
          }

          // Item is being edited after its creation.
          this.isEditing = true;
          this.isModalOpen = false;
          this.openAlert("success", message, null);

        })
        .catch((error) => {
          this.showDevError(error);
        });
    },

    removeItem(url, object_id = false) {
      this.$axios
        .delete(url, {
          params: {
            id: object_id ? object_id : this.newItem.id
          }
        })
        .then(() => {
          // Fetch all the Data after the item has been removed.
          this.fetchPaginatedData(this.fetchUrl, this.fetchParams);
          this.openAlert("success", "Registro eliminado con éxito.", null);
          this.isDeleteModalOpen = false;
        })
        .catch((error) => {
          this.showDevError(error);
        });
    },

    openCreateModal() {
      this.newItem = {};
      this.isEditing = false;
      this.isModalOpen = true;
    },

    openFilterModal() {
      this.isFilterModalOpen = true;
    },

    openIconsModal() {
      this.isIconsModalOpen = true;
    },

    openNotificationsModal() {
      this.isNotificationModalOpen = true;
    },

    openEditModal(item) {
      this.newItem = { ...item };
      this.isEditing = true;
      this.isModalOpen = true;
    },

    openRemoveModal(item) {
      this.newItem = { ...item };
      this.isDeleteModalOpen = true;
    },

    /**
     *  This debounce function won't be executed until the previous execution of that method has stopped.
     */
    debounce( callback, delay ) {
      let timeout

      return function(...args) {
        const effect = () => {
          timeout = null
          return callback.apply(this, args)
        }

        clearTimeout(timeout)
        timeout = setTimeout(effect, delay)
      }
    },
        /** Used to maintain between table views and form views
         * or
         * delete upon exiting them
         * with the localStorage associated with pagination
         *
         * @param {string} storagePreference - The key in localStorage to store or remove data related to pagination or view state
         * @param {string} routePath - The base route path for the current view, e.g., '/usuarios' or '/hitos'
         * @param {boolean} [exception=false] - If true, applies special handling for routes like projects (optional, default is false)
        */
        beforeRouteLeaving(to, from, next, nameStoragePreference = null, storagePreference, routePath, exception = false) {
            if (!nameStoragePreference || typeof nameStoragePreference !== 'string' || nameStoragePreference.trim() === '') {
                next();
                return;
            }

            const baseTo = this.getBasePath(to.fullPath);
            const baseFrom = this.getBasePath(from.fullPath);

            const fromIsRoot = from.fullPath === '/' || from.fullPath === '';
            const toIsProyecto = to.fullPath.startsWith('/proyecto');

            const shouldKeep = (baseTo === baseFrom) || (fromIsRoot && toIsProyecto);


            if (shouldKeep) {
                sessionStorage.setItem(nameStoragePreference, JSON.stringify(storagePreference));
            } else {
                sessionStorage.removeItem(nameStoragePreference);

                const keysToRemove = [];
                for (let i = 0; i < sessionStorage.length; i++) {
                    const key = sessionStorage.key(i);
                    if (key && (key.includes('StorageObj') || key.includes('undefined'))) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => sessionStorage.removeItem(key));
            }

            next();
        },

        getBasePath(path) {
            const regex = /^\/[a-zA-Z0-9_-]+/;

            if (typeof path !== 'string') return '';

            const match = path.match(regex);
            return match ? match[0] : path;
        },
  }
};
</script>
