<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import axios from 'axios'

const router = useRouter()
const ability = useAbility()

// TODO: Get type from backend
const userData = useCookie('userData')

const logout = async () => {
  try {
    // Llamar al endpoint de logout para revocar tokens
    await axios.post('/api/logout')
  } catch (error) {
    console.error('Error al cerrar sesión:', error)
  } finally {
    // Eliminar TODOS los datos del localStorage
    localStorage.removeItem('accessToken')
    localStorage.removeItem('userData')
    localStorage.removeItem('userAbilityRules')
    localStorage.removeItem('homeRoute')
    
    // Limpiar todo el localStorage relacionado con el usuario (seguridad adicional)
    // Esto elimina cualquier otra clave que pueda haberse agregado
    const keysToRemove = []
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i)
      if (key && (key.startsWith('user') || key.includes('token') || key.includes('auth'))) {
        keysToRemove.push(key)
      }
    }
    keysToRemove.forEach(key => localStorage.removeItem(key))
    
    // Eliminar cookies con path explícito para asegurar borrado correcto
    useCookie('accessToken', { path: '/' }).value = null
    useCookie('userData', { path: '/' }).value = null
    useCookie('userAbilityRules', { path: '/' }).value = null

    // Reset ability a vacío
    ability.update([])

    console.log('🧹 Todos los datos del usuario eliminados')

    // Redirigir a login
    await router.push('/login')
  }
}

const userProfileList = [
  {
    type: 'navItem',
    icon: 'tabler-user',
    title: 'Perfil',
    to: { name: 'perfil' },
  },
]
</script>

<template>
  <div
    v-if="userData"
    class="d-flex align-center gap-3"
  >
    <!-- Información del usuario (nombre y rol) -->
    <div class="text-end d-none d-md-block">
      <div class="text-body-1 font-weight-medium text-high-emphasis">
        {{ userData.fullName || userData.name || userData.username }}
      </div>
      <div class="text-body-2 text-capitalize text-medium-emphasis">
        {{ userData.role || 'Usuario' }}
      </div>
    </div>

    <!-- Avatar con badge -->
    <VBadge
      dot
      bordered
      location="bottom right"
      offset-x="1"
      offset-y="2"
      color="success"
    >
      <VAvatar
        size="38"
        class="cursor-pointer"
        :color="!(userData && userData.avatar) ? 'primary' : undefined"
        :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
      >
        <VImg
          v-if="userData && userData.avatar"
          :src="userData.avatar"
        />
        <VIcon
          v-else
          icon="tabler-user"
        />

        <!-- SECTION Menu -->
        <VMenu
          activator="parent"
          width="240"
          location="bottom end"
          offset="12px"
        >
          <VList>
            <VListItem>
              <div class="d-flex gap-2 align-center">
                <VListItemAction>
                  <VBadge
                    dot
                    location="bottom right"
                    offset-x="3"
                    offset-y="3"
                    color="success"
                    bordered
                  >
                    <VAvatar
                      :color="!(userData && userData.avatar) ? 'primary' : undefined"
                      :variant="!(userData && userData.avatar) ? 'tonal' : undefined"
                    >
                      <VImg
                        v-if="userData && userData.avatar"
                        :src="userData.avatar"
                      />
                      <VIcon
                        v-else
                        icon="tabler-user"
                      />
                    </VAvatar>
                  </VBadge>
                </VListItemAction>

                <div>
                  <h6 class="text-h6 font-weight-medium">
                    {{ userData.fullName || userData.name || userData.username }}
                  </h6>
                  <VListItemSubtitle class="text-capitalize text-disabled">
                    {{ userData.role || 'Usuario' }}
                  </VListItemSubtitle>
                </div>
              </div>
            </VListItem>

            <PerfectScrollbar :options="{ wheelPropagation: false }">
              <template
                v-for="item in userProfileList"
                :key="item.title"
              >
                <VListItem
                  v-if="item.type === 'navItem'"
                  :to="item.to"
                >
                  <template #prepend>
                    <VIcon
                      :icon="item.icon"
                      size="22"
                    />
                  </template>

                  <VListItemTitle>{{ item.title }}</VListItemTitle>

                  <template
                    v-if="item.badgeProps"
                    #append
                  >
                    <VBadge
                      rounded="sm"
                      class="me-3"
                      v-bind="item.badgeProps"
                    />
                  </template>
                </VListItem>

                <VDivider
                  v-else
                  class="my-2"
                />
              </template>

              <div class="px-4 py-2">
                <VBtn
                  block
                  size="small"
                  color="error"
                  append-icon="tabler-logout"
                  @click="logout"
                >
                  Logout
                </VBtn>
              </div>
            </PerfectScrollbar>
          </VList>
        </VMenu>
        <!-- !SECTION -->
      </VAvatar>
    </VBadge>
  </div>
</template>
