<script setup>
import AddEditRoleDialog from '@/components/dialogs/AddEditRoleDialog.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Define metadata para la página
definePage({
  meta: {
    section: 'apps-roles',
    action: 'read',
    subject: 'apps-roles',
  },
})

const roles = ref([])
const permissionsByMenu = ref([]) // Permisos agrupados por menú
const selectedRoleId = ref(null)
const rolePermissionSet = ref(new Set())
const roleTuInicio = ref('') // Vista inicial del rol (campo tu_inicio en BD)
const availableRoutes = ref([]) // Rutas disponibles desde menús

// Estados para gestión de roles
const isAddRoleDialogVisible = ref(false)
const isEditRoleDialogVisible = ref(false)
const editingRole = ref(null)

// Diálogo de permisos por rol
const isPermissionsDialogOpen = ref(false)
const roleForPermissions = ref(null) // objeto rol seleccionado

//  Paneles expandidos por defecto
const expandedMenus = ref([])

const selectedRole = computed(() => roles.value.find(r => r.id === selectedRoleId.value))

const loadRoles = async () => {
  const { data } = await axios.get('/api/roles')

  roles.value = data
  if (!selectedRoleId.value && roles.value.length)
    selectedRoleId.value = roles.value[0].id
}

const loadPermissions = async () => {
  const { data } = await axios.get('/api/permissions/from-menus')

  permissionsByMenu.value = data
}

const loadAvailableRoutes = async () => {
  try {
    const { data } = await axios.get('/api/menus')

    // Crear lista de rutas desde los menús y submenús
    const routes = []
    
    data.forEach(menu => {
      // Agregar el menú principal si tiene ruta
      if (menu.route) {
        routes.push({ 
          title: menu.name, 
          value: menu.route, 
        })
      }
      
      // Agregar submenús si existen
      if (menu.submenus && menu.submenus.length > 0) {
        menu.submenus.forEach(submenu => {
          if (submenu.route) {
            routes.push({ 
              title: `${menu.name} - ${submenu.name}`, 
              value: submenu.route, 
            })
          }
        })
      }
    })
    
    availableRoutes.value = routes
    console.log('Rutas cargadas:', routes) // Debug
  } catch (error) {
    console.error('Error cargando rutas:', error)

    // Fallback a rutas por defecto si falla
    availableRoutes.value = [
      { title: 'Dashboard', value: '/dashboard-inicio' },
      { title: 'Perfil', value: '/perfil' },
    ]
  }
}

const loadRolePermissions = async roleId => {
  if (!roleId) return
  const { data } = await axios.get(`/api/roles/${roleId}/permissions`)

  rolePermissionSet.value = new Set(data.permissions)
}

const togglePermission = permId => {
  if (rolePermissionSet.value.has(permId))
    rolePermissionSet.value.delete(permId)
  else
    rolePermissionSet.value.add(permId)
}

const savePermissions = async () => {
  await axios.put(`/api/roles/${selectedRoleId.value}/permissions`, { 
    permission_ids: Array.from(rolePermissionSet.value),
    tu_inicio: roleTuInicio.value,
  })
  await loadRoles() // Recargar roles para actualizar
}

// Abrir/cerrar diálogo de permisos de un rol
const openPermissionsDialog = async role => {
  roleForPermissions.value = role
  selectedRoleId.value = role.id
  roleTuInicio.value = role.tu_inicio || '/dashboard-inicio' // Cargar vista inicial
  // Asegurar permisos cargados y permisos del rol
  if (!permissionsByMenu.value.length) await loadPermissions()
  await loadRolePermissions(role.id)
  
  // Si el rol es "admin", activar TODOS los permisos automáticamente
  if (role.nombre?.toLowerCase() === 'admin') {
    // Recolectar todos los IDs de permisos disponibles
    const allPermissionIds = permissionsByMenu.value.flatMap(menu => 
      menu.permissions.map(perm => perm.id)
    )
    // Activar todos los permisos
    rolePermissionSet.value = new Set(allPermissionIds)
  }
  
  // Expandir todos los menús
  expandedMenus.value = permissionsByMenu.value.map((_, index) => index)
  isPermissionsDialogOpen.value = true
}

