import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

export const useUserStore = defineStore('user', () => {
    // State
    const userDataCookie = useCookie('userData')
    const userData = ref(userDataCookie.value || null)

    // Getters
    const avatar = computed(() => userData.value?.avatar || userData.value?.avatar_url || '')
    const name = computed(() => userData.value?.fullName || userData.value?.name || userData.value?.username || '')
    const role = computed(() => userData.value?.role || 'Usuario')
    const email = computed(() => userData.value?.email || '')

    // Actions
    function updateUserData(newData) {
        userData.value = { ...userData.value, ...newData }
        userDataCookie.value = userData.value
    }

    function updateAvatar(avatarUrl) {
        if (userData.value) {
            userData.value = {
                ...userData.value,
                avatar: avatarUrl,
                avatar_url: avatarUrl,
            }
            userDataCookie.value = userData.value
        }
    }

    function setUserData(data) {
        userData.value = data
        userDataCookie.value = data
    }

    function clearUserData() {
        userData.value = null
        userDataCookie.value = null
    }

    // Sincronizar con la cookie cuando cambie
    watch(userDataCookie, (newValue) => {
        if (newValue && JSON.stringify(newValue) !== JSON.stringify(userData.value)) {
            userData.value = newValue
        }
    }, { deep: true })

    return {
        userData,
        avatar,
        name,
        role,
        email,
        updateUserData,
        updateAvatar,
        setUserData,
        clearUserData,
    }
})
