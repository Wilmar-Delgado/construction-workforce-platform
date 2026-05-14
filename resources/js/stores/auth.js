import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
    }),

    actions: {
        setUser(user) {
            console.log('Setting user in auth store:', user);
            this.user = user
        }
    },

    getters: {
        userName: (state) => state.user?.name ?? '',
        isAuthenticated: (state) => !!state.user,
    }
})