const closePermissionsDialog = () => {
  isPermissionsDialogOpen.value = false
  roleForPermissions.value = null
}

// Funciones para gestión de roles
const editRole = role => {
  editingRole.value = { ...role }
  isEditRoleDialogVisible.value = true
}

const deleteRole = async roleId => {
  if (confirm('¿Estás seguro de eliminar este rol?')) {
    await axios.delete(`/api/roles/${roleId}`)
    await loadRoles()
    if (selectedRoleId.value === roleId) {
      selectedRoleId.value = roles.value[0]?.id || null
      if (selectedRoleId.value) {
        await loadRolePermissions(selectedRoleId.value)
      }
    }
  }
}

const onRoleCreated = () => {
  loadRoles()
  isAddRoleDialogVisible.value = false
}

const onRoleUpdated = () => {
  loadRoles()
  isEditRoleDialogVisible.value = false
  editingRole.value = null
}

// Helper para iconos según tipo
const getPermissionIcon = (type) => {
  switch (type) {
    case 'menu': return 'tabler-layout-dashboard'
    case 'submenu': return 'tabler-point'
    case 'subsubmenu': return 'tabler-circle-dot'
    default: return 'tabler-point'
  }
}

// Helper para colores según tipo
const getPermissionColor = (type) => {
  switch (type) {
    case 'menu': return 'primary'
    case 'submenu': return 'secondary'
    case 'subsubmenu': return 'info'
    default: return 'default'
  }
}

onMounted(async () => {
  // Función para esperar a que el token esté disponible
  const waitForToken = () => {
    return new Promise((resolve) => {
      const checkToken = () => {
        const token = localStorage.getItem('accessToken') || 
                      (document.cookie.match(/(?:^|;\s*)accessToken=([^;]*)/) || [])[1]
        return !!token
      }

      // Si el token ya existe, resolver inmediatamente
      if (checkToken()) {
        resolve(true)
        return
      }

      // Si no existe, verificar cada 50ms hasta 2 segundos máximo
      let attempts = 0
      const maxAttempts = 40 // 40 * 50ms = 2000ms
      
      const interval = setInterval(() => {
        attempts++
        if (checkToken()) {
          clearInterval(interval)
          resolve(true)
        } else if (attempts >= maxAttempts) {
          clearInterval(interval)
          console.warn('⚠️ Token no disponible después de 2 segundos')
          resolve(false)
        }
      }, 50)
    })
  }

  // Esperar a que el token esté disponible antes de hacer peticiones
  await waitForToken()
  
  await Promise.all([loadRoles(), loadPermissions(), loadAvailableRoutes()])
  await loadRolePermissions(selectedRoleId.value)
})
</script>

