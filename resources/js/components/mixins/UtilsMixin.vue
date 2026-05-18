<script>
import { mapState } from 'vuex';
export default {
  computed: {
    ...mapState("auth", ["user"])
  },
  methods: {
    /**
     *  Formats the date passed as parameter to "DD/MM/YYYY" string format.
     */
    moment(value) {
      return this.$moment(value).format("DD/MM/YYYY");
    },
    /**
     *  Formats the date passed as parameter to "DD/MM/YYYY HH:mm:ss" string format.
     */
    getDatetime(value) {
      return this.$moment(value, "YYYY-MM-DDTHH:mm:ss[Z]").format("DD/MM/YYYY HH:mm");
    },
    /**
     *  Returns a default value if the oldValue is undefined
     */
    aux(oldValue, newValue) {
      if (oldValue === undefined) return newValue;
      else return oldValue;
    },

    normalizeID(id) {
      const normalizedID = id.toUpperCase().replace(/[ .-]/g, '');

      // Pad with leading zeros if shorter than 9 characters
      if (normalizedID.charAt(0) == 'X' || normalizedID.charAt(0) == 'Y' || normalizedID.charAt(0) == 'Z') {
        return normalizedID.length < 9 ? normalizedID.charAt(0) + ('0'.repeat(9 - normalizedID.length)) + normalizedID.substr(1, normalizedID.length) : normalizedID;
      } else {
        return normalizedID.length < 9 ? '0'.repeat(9 - normalizedID.length) + normalizedID : normalizedID;
      }
    },

    validateDNI(dni) {
      const dniRegex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/;

      if (!dniRegex.test(dni)) {
        return false;
      }

      const dniLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
      const letterIndex = parseInt(dni.slice(0, -1), 10) % 23;
      const expectedLetter = dniLetters.charAt(letterIndex);

      return dni.charAt(dni.length - 1) === expectedLetter;
    },

    validateNIE(nie) {
      const nieRegex = /^[XYZ][0-9]{7,8}[TRWAGMYFPDXBNJZSQVHLCKE]$/;

      if (nie.length != 9 || !nieRegex.test(nie)) {
        return false;
      }

      const nieLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
      const letterIndex = parseInt(nie.replace('X', '0').replace('Y', '1').replace('Z', '2').slice(0, -1), 10) % 23;
      const expectedLetter = nieLetters.charAt(letterIndex);

      return nie.charAt(nie.length - 1) === expectedLetter;
    },

    processIDs(inputArray) {
      const resultArray = [];

      inputArray.forEach((id) => {
        let normalizedID;
        if(id!=undefined){
          normalizedID = this.normalizeID(id.toString());

          if (this.validateDNI(normalizedID) || this.validateNIE(normalizedID)) {
            if (!resultArray.includes(normalizedID)) {
              // Add to resultArray if not already present
              resultArray.push(normalizedID);
            }
          }
        }
      });
      return resultArray;
    },

    changeBackground(array, value, data) {
        let aux = [];

        array.forEach(element => {
            if(Array.isArray(value)) {
                value.forEach(val => {
                    if (element?.[val]?.search(data?.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')) !== -1) {
                        aux.push(element[val]);
                    }
                })
            } else {
                if (element?.[value]?.search(data?.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')) !== -1) {
                    aux.push(element[value]);
                }
            }
        });
        this.isEmpty = aux.length == 0;
    },

    handlePermissionsInStore(newPermissions) {
        if (newPermissions) {
            const permission = typeof newPermissions === "string" ? JSON.parse(newPermissions) : newPermissions;
            this.$store.dispatch("auth/setPermission", permission);
        }
    },

    darkenColor(color, percent) {
        color = color ?? '#ff4f4f';
        const num = parseInt(color.slice(1), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;

        return `#${(0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1).toUpperCase()}`;
    },
  }
};
</script>
