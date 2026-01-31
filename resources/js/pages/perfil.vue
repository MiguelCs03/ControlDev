<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useNotificationStore } from '@/store/notification'

const router = useRouter()
const notificationStore = useNotificationStore()

// Get user data from cookie
const userData = ref(null)
const isLoadingData = ref(false)

// Form data for profile
const profileForm = ref({
  name: '',
  username: '',
  email: '',
  numero: '',
  fecha_nacimiento: '',
  cargo: '',
})

// Form data for password change
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Loading states
const isProfileLoading = ref(false)
const isPasswordLoading = ref(false)

// Password visibility toggles
const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Referencias a los formularios
const passwordFormRef = ref(null)

// Validation rules
const rules = {
  required: value => !!value || 'Este campo es requerido',
  email: value => {
    const pattern = /^[\w.%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i
    
    return pattern.test(value) || 'Ingrese un email válido'
  },
  minPassword: value => value.length >= 6 || 'La contraseña debe tener al menos 6 caracteres',
  confirmPassword: value => value === passwordForm.value.newPassword || 'Las contraseñas no coinciden',
}

// Cargar datos del perfil desde la API
const loadProfile = async () => {
  try {
    isLoadingData.value = true
    const response = await axios.get('/api/profile')
    userData.value = response.data
    
    // Actualizar el formulario con los datos recibidos
    profileForm.value = {
      name: response.data.name || '',
      username: response.data.username || '',
      email: response.data.email || '',
      numero: response.data.numero || '',
      fecha_nacimiento: response.data.fecha_nacimiento || '',
      cargo: response.data.cargo || '',
    }
  } catch (error) {
    console.error('Error al cargar perfil:', error)
    notificationStore.addNotification('Error al cargar los datos del perfil', 'error')
  } finally {
    isLoadingData.value = false
  }
}

// Save profile data
const saveProfile = async () => {
  try {
    isProfileLoading.value = true

    // API call to update profile
    const response = await axios.put('/api/profile', profileForm.value)

    // Update userData with new data
    userData.value = response.data.user
    
    // Actualizar la cookie también
    const userDataCookie = useCookie('userData')
    if (userDataCookie.value) {
      userDataCookie.value = {
        ...userDataCookie.value,
        name: response.data.user.name,
        username: response.data.user.username,
        email: response.data.user.email,
        numero: response.data.user.numero,
      }
    }

    // Show success message
    notificationStore.addNotification('Perfil actualizado exitosamente', 'success')

  } catch (error) {
    console.error('Error al actualizar perfil:', error)
    notificationStore.addNotification('Error al actualizar el perfil', 'error')
  } finally {
    isProfileLoading.value = false
  }
}

// Change password
const changePassword = async () => {
  try {
    isPasswordLoading.value = true

    // API call to change password
    const response = await axios.put('/api/profile/password', {
      email: userData.value?.email,
      current_password: passwordForm.value.currentPassword,
      new_password: passwordForm.value.newPassword,
      new_password_confirmation: passwordForm.value.confirmPassword,
    })

    // Resetear el formulario completo (valores y validaciones)
    if (passwordFormRef.value) {
      passwordFormRef.value.reset()
    }

    // Reset form values como respaldo
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }

    // Show success message
    notificationStore.addNotification('Contraseña actualizada exitosamente', 'success')

  } catch (error) {
    const errorMessage = error.response?.data?.message || 'Error al cambiar la contraseña'
    notificationStore.addNotification(errorMessage, 'error')
    console.error('Error al cambiar contraseña:', error)
  } finally {
    isPasswordLoading.value = false
  }
}

const avatarModal = ref(false)
const avatarFile = ref(null)
const avatarPreview = ref('')
const isAvatarLoading = ref(false)

const openAvatarModal = () => {
  avatarModal.value = true
  avatarFile.value = null
  avatarPreview.value = userData.value?.avatar_url || userData.value?.avatar || ''
}

const closeAvatarModal = () => {
  avatarModal.value = false
  avatarFile.value = null
  avatarPreview.value = ''
}

