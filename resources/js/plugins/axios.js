import axios from 'axios'

let isAxiosConfigured = false

// Helper para obtener el token
const getToken = () => {
  // 1. Intentar desde localStorage
  let token = null
  if (typeof window !== 'undefined' && window.localStorage) {
    token = localStorage.getItem('accessToken')
  }
  
  // 2. Si no está en localStorage, intentar desde cookie
  if (!token && typeof document !== 'undefined') {
    const match = document.cookie.match(/(?:^|;\s*)accessToken=([^;]*)/)
    token = match ? decodeURIComponent(match[1]) : null
  }
  
  return token
}

// Plugin para configurar axios de forma segura sin ejecutar código en import-time
export default function () {
  // Evitar registrar interceptores múltiples (p.ej. HMR)
  if (isAxiosConfigured)
    return

  isAxiosConfigured = true

  // Configurar Axios para enviar cookies en cada petición
  axios.defaults.withCredentials = true
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
  
  // Interceptor para agregar el token de autorización en cada petición
  axios.interceptors.request.use(
    async config => {
      let token = getToken()
      
      // Si no hay token, intentar obtenerlo después de esperar
      // (útil cuando la página se recarga y el token aún no está disponible)
      if (!token) {
        // Intentar múltiples veces con delay entre intentos
        for (let i = 0; i < 10; i++) {
          await new Promise(resolve => setTimeout(resolve, 50))
          token = getToken()
          if (token) break
        }
      }
      
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      } else {
        console.warn('⚠️ Petición sin token de autenticación:', config.url)
      }
      
      return config
    },
    error => {
      return Promise.reject(error)
    }
  )
  
  // Interceptor para manejar errores de autenticación
  axios.interceptors.response.use(
    response => response,
    error => {
      if (error.response?.status === 401) {
        // Token expirado o inválido
        if (typeof window !== 'undefined') {
          // Limpiar TODO el localStorage relacionado con autenticación
          if (window.localStorage) {
            localStorage.removeItem('accessToken')
            localStorage.removeItem('userData')
            localStorage.removeItem('userAbilityRules')
            localStorage.removeItem('homeRoute')
            
            // Limpiar cualquier otra clave relacionada
            const keysToRemove = []
            for (let i = 0; i < localStorage.length; i++) {
              const key = localStorage.key(i)
              if (key && (key.startsWith('user') || key.includes('token') || key.includes('auth'))) {
                keysToRemove.push(key)
              }
            }
            keysToRemove.forEach(key => localStorage.removeItem(key))
          }
          
          // Limpiar cookies
          document.cookie = 'accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
          document.cookie = 'userData=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
          document.cookie = 'userAbilityRules=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
          
          // Redirigir al login solo si no estamos ya en login
          if (!window.location.pathname.includes('/login')) {
            console.warn('🚫 Sesión expirada o token inválido - Redirigiendo al login')
            window.location.href = '/login'
          }
        }
      }
      
      return Promise.reject(error)
    }
  )
}
