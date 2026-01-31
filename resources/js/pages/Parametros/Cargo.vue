<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Cargos</h2>
          <p class="text-body-2 mb-0">Administra los cargos del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar cargo"
            placeholder="Buscar por descripción..."
            prepend-inner-icon="tabler-search"
            clearable
            density="comfortable"
            hide-details
            style="min-width: 250px;"
          />

          <VBtn
            color="success"
            prepend-icon="tabler-plus"
            @click="mostrarModalCrear"
          >
            Nuevo Cargo
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de cargos -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="cargosFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de Estado - Ahora siempre será "Activo" -->
        <template #item.ACTIVO="{ item }">
          <div class="d-flex align-center justify-right gap-1">
            <VIcon
              color="success"
              size="20"
            >
              tabler-circle-dot
            </VIcon>
            <span class="text-caption">
              Activo
            </span>
          </div>
        </template>

        <!-- Columna de Acciones -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-2">
            <!-- Editar -->
            <VBtn
              icon="tabler-pencil"
              size="small"
              color="primary"
              variant="text"
              @click="editarCargo(item)"
            />

            <!-- Eliminar -->
            <VBtn
              icon="tabler-trash"
              size="small"
              color="error"
              variant="text"
              @click="mostrarConfirmacionEliminar(item)"
            />
          </div>
        </template>

        <!-- Sin datos -->
        <template #no-data>
          <div class="text-center py-6">
            <VIcon size="64" color="grey-lighten-2">
              tabler-briefcase-off
            </VIcon>
            <p class="text-h6 mt-4">No hay cargos registrados</p>
            <p class="text-body-2">Crea tu primer cargo haciendo clic en "Nuevo Cargo"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear cargo -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Cargo</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarCargo">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoCargo.DESCRIPCION"
                  label="Descripción del cargo"
                  placeholder="Ej: Gerente General"
                  :rules="[reglas.requerido, reglas.max150]"
                  prepend-inner-icon="tabler-briefcase"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="cerrarModalCrear">
            Cancelar
          </VBtn>
          <VBtn
            color="success"
            :loading="guardando"
            :disabled="guardando"
            @click="guardarCargo"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar cargo -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="cargoEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Cargo</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarCargo">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="cargoEditando.DESCRIPCION"
                  label="Descripción del cargo"
                  :rules="[reglas.requerido, reglas.max150]"
                  prepend-inner-icon="tabler-briefcase"
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="modalEditarVisible = false">
            Cancelar
          </VBtn>
          <VBtn
            color="success"
            :loading="guardando"
            :disabled="guardando"
            @click="actualizarCargo"
          >
            Actualizar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal de confirmación para eliminar -->
    <VDialog
      v-model="modalConfirmacionEliminarVisible"
      max-width="400px"
      persistent
    >
      <VCard>
        <VCardText class="text-center pt-6">
          <!-- Icono de eliminación -->
          <VIcon
            color="error"
            size="64"
            class="mb-4"
          >
            tabler-trash
          </VIcon>
          
          <h3 class="text-h5 mb-2">
            Eliminar Cargo
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el cargo 
            <strong>"{{ cargoConfirmandoEliminar?.DESCRIPCION }}"</strong>?
          </p>
          <p class="text-body-2 text-warning mb-4">
            Esta acción no se puede deshacer.
          </p>

          <div class="d-flex justify-center gap-3 pt-2">
            <VBtn
              color="grey"
              variant="outlined"
              :disabled="procesandoEliminar"
              @click="modalConfirmacionEliminarVisible = false"
            >
              Cancelar
            </VBtn>
            <VBtn
              color="error"
              :loading="procesandoEliminar"
              :disabled="procesandoEliminar"
              @click="confirmarEliminarCargo"
            >
              <VIcon
                icon="tabler-trash"
                size="20"
                class="me-1"
              />
              Eliminar
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/store/notification'

// Define metadata para la página
definePage({
  meta: {
    section: 'parametros-cargos',
    action: 'read',
    subject: 'parametros-cargos',
  },
})

const notificationStore = useNotificationStore()

// Estados
const cargando = ref(false)
const guardando = ref(false)
const procesandoEliminar = ref(false)
const busqueda = ref('')
const modalCrearVisible = ref(false)
const modalEditarVisible = ref(false)
const modalConfirmacionEliminarVisible = ref(false)

// Datos
const cargos = ref([])
const cargoEditando = ref(null)
const cargoConfirmandoEliminar = ref(null)

// Formulario nuevo cargo
const nuevoCargo = ref({
  DESCRIPCION: '',
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Descripción', key: 'DESCRIPCION', sortable: true },
  { title: 'Estado', key: 'ACTIVO', sortable: false },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  max150: value => value.length <= 150 || 'Máximo 150 caracteres',
}

// Filtrar cargos
const cargosFiltrados = computed(() => {
  if (!busqueda.value) return cargos.value

  const busquedaLower = busqueda.value.toLowerCase()
  return cargos.value.filter(cargo => {
    return cargo.DESCRIPCION.toLowerCase().includes(busquedaLower)
  })
})

// Métodos
const cargarCargos = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/cargos')
    cargos.value = response.data
  } catch (error) {
    console.error('Error al cargar cargos:', error)
    notificationStore.addNotification('Error al cargar los cargos', 'error')
  } finally {
    cargando.value = false
  }
}

const mostrarModalCrear = () => {
  modalCrearVisible.value = true
}

const cerrarModalCrear = () => {
  modalCrearVisible.value = false
  resetearFormularioCrear()
}

const guardarCargo = async () => {
  try {
    guardando.value = true
    const datos = { ...nuevoCargo.value }
    
    const response = await axios.post('/api/parametros/cargos', datos)
    
    // Agregar el nuevo cargo a la lista
    cargos.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Cargo "${response.data.DESCRIPCION}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear cargo:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear el cargo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarCargo = (cargo) => {
  cargoEditando.value = { ...cargo }
  modalEditarVisible.value = true
}

const actualizarCargo = async () => {
  try {
    guardando.value = true
    const datos = { ...cargoEditando.value }
    const id = datos.COD_CARGO
    
    // Remover el ID de los datos a actualizar
    delete datos.COD_CARGO
    
    const response = await axios.put(`/api/parametros/cargos/${id}`, datos)
    
    // Actualizar en la lista
    const index = cargos.value.findIndex(c => c.COD_CARGO === id)
    if (index !== -1) {
      cargos.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Cargo "${response.data.DESCRIPCION}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar cargo:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar el cargo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (cargo) => {
  cargoConfirmandoEliminar.value = { ...cargo }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarCargo = async () => {
  try {
    procesandoEliminar.value = true
    const cargo = cargoConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/cargos/${cargo.COD_CARGO}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    cargos.value = cargos.value.filter(c => c.COD_CARGO !== cargo.COD_CARGO)
    
    notificationStore.addNotification(`Cargo "${cargo.DESCRIPCION}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    cargoConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar cargo:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el cargo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoCargo.value = {
    DESCRIPCION: '',
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarCargos()
})
</script>