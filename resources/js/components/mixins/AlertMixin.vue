<script>
export default {
  data() {
    return {
      localAlert: {
        color: "",
        title: "",
        description: ""
      }
    };
  },
  methods: {
    openAlert(type, title, description = null) {
      if (type == "success") {
        this.localAlert = {
          color: "green",
          title: title,
          description: description
        };
      } else if (type == "error") {
        this.localAlert = {
          color: "red",
          title: title,
          description: description
        };
      } else if (type == "info") {
        this.localAlert = {
          color: "blue",
          title: title,
          description: description
        };
      }
      this.$store.commit("OPEN_ALERT", this.localAlert);
    },
    /**
     *  Returns useful DEV message from laravel.
     */
    showDevError(error) {
      this.openAlert(
        "error",
        "Mensaje del servidor",
        error.response.data["message"]
      );
    },
    /**
     *  Returns a predefined message as error.
     */
    showFriendlyError() {
      this.openAlert(
        "error",
        "Se ha producido un error",
        "Vuelva a intentarlo más tarde."
      );
    }
  }
};
</script>
