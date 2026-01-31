<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Unidades</h2>
          <p class="text-body-2 mb-0">Administra las unidades del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar unidad"
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
            Nueva Unidad
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de unidades -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="unidadesFiltradas"
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
              @click="editarUnidad(item)"
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
              tabler-building
            </VIcon>
            <p class="text-h6 mt-4">No hay unidades registradas</p>
            <p class="text-body-2">Crea tu primera unidad haciendo clic en "Nueva Unidad"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear unidad -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nueva Unidad</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarUnidad">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevaUnidad.DESCRIPCION"
                  label="Descripción de la unidad"
                  placeholder="Ej: Unidad Central"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-building"
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
            @click="guardarUnidad"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar unidad -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="unidadEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Unidad</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarUnidad">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="unidadEditando.DESCRIPCION"
                  label="Descripción de la unidad"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-building"
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
            @click="actualizarUnidad"
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
            Eliminar Unidad
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar la unidad 
            <strong>"{{ unidadConfirmandoEliminar?.DESCRIPCION }}"</strong>?
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
              @click="confirmarEliminarUnidad"
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
    section: 'parametros-unidades',
    action: 'read',
    subject: 'parametros-unidades',
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
const unidades = ref([])
const unidadEditando = ref(null)
const unidadConfirmandoEliminar = ref(null)

// Formulario nueva unidad (solo DESCRIPCION)
const nuevaUnidad = ref({
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
  max50: value => value.length <= 50 || 'Máximo 50 caracteres',
}

// Filtrar unidades
const unidadesFiltradas = computed(() => {
  if (!busqueda.value) return unidades.value

  const busquedaLower = busqueda.value.toLowerCase()
  return unidades.value.filter(unidad => {
    return unidad.DESCRIPCION.toLowerCase().includes(busquedaLower)
  })
})

// Métodos
const cargarUnidades = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/unidades')
    unidades.value = response.data
  } catch (error) {
    console.error('Error al cargar unidades:', error)
    notificationStore.addNotification('Error al cargar las unidades', 'error')
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

const guardarUnidad = async () => {
  try {
    guardando.value = true
    const datos = { ...nuevaUnidad.value }
    
    const response = await axios.post('/api/parametros/unidades', datos)
    
    // Agregar la nueva unidad a la lista
    unidades.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Unidad "${response.data.DESCRIPCION}" creada exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear unidad:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear la unidad'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarUnidad = (unidad) => {
  unidadEditando.value = { ...unidad }
  modalEditarVisible.value = true
}

const actualizarUnidad = async () => {
  try {
    guardando.value = true
    const datos = { ...unidadEditando.value }
    const id = datos.COD_UNIDAD
    
    // Remover el ID de los datos a actualizar
    delete datos.COD_UNIDAD
    
    const response = await axios.put(`/api/parametros/unidades/${id}`, datos)
    
    // Actualizar en la lista
    const index = unidades.value.findIndex(u => u.COD_UNIDAD === id)
    if (index !== -1) {
      unidades.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Unidad "${response.data.DESCRIPCION}" actualizada exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar unidad:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar la unidad'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (unidad) => {
  unidadConfirmandoEliminar.value = { ...unidad }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarUnidad = async () => {
  try {
    procesandoEliminar.value = true
    const unidad = unidadConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/unidades/${unidad.COD_UNIDAD}`)
    
    // Remover de la lista (ya que solo se muestran activas)
    unidades.value = unidades.value.filter(u => u.COD_UNIDAD !== unidad.COD_UNIDAD)
    
    notificationStore.addNotification(`Unidad "${unidad.DESCRIPCION}" eliminada exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    unidadConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar unidad:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar la unidad'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevaUnidad.value = {
    DESCRIPCION: '',
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarUnidades()
})
</script>