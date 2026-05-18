// import Vue from "vue";
import { createStore } from "vuex";

import auth from "./modules/auth";

export default createStore({
    modules: {
        auth,
    },
    state: {
        isAlertOpen: false,
        alert: {
            color: "",
            title: "",
            description: null,
        },
        isNavbarOpen: false,
        size: "mobile",
    },
    mutations: {
        OPEN_ALERT(state, alert) {
            state.alert = alert;
            state.isAlertOpen = true;
        },
        CLOSE_ALERT(state) {
            state.isAlertOpen = false;
        },
        TOGGLE_NAVBAR_STATE(state) {
            state.isNavbarOpen = !state.isNavbarOpen;
            sessionStorage.setItem("isNavbarOpen", state.isNavbarOpen);
        },
        OPEN_NAVBAR(state) {
            state.isNavbarOpen = true;
            sessionStorage.setItem("isNavbarOpen", true);
        },
        CLOSE_NAVBAR(state) {
            state.isNavbarOpen = false;
            sessionStorage.setItem("isNavbarOpen", false);
        },
        SET_SIZE(state, value) {
            state.size = value;
        },
    },
    getters: {
        isAlertOpen: (state) => state.isAlertOpen,
        isNavbarOpen: (state) => (state.isNavbarOpen ? true : false),
        size: (state) => state.size,
    },
});
