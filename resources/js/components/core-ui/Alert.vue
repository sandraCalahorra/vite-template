<template>
  <transition
    mode="out-in"
    enter-active-class="transition-all duration-500 ease-in"
    leave-active-class="transition-all duration-500 ease-in"
    enter-class="opacity-0"
    enter-to-class="opacity-100"
    leave-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed bottom-0 right-0 z-50 mr-6 pl-5 pr-10 py-4 border rounded cursor-pointer shadow-xl"
      :class="{
        'bg-green-100 text-green-900 border-green-700': color == 'green',
        'bg-red-100 text-red-900 border-red-700': color == 'red',
        'bg-blue-100 text-blue-900 border-blue-700': color == 'blue',
        'mb-20': formRoutes.includes(this.$route.name),
        'mb-6': !formRoutes.includes(this.$route.name)
      }"
      role="alert"
      @click="closeAlert"
    >
      <div class="flex">
        <div>
          <svg
            v-if="color == 'green'"
            class="size-5 fill-current mr-3"
            :class="`text-green-800`"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
          >
            <path d="M12 24a12 12 0 110-24 12 12 0 010 24zm0-2.4a9.6 9.6 0 100-19.2 9.6 9.6 0 000 19.2zM9.24 11.16l1.56 1.548 3.96-3.96a1.2 1.2 0 011.68 1.704l-4.8 4.8a1.2 1.2 0 01-1.68 0l-2.4-2.4a1.2 1.2 0 011.68-1.704v.012z" />
          </svg>
          <svg
            v-if="color == 'red'"
            class="size-5 fill-current mr-3"
            :class="`text-red-800`"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
          >
            <path
              d="M3.663 20.631A12 12 0 1120.337 3.37 12 12 0 013.663 20.63zm1.692-1.692A9.606 9.606 0 1018.94 5.355 9.606 9.606 0 005.355 18.939zm8.485-6.791l1.704 1.691a1.206 1.206 0 01-1.705 1.704l-1.691-1.715-1.692 1.704a1.208 1.208 0 01-1.314.26 1.207 1.207 0 01-.39-1.964l1.715-1.68-1.704-1.692a1.206 1.206 0 011.704-1.705l1.68 1.716 1.692-1.704a1.204 1.204 0 111.704 1.704l-1.715 1.68h.011z"
            />
          </svg>
          <svg
            v-if="color == 'blue'"
            class="size-5 fill-current mr-3"
            :class="`text-blue-800`"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
          >
            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
          </svg>
        </div>
        <div>
          <p class="font-bold leading-5">
            {{ title }}
          </p>

          <div class="text-sm" v-if="isHtmlContent(description)" v-html="description"></div>

          <p v-else class="text-sm">
            {{ description }}
          </p>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "Alert",
  props: {
    color: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ""
    },
    open: {
      type: Boolean
    }
  },
  data() {
    return {
        formRoutes: [
            'announcementsForm',
            'announcementsCreate',
            'customersForm',
            'customersCreate',
            'devicesForm',
            'devicesCreate',
            'myProfile',
        ],
    }
  },
  emits: ['close'],
  updated() {
    if (this.open) {
      this.timeout = setTimeout(() => {
        this.closeAlert();
      }, 5000);
    }
  },
  methods: {
    isHtmlContent(val) {
      const htmlRegex = /<[^>]*>/;
      return htmlRegex.test(val);
    },
    closeAlert() {
      this.$emit("close");
    }
  }
};
</script>
