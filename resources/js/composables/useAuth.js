import axios from 'axios'

export function useAuth() {
  // Obtener token de múltiples fuentes
  const getToken = () => {
    // 1. Intentar desde localStorage
    let token = localStorage.getItem('accessToken')
    
    // 2. Si no está en localStorage, intentar desde cookie
    if (!token && typeof document !== 'undefined') {
      const match = document.cookie.match(/(?:^|;\s*)accessToken=([^;]*)/)
      token = match ? decodeURIComponent(match[1]) : null
    }
    
    // 3. Intentar desde useCookie si está disponible
    if (!token) {
      try {
        const cookieToken = useCookie('accessToken')
        token = cookieToken.value
      } catch (e) {
        // Ignorar error si useCookie no está disponible
      }
    }
    
    return token
  }

  // Obtener datos del usuario desde localStorage
  const getUserData = () => {
    try {
      const userData = localStorage.getItem('userData')
      return userData ? JSON.parse(userData) : null
    } catch (e) {
      console.error('Error al parsear userData:', e)
      return null
    }
  }

  // Obtener reglas de habilidad desde localStorage
  const getUserAbilityRules = () => {
    try {
      const rules = localStorage.getItem('userAbilityRules')
      return rules ? JSON.parse(rules) : []
    } catch (e) {
      console.error('Error al parsear userAbilityRules:', e)
      return []
    }
  }

  // Guardar token y datos del usuario
  const setAuthData = (token, userData, userAbilityRules, homeRoute) => {
    // Guardar en localStorage
    localStorage.setItem('accessToken', token)
    localStorage.setItem('userData', JSON.stringify(userData))
    localStorage.setItem('userAbilityRules', JSON.stringify(userAbilityRules))
    if (homeRoute) {
      localStorage.setItem('homeRoute', homeRoute)
    }
    
    // Guardar en cookies
    try {
      useCookie('accessToken').value = token
      useCookie('userData').value = userData
      useCookie('userAbilityRules').value = userAbilityRules
    } catch (e) {
      // Fallback: guardar directamente en document.cookie
      document.cookie = `accessToken=${token}; path=/; max-age=${60 * 60 * 24 * 7}`
    }
    
    // Configurar header de axios
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  // Guardar solo token
  const setToken = (token) => {
    localStorage.setItem('accessToken', token)
    
    try {
      useCookie('accessToken').value = token
    } catch (e) {
      document.cookie = `accessToken=${token}; path=/; max-age=${60 * 60 * 24 * 7}`
    }
    
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }

  // Eliminar todos los datos del usuario
  const clearAuthData = () => {
    // Limpiar localStorage específico
    localStorage.removeItem('accessToken')
    localStorage.removeItem('userData')
    localStorage.removeItem('userAbilityRules')
    localStorage.removeItem('homeRoute')
    
    // Limpiar cualquier otra clave relacionada con auth
    const keysToRemove = []
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key && (key.startsWith('user') || key.includes('token') || key.includes('auth'))) {
        keysToRemove.push(key)
      }
    }
    keysToRemove.forEach(key => localStorage.removeItem(key))
    
    // Limpiar cookies
    try {
      useCookie('accessToken').value = null
      useCookie('userData').value = null
      useCookie('userAbilityRules').value = null
    } catch (e) {
      document.cookie = 'accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      document.cookie = 'userData=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
      document.cookie = 'userAbilityRules=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
    }
    
    // Limpiar header de axios
    delete axios.defaults.headers.common['Authorization']
  }

  // Alias para clearAuthData
  const clearToken = () => {
    clearAuthData()
  }

  // Verificar si hay token válido
  const hasToken = () => {
    return !!getToken()
  }

  // Verificar si hay datos de usuario completos
  const isAuthenticated = () => {
    return !!(getToken() && getUserData())
  }

  return {
    getToken,
    getUserData,
    getUserAbilityRules,
    setToken,
    setAuthData,
    clearToken,
    clearAuthData,
    hasToken,
    isAuthenticated,
  }
}