<template>
  <div class="pa-6">
    <!-- Header con botón para crear rol -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h4 class="text-h4 mb-2">
          Gestión de Roles
        </h4>
        <p class="text-body-1">
          Administra roles y asigna permisos por secciones
        </p>
      </div>
      <VBtn 
        color="primary" 
        prepend-icon="tabler-plus"
        @click="isAddRoleDialogVisible = true"
      >
        Crear Rol
      </VBtn>
    </div>

    <!-- Sección de gestión de roles existentes -->
    <VCard class="mb-6">
      <VCardTitle>Roles Existentes</VCardTitle>
      <VCardText>
        <VRow>
          <VCol 
            v-for="role in roles" 
            :key="role.id" 
            cols="12" 
            sm="6" 
            md="4"
          >
            <VCard variant="outlined">
              <VCardText>
                <div class="d-flex justify-space-between align-center">
                  <div>
                    <h6 class="text-h6 mb-1">
                      {{ role.nombre }}
                    </h6>
                    <p class="text-caption text-medium-emphasis mb-0">
                      Estado: {{ role.activo ? 'Activo' : 'Inactivo' }}
                    </p>
                  </div>
                  <div>
                    <VTooltip text="Permisos">
                      <template #activator="{ props }">
                        <VBtn
                          v-bind="props"
                          icon="tabler-shield"
                          size="small"
                          variant="text"
                          color="primary"
                          @click="openPermissionsDialog(role)"
                        />
                      </template>
                    </VTooltip>
                    <VBtn
                      icon="tabler-edit"
                      size="small"
                      variant="text"
                      @click="editRole(role)"
                    />
                    <VBtn
                      icon="tabler-trash"
                      size="small"
                      variant="text"
                      color="error"
                      @click="deleteRole(role.id)"
                    />
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Diálogo de permisos por rol -->
    <VDialog
      v-model="isPermissionsDialogOpen"
      max-width="800px"
      persistent
    >
      <VCard>
        <!-- Header destacado con rol -->
        <VCardTitle class="pa-4 bg-primary">
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center gap-2">
              <VIcon
                icon="tabler-shield-check"
                size="28"
                color="white"
              />
              <span class="text-h6 text-white font-weight-bold">{{ roleForPermissions?.nombre }}</span>
            </div>
            <VBtn
              icon="tabler-x"
              variant="text"
              color="white"
              size="small"
              @click="closePermissionsDialog"
            />
          </div>
        </VCardTitle>

        <VCardText class="pa-4">
          <!-- Selector de vista inicial - compacto -->
          <VCard
            variant="outlined"
            class="mb-4"
          >
            <VCardText class="pa-3">
              <div class="d-flex align-center gap-2 mb-2">
                <VIcon
                  icon="tabler-home"
                  size="18"
                  color="primary"
                />
                <span class="text-subtitle-2 font-weight-medium">Vista Inicial</span>
              </div>
              <VSelect
                v-model="roleTuInicio"
                :items="availableRoutes"
                density="compact"
                variant="outlined"
                hide-details
                placeholder="Selecciona la vista inicial"
              />
            </VCardText>
          </VCard>

          <!-- Permisos agrupados por menú -->
          <div class="text-subtitle-2 font-weight-medium mb-3">
            Permisos de Acceso
          </div>
          
          <VExpansionPanels
            v-model="expandedMenus"
            multiple
          >
            <VExpansionPanel
              v-for="menuGroup in permissionsByMenu"
              :key="menuGroup.menu_id"
              elevation="0"
              class="mb-2"
            >
              <VExpansionPanelTitle class="py-3">
                <div class="d-flex align-center gap-2">
                  <VIcon
                    :icon="menuGroup.menu_icon || 'tabler-folder'"
                    size="20"
                    color="primary"
                  />
                  <span class="font-weight-semibold">{{ menuGroup.menu_name }}</span>
                  <VChip
                    size="x-small"
                    variant="tonal"
                    color="primary"
                  >
                    {{ menuGroup.permissions.length }}
                  </VChip>
                </div>
              </VExpansionPanelTitle>
              
              <VExpansionPanelText>
                <div
                  v-for="perm in menuGroup.permissions"
                  :key="perm.id"
                  class="d-flex align-center justify-space-between py-2 permission-item"
                >
                  <div class="d-flex align-center gap-2">
                    <VIcon
                      :icon="getPermissionIcon(perm.type)"
                      size="18"
                      :color="getPermissionColor(perm.type)"
                    />
                    <div>
                      <div class="text-body-2 font-weight-medium">{{ perm.name }}</div>
                    </div>
                  </div>
                  <VSwitch
                    :model-value="rolePermissionSet.has(perm.id)"
                    color="primary"
                    hide-details
                    density="compact"
                    @update:model-value="togglePermission(perm.id)"
                  />
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>
        </VCardText>

        <VDivider />

        <VCardActions class="pa-3">
          <VSpacer />
          <VBtn
            color="grey"
            variant="text"
            size="small"
            @click="closePermissionsDialog"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            size="small"
            @click="async () => { await savePermissions(); closePermissionsDialog() }"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialogs -->
    <AddEditRoleDialog 
      v-model:is-dialog-visible="isAddRoleDialogVisible"
      @role-created="onRoleCreated"
    />

    <AddEditRoleDialog 
      v-model:is-dialog-visible="isEditRoleDialogVisible"
      :role-permissions="editingRole"
      @role-updated="onRoleUpdated"
    />
  </div>
</template>

<style scoped>
.permission-item {
  border-bottom: 1px solid rgba(var(--v-border-color), 0.08);
}

.permission-item:last-child {
  border-bottom: none;
}

.permission-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.02);
}
</style>
