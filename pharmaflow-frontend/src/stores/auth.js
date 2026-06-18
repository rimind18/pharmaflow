import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore(
  'auth',
  () => {
    // =====================
    // STATE
    // =====================

    const user = ref(null)
    const token = ref(null)
    const initialized = ref(false)

    // =====================
    // COMPUTED
    // =====================

    const isAuthenticated =
      computed(() => !!token.value)

    const role = computed(
      () => user.value?.role || null
    )

    const isCustomer = computed(
      () => role.value === 'customer'
    )

    const isStaff = computed(
      () => role.value === 'staff'
    )

    const isOwner = computed(
      () => role.value === 'owner'
    )

    // =====================
    // INITIALIZE AUTH
    // =====================

    const initializeAuth =
      async () => {
        try {
          const savedToken =
            localStorage.getItem(
              'token'
            )

          const savedUser =
            localStorage.getItem(
              'user'
            )

          if (
            savedToken &&
            savedUser
          ) {
            token.value =
              savedToken

            user.value =
              JSON.parse(
                savedUser
              )

            // verify token
            try {
              const response =
                await api.get(
                  'auth/me'
                )

              user.value =
                response.data.user

              localStorage.setItem(
                'user',
                JSON.stringify(
                  user.value
                )
              )
            } catch (err) {
              console.warn(
                'Token invalid'
              )

              clearAuth()
            }
          }
        } catch (error) {
          console.error(
            'Initialize auth error:',
            error
          )

          clearAuth()
        } finally {
          initialized.value =
            true
        }
      }

    // =====================
    // LOGIN
    // =====================

    const login = async (
      email,
      password
    ) => {
      try {
        const response =
          await api.post(
            'auth/login',
            {
              email,
              password,
            }
          )

        const data =
          response.data

        token.value =
          data.token

        user.value =
          data.user

        localStorage.setItem(
          'token',
          data.token
        )

        localStorage.setItem(
          'user',
          JSON.stringify(
            data.user
          )
        )

        return data
      } catch (error) {
        throw (
          error.response?.data ||
          error.message
        )
      }
    }

    // =====================
    // REGISTER
    // =====================

    const register = async (
      formData
    ) => {
      try {
        const response =
          await api.post(
            'auth/register',
            formData
          )

        const data =
          response.data

        token.value =
          data.token

        user.value =
          data.user

        localStorage.setItem(
          'token',
          data.token
        )

        localStorage.setItem(
          'user',
          JSON.stringify(
            data.user
          )
        )

        return data
      } catch (error) {
        throw (
          error.response?.data ||
          error.message
        )
      }
    }

    // =====================
    // LOGOUT
    // =====================

    const logout = async () => {
      try {
        if (token.value) {
          await api.post(
            'auth/logout'
          )
        }
      } catch (error) {
        console.warn(
          'Logout error:',
          error
        )
      } finally {
        clearAuth()
      }
    }

    // =====================
    // CLEAR AUTH
    // =====================

    const clearAuth =
      () => {
        token.value = null
        user.value = null

        localStorage.removeItem(
          'token'
        )

        localStorage.removeItem(
          'user'
        )
      }

    return {
      // state
      user,
      initialized,
      token,
      

      // computed
      isAuthenticated,
      role,
      isCustomer,
      isStaff,
      isOwner,

      // actions
      initializeAuth,
      login,
      register,
      logout,
      clearAuth,
    }
  }
)