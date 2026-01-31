<template>
  <VDialog
    :model-value="isOpen"
    max-width="600px"
    persistent
    @update:model-value="val => { if (!val) emit('close') }"
  >
    <VCard>
      <VCardTitle class="headline">
        <span class="text-h5">Editar Usuario</span>
      </VCardTitle>
      
      <VCardText>
        <VForm
          ref="formRef"
          @submit.prevent="onSubmit"
        >
          <VContainer>
            <VRow>
              <!-- Nombre -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.name"
                  label="Nombre"
                  required
                  prepend-inner-icon="tabler-user"
                />
              </VCol>

              <!-- Email -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  required
                  prepend-inner-icon="tabler-mail"
                />
              </VCol>

              <!-- Contraseña -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.password"
                  label="Contraseña (dejar vacío para no cambiar)"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  prepend-inner-icon="tabler-lock"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <!-- Cargo -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.cargo"
                  label="Cargo"
                  prepend-inner-icon="tabler-briefcase"
                />
              </VCol>

              <!-- Fecha de nacimiento -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="form.fecha_nacimiento"
                  label="Fecha de nacimiento"
                  type="date"
                  prepend-inner-icon="tabler-calendar"
                />
              </VCol>

              <!-- Rol -->
              <VCol
                cols="12"
                md="6"
              >
                <VSelect
                  v-model="form.role_id"
                  :items="roles"
                  item-title="nombre"
                  item-value="id"
                  label="Rol"
                  prepend-inner-icon="tabler-users"
                />
              </VCol>

              <!-- Switch Activo -->
              
            </VRow>
          </VContainer>
        </VForm>
      </VCardText>

      <VCardActions>
        <VSpacer />
        <VBtn
          color="error"
          variant="outlined"
          @click="close"
        >
          Cancelar
        </VBtn>
        <VBtn
          color="success"
          variant="elevated"
          type="submit"
          @click="onSubmit"
        >
          Guardar cambios
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { useNotificationStore } from '@/store/notification'
import axios from 'axios'
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
  user: Object,
  isOpen: Boolean,
})

const emit = defineEmits(['close', 'updated'])

const notificationStore = useNotificationStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  cargo: '',
  fecha_nacimiento: '',
  role_id: null,
  activo: true,
})

const roles = ref([])
const formRef = ref()
const isPasswordVisible = ref(false)

watch(() => props.user, user => {
  if (user) {
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      cargo: user.cargo,
      fecha_nacimiento: user.fecha_nacimiento,

      // Mapear desde colección de roles si existe
      role_id: user.role_id || (Array.isArray(user.roles) && user.roles.length ? user.roles[0].id : (user.role ? user.role.id : null)),
      activo: user.activo !== false,
    }
  }
}, { immediate: true })

onMounted(async () => {
  const { data } = await axios.get('/api/roles')

  roles.value = data
})

const onSubmit = async () => {
  if (!formRef.value.validate()) return
  try {
    // Convertir role_id a arreglo roles solicitado por API
    const payload = {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password || undefined,
      cargo: form.value.cargo,
      fecha_nacimiento: form.value.fecha_nacimiento,
      activo: form.value.activo,
      roles: form.value.role_id ? [form.value.role_id] : [],
    }

    await axios.put(`/api/users/${props.user.id}`, payload)
    notificationStore.addNotification(`Usuario ${form.value.name} actualizado exitosamente`, 'success')
    emit('updated')
    close()
  } catch (e) {
    // Manejo de errores
    const errorMessage = e.response?.data?.message || 'Error al actualizar usuario'
    notificationStore.addNotification(errorMessage, 'error')
  }
}

function close() {
  emit('close')
}
</script>
