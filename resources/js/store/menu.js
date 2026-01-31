import axios from 'axios'
import { computed, ref } from 'vue'

export const menus = ref([])
// Bandera para evitar cargas simultáneas
let isLoadingMenus = false

const normalizeInternalPath = rawRoute => {
  let path = (rawRoute ?? '').toString().trim()
  if (!path)
    return ''

  // If someone stores "prueba" instead of "/prueba", normalize to an internal path.
  if (!path.startsWith('/'))
    path = `/${path}`

  return path
}

const navToKey = to => {
  if (!to)
    return ''

  const raw = typeof to === 'string'
    ? to
    : (to?.name ?? to?.path ?? '')

  return raw.toString().replace(/^\/+/, '')
}

const buildNavItemKey = item => {
  const routeKey = navToKey(item.to)
  const sectionKey = (item.section || routeKey || item.title || '').toLowerCase().trim()

  return { routeKey, sectionKey }
}

const filterNavItemsBySections = (items, allowedSections) => {
  return (items || [])
    .map(item => {
      if (item?.children?.length) {
        const nextChildren = filterNavItemsBySections(item.children, allowedSections)

        // Keep group if any child is visible
        if (nextChildren.length)
          return { ...item, children: nextChildren }

        // Or keep if group itself is explicitly allowed
        const { sectionKey } = buildNavItemKey(item)
        return allowedSections.includes(sectionKey) ? { ...item, children: [] } : null
      }

      const { sectionKey } = buildNavItemKey(item)
      return allowedSections.includes(sectionKey) ? item : null
    })
    .filter(Boolean)
}

// Función para filtrar menús según permisos
// NOTA: El backend YA filtra los menús según los permisos del usuario
// Por lo tanto, simplemente retornamos lo que el backend envió
export const filteredMenus = computed(() => {
  const userData = useCookie('userData').value

  // Registrar en consola para depuración
  console.debug('[Menu] Frontend menu display:', {
    menusFromBackend: menus.value.length,
    userData: userData?.email,
  })

  // El backend ya filtró los menús según los permisos del usuario
  // Retornamos directamente lo que el backend envió
  return menus.value
})

export async function fetchMenus() {
  // Si ya se están cargando, esperar a que terminen
  if (isLoadingMenus) {
    console.debug('[Menu] Ya hay una carga en progreso, esperando...')
    // Esperar hasta que termine la carga actual
    while (isLoadingMenus) {
      await new Promise(resolve => setTimeout(resolve, 100))
    }
    console.debug('[Menu] Carga completada por otra llamada')
    return
  }

  // Si ya hay menús cargados, no volver a cargar
  if (menus.value.length > 0) {
    console.debug('[Menu] Menús ya cargados, saltando petición')
    return
  }

  isLoadingMenus = true
  try {
    console.debug('[Menu] Fetching menus from API...')

    // El interceptor de Axios ya se encarga de enviar el token automáticamente
    const response = await axios.get('/api/menus')

    menus.value = response.data.map(menu => {
      // Normalizar la sección del menú principal
      const menuRoute = menu.route || ''
      const menuPath = normalizeInternalPath(menuRoute)
      const menuRouteKey = menuPath.replace(/^\/+/, '')
      const menuSection = (menu.section || menuRouteKey || menu.name || '').toString().toLowerCase().trim()

      return {
        title: menu.name,
        icon: menu.icon ? { icon: menu.icon } : undefined,

        // `@layouts/utils` treats string `to` as route-name. Use `{ path }` for dynamic menus.
        to: menuPath ? { path: menuPath } : undefined,
        section: menuSection,

        // Importante: NO establecer action/subject en grupos para que canViewNavMenuGroup
        // se base solo en la visibilidad de los hijos.
        children: menu.submenus?.map(sub => {
          // Normalizar la sección de los submenús
          const subRoute = sub.route || ''
          const subPath = normalizeInternalPath(subRoute)
          const subRouteKey = subPath.replace(/^\/+/, '')
          const subSection = (sub.section || subRouteKey || sub.name || '').toString().toLowerCase().trim()

          const hasThirdLevel = Array.isArray(sub.subsubmenus) && sub.subsubmenus.length > 0

          // If it has children, it must be a group
          if (hasThirdLevel) {
            return {
              title: sub.name,
              icon: sub.icon ? { icon: sub.icon } : undefined,
              section: subSection,
              children: sub.subsubmenus
                .filter(s3 => s3 && s3.is_active !== false)
                .map(s3 => {
                  const s3Route = s3.route || ''
                  const s3Path = normalizeInternalPath(s3Route)
                  const s3RouteKey = s3Path.replace(/^\/+/, '')
                  const s3Section = (s3.section || s3RouteKey || s3.name || '').toString().toLowerCase().trim()

                  return {
                    title: s3.name,
                    icon: s3.icon ? { icon: s3.icon } : undefined,
                    to: s3Path ? { path: s3Path } : undefined,
                    section: s3Section,
                    action: 'read',
                    subject: s3Section,
                  }
                }),
            }
          }

          // Otherwise it's a normal link
          return {
            title: sub.name,
            icon: sub.icon ? { icon: sub.icon } : undefined,
            to: subPath ? { path: subPath } : undefined,
            section: subSection,
            action: 'read',
            subject: subSection,
          }
        }) || [],
      }
    })

    console.debug('[Menu] Menus fetched:', menus.value)
  } catch (error) {
    console.error('[Menu] Error fetching menus:', error)
    console.error('[Menu] Error details:', error.response?.data || error.message)
    throw error // Re-lanzar el error para que el llamador lo maneje
  } finally {
    isLoadingMenus = false
  }
}