const onAvatarChange = event => {
  const file = event.target.files[0]

  avatarFile.value = file
  if (file) {
    const reader = new FileReader()

    reader.onload = e => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else {
    avatarPreview.value = userData.value?.avatar_url || userData.value?.avatar || ''
  }
}

const uploadAvatar = async () => {
  if (!avatarFile.value) return
  isAvatarLoading.value = true

  const formData = new FormData()
  formData.append('avatar', avatarFile.value)
  
  const accessToken = useCookie('accessToken').value
  
  try {
    const response = await fetch('/user/avatar', {
      method: 'POST',
      body: formData,
      credentials: 'include',
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Accept': 'application/json',
      },
    })

    const data = await response.json()
    if (response.ok) {
      // Actualizar userData con la nueva URL del avatar
      userData.value = {
        ...userData.value,
        avatar: data.avatar,
        avatar_url: data.avatar_url,
      }
      
      // Actualizar también la cookie
      const userDataCookie = useCookie('userData')
      if (userDataCookie.value) {
        userDataCookie.value = {
          ...userDataCookie.value,
          avatar: data.avatar_url,
        }
      }
      
      closeAvatarModal()
      
      // Mostrar notificación de éxito
      notificationStore.addNotification('Foto de perfil actualizada correctamente', 'success')
      
      // Recargar datos del perfil
      await loadProfile()
    } else {
      notificationStore.addNotification(data.message || `Error al subir imagen (HTTP ${response.status})`, 'error')
    }
  } catch (err) {
    console.error('Error al subir avatar:', err)
    notificationStore.addNotification('Error de red al subir la imagen', 'error')
  } finally {
    isAvatarLoading.value = false
  }
}

// Meta for page
definePage({
  meta: {
    layout: 'default',
    // No se requiere 'section' porque el perfil debe ser accesible para todos los usuarios autenticados
    requiresAuth: true,
  },
})

