import router from '../../router';
import packageInfo from '../../../../package.json';

export default {
  namespaced: true,
  state: {
    user: localStorage.getItem('user') || null,
    permissions: localStorage.getItem('permissions') || [],
    version: localStorage.getItem('version') || null,
  },
  actions: {
    setUser({ commit }, user) {
      localStorage.setItem('user', JSON.stringify(user))
      commit("SET_USER", user);
    },
    setPermission({ commit }, permissions) {
        localStorage.setItem('permissions', JSON.stringify(permissions));
        commit("SET_PERMISSION", permissions); // update permissions
    },
    setVersion({ commit }, version) {
        localStorage.setItem('version', JSON.stringify(version));
        commit("SET_VERSION", version); // update version

        // const user = JSON.parse(localStorage.getItem('user'));
        // user.version = version;

        // commit("SET_USER", user); // update both user and its current version
    },
    autoLogin({ commit }) {
      let user = JSON.parse(localStorage.getItem('user'))
      commit("SET_USER", user || null)
      if (!user) {
        router.push({ name: 'login' })
      }
    },
    logout({ commit }) {
      commit("SET_USER", null)
      localStorage.removeItem("user")
      router.push({ name: 'login' })
    }
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user;
    },
    SET_PERMISSION(state, permissions) {
        state.permissions = permissions;
    },
    SET_VERSION(state, version) {
        state.version = version;
    },
  },
  getters: {
    user: (state) => state.user,
    permissions: (state) => state.permissions,
    version: (state) => state.version,
    hasPermission: (state) => (entity, action) => {
        // Bypass if user level is 0
        if (state.user && state.user.level == 0) {
            return true;
        }

        /**
         * Bypass permission check when version mismatch is detected.
         *
         * Returning `true` here temporarily grants access (bypassing `hasPermission`)
         * until the client reloads and the versions are synchronized.
         */
        if (state.version != packageInfo.version) {
            return true; // Temporary permission bypass until version syncs
        }

        return state.permissions[entity]?.includes(action) || false;
    }
  }
};