// Cargar datos al montar el componente
onMounted(() => {
  loadProfile()
})
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h4 class="text-h4 font-weight-medium mb-1">
          Mi Perfil
        </h4>
        <p class="text-body-1 mb-0">
          Actualiza tu información personal y configuración de seguridad
        </p>
      </div>
    </div>

    <VRow>
      <VCol
        cols="12"
        md="8"
      >
        <!-- Profile Information Card -->
        <VCard class="mb-6">
          <VCardItem>
            <VCardTitle>Información Personal</VCardTitle>
            <VCardSubtitle>Actualiza tus datos personales</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VForm @submit.prevent="saveProfile">
              <VRow>
                <!-- seccion de foto de perfil -->
                <VCol
                  cols="12"
                  class="d-flex justify-center mb-4"
                >
                  <VAvatar
                    size="120"
                    :color="!(userData && (userData.avatar_url || userData.avatar)) ? 'primary' : undefined"
                    :variant="!(userData && (userData.avatar_url || userData.avatar)) ? 'tonal' : undefined"
                  >
                    <VImg
                      v-if="userData && (userData.avatar_url || userData.avatar)"
                      :src="userData.avatar_url || userData.avatar"
                    />
                    <VIcon
                      v-else
                      icon="tabler-user"
                      size="60"
                    />
                  </VAvatar>
                </VCol>
                <VCol
                  cols="12"
                  class="d-flex justify-center mb-2"
                >
                  <VBtn
                    color="primary"
                    variant="tonal"
                    @click="openAvatarModal"
                  >
                    Cambiar Foto de Perfil
                  </VBtn>
                </VCol>

                <!-- Full Name -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.name"
                    label="Nombre Completo"
                    :rules="[rules.required]"
                    prepend-inner-icon="tabler-user"
                  />
                </VCol>

                <!-- Username -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.username"
                    label="Nombre de Usuario"
                    :rules="[rules.required]"
                    prepend-inner-icon="tabler-at"
                  />
                </VCol>

                <!-- Email -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.email"
                    label="Correo Electrónico"
                    type="email"
                    :rules="[rules.required, rules.email]"
                    prepend-inner-icon="tabler-mail"
                  />
                </VCol>

                <!-- Phone/Numero -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.numero"
                    label="Teléfono"
                    prepend-inner-icon="tabler-phone"
                  />
                </VCol>

                <!-- Cargo -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.cargo"
                    label="Cargo"
                    prepend-inner-icon="tabler-briefcase"
                  />
                </VCol>

                <!-- Fecha de Nacimiento -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="profileForm.fecha_nacimiento"
                    label="Fecha de Nacimiento"
                    type="date"
                    prepend-inner-icon="tabler-calendar"
                  />
                </VCol>

                <!-- Save Button -->
                <VCol
                  cols="12"
                  class="d-flex justify-end gap-3"
                >
                  <VBtn
                    color="secondary"
                    variant="tonal"
                    @click="router.go(-1)"
                  >
                    Cancelar
                  </VBtn>
                  <VBtn
                    type="submit"
                    :loading="isProfileLoading"
                  >
                    Guardar Cambios
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>

        <!-- Password Change Card -->
        <VCard>
          <VCardItem>
            <VCardTitle>Cambiar Contraseña</VCardTitle>
            <VCardSubtitle>Actualiza tu contraseña para mantener tu cuenta segura</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VForm ref="passwordFormRef" @submit.prevent="changePassword">
              <VRow>
                <!-- Current Password -->
                <VCol cols="12">
                  <VTextField
                    v-model="passwordForm.currentPassword"
                    label="Contraseña Actual"
                    :type="isCurrentPasswordVisible ? 'text' : 'password'"
                    :rules="[rules.required]"
                    prepend-inner-icon="tabler-lock"
                    :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                  />
                </VCol>

                <!-- New Password -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="passwordForm.newPassword"
                    label="Nueva Contraseña"
                    :type="isNewPasswordVisible ? 'text' : 'password'"
                    :rules="[rules.required, rules.minPassword]"
                    prepend-inner-icon="tabler-lock"
                    :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  />
                </VCol>

                <!-- Confirm Password -->
                <VCol
                  cols="12"
                  md="6"
                >
                  <VTextField
                    v-model="passwordForm.confirmPassword"
                    label="Confirmar Nueva Contraseña"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    :rules="[rules.required, rules.confirmPassword]"
                    prepend-inner-icon="tabler-lock-check"
                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                  />
                </VCol>

                <!-- Change Password Button -->
                <VCol
                  cols="12"
                  class="d-flex justify-end"
                >
                  <VBtn
                    type="submit"
                    color="warning"
                    :loading="isPasswordLoading"
                  >
                    Cambiar Contraseña
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Sidebar with User Info -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardItem>
            <VCardTitle>Información de la Cuenta</VCardTitle>
          </VCardItem>

          <VCardText>
            <div class="d-flex align-center gap-3 mb-4">
              <VAvatar
                size="60"
                :color="!(userData && (userData.avatar_url || userData.avatar)) ? 'primary' : undefined"
                :variant="!(userData && (userData.avatar_url || userData.avatar)) ? 'tonal' : undefined"
              >
                <VImg
                  v-if="userData && (userData.avatar_url || userData.avatar)"
                  :src="userData.avatar_url || userData.avatar"
                />
                <VIcon
                  v-else
                  icon="tabler-user"
                />
              </VAvatar>
              <div>
                <h6 class="text-h6 font-weight-medium mb-1">
                  {{ userData?.name || userData?.username }}
                </h6>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  {{ userData?.cargo || 'Sin cargo asignado' }}
                </p>
              </div>
            </div>

            <VDivider class="mb-4" />

            <!-- Account Details -->
            <div class="mb-3">
              <h6 class="text-subtitle-1 font-weight-medium mb-2">
                Detalles de la Cuenta
              </h6>

              <div class="d-flex align-center gap-2 mb-2">
                <VIcon
                  icon="tabler-mail"
                  size="16"
                />
                <span class="text-body-2">{{ userData?.email || 'No especificado' }}</span>
              </div>

              <div class="d-flex align-center gap-2 mb-2">
                <VIcon
                  icon="tabler-user-circle"
                  size="16"
                />
                <span class="text-body-2">{{ userData?.username || 'No especificado' }}</span>
              </div>

              <div
                v-if="userData?.numero"
                class="d-flex align-center gap-2 mb-2"
              >
                <VIcon
                  icon="tabler-phone"
                  size="16"
                />
                <span class="text-body-2">{{ userData?.numero }}</span>
              </div>

              <div
                v-if="userData?.roles && userData.roles.length > 0"
                class="d-flex align-center gap-2"
              >
                <VIcon
                  icon="tabler-shield-check"
                  size="16"
                />
                <span class="text-body-2">{{ userData.roles[0]?.nombre || 'Usuario' }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <!-- Modal para subir imagen -->>
    <VDialog
      v-model="avatarModal"
      max-width="400"
    >
      <VCard>
        <VCardTitle class="text-h6">
          Subir Foto de Perfil
        </VCardTitle>
        <VCardText>
          <div class="d-flex flex-column align-center">
            <VAvatar
              size="120"
              class="mb-4"
            >
              <VImg
                v-if="avatarPreview"
                :src="avatarPreview"
              />
              <VIcon
                v-else
                icon="tabler-user"
                size="60"
              />
            </VAvatar>
            <input
              type="file"
              accept="image/*"
              @change="onAvatarChange"
            >
          </div>
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn
            variant="text"
            @click="closeAvatarModal"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            :loading="isAvatarLoading"
            :disabled="!avatarFile"
            @click="uploadAvatar"
          >
            Subir Foto
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar para notificaciones -->
    <VSnackbar
      v-model="snackbar"
      :color="snackbarColor"
      location="top"
      :timeout="3000"
      transition="slide-y-transition"
    >
      <div class="d-flex align-center gap-2">
        <VIcon 
          :icon="snackbarColor === 'success' ? 'tabler-circle-check' : 'tabler-alert-circle'"
          size="24"
        />
        <span>{{ snackbarMessage }}</span>
      </div>
    </VSnackbar>
  </div>
</template>

<style scoped>
.v-avatar {
  border: 2px solid rgb(var(--v-theme-surface));
}
</style>
