<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Tipos de Justificación</h2>
          <p class="text-body-2 mb-0">Administra los tipos de justificación del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar tipo de justificación"
            placeholder="Buscar por nombre..."
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
            Nuevo Tipo
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de tipos de justificación -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="tiposJustificacionFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de Nombre -->
        <template #item.nombre="{ item }">
          <span class="font-weight-medium">{{ item.nombre }}</span>
        </template>

        <!-- Columna de Estado -->
        <template #item.activo="{ item }">
          <div class="d-flex align-center justify-right gap-1">
            <VIcon
              :color="item.activo ? 'success' : 'error'"
              size="20"
            >
              {{ item.activo ? 'tabler-circle-dot' : 'tabler-circle-dashed' }}
            </VIcon>
            <span class="text-caption">
              {{ item.activo ? 'Activo' : 'Inactivo' }}
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
              @click="editarTipoJustificacion(item)"
              :disabled="!item.activo"
            />

            <!-- Eliminar -->
            <VBtn
              icon="tabler-trash"
              size="small"
              color="error"
              variant="text"
              @click="mostrarConfirmacionEliminar(item)"
              :disabled="!item.activo"
            />
          </div>
        </template>

        <!-- Sin datos -->
        <template #no-data>
          <div class="text-center py-6">
            <VIcon size="64" color="grey-lighten-2">
              tabler-file-text
            </VIcon>
            <p class="text-h6 mt-4">No hay tipos de justificación registrados</p>
            <p class="text-body-2">Crea tu primer tipo de justificación haciendo clic en "Nuevo Tipo"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear tipo de justificación -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Tipo de Justificación</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarTipoJustificacion">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoTipoJustificacion.nombre"
                  label="Nombre del Tipo *"
                  placeholder="Ej: Permiso Médico, Asuntos Personales, etc."
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-tag"
                  required
                />
              </VCol>
            </VRow>
            <small class="text-caption text-disabled">* Campo obligatorio</small>
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
            @click="guardarTipoJustificacion"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar tipo de justificación -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="tipoJustificacionEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Tipo de Justificación</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarTipoJustificacion">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="tipoJustificacionEditando.nombre"
                  label="Nombre del Tipo *"
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-tag"
                  required
                />
              </VCol>
            </VRow>
            <small class="text-caption text-disabled">* Campo obligatorio</small>
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
            @click="actualizarTipoJustificacion"
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
            Eliminar Tipo de Justificación
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el tipo de justificación 
            <strong>"{{ tipoJustificacionConfirmandoEliminar?.nombre }}"</strong>?
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
              @click="confirmarEliminarTipoJustificacion"
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
    section: 'parametros-tipos-justificacion',
    action: 'read',
    subject: 'parametros-tipos-justificacion',
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
const tiposJustificacion = ref([])
const tipoJustificacionEditando = ref(null)
const tipoJustificacionConfirmandoEliminar = ref(null)

// Formulario nuevo tipo de justificación
const nuevoTipoJustificacion = ref({
  nombre: '',
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Nombre', key: 'nombre', sortable: true },
  { title: 'Estado', key: 'activo', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  max100: value => (value || '').length <= 100 || 'Máximo 100 caracteres',
}

// Computed properties
const tiposJustificacionFiltrados = computed(() => {
  if (!busqueda.value) return tiposJustificacion.value

  const busquedaLower = busqueda.value.toLowerCase()
  return tiposJustificacion.value.filter(tipo => {
    return tipo.nombre.toLowerCase().includes(busquedaLower)
  })
})

// Métodos
const cargarTiposJustificacion = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/tipos-justificacion')
    tiposJustificacion.value = response.data
  } catch (error) {
    console.error('Error al cargar tipos de justificación:', error)
    notificationStore.addNotification('Error al cargar los tipos de justificación', 'error')
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

const guardarTipoJustificacion = async () => {
  try {
    guardando.value = true
    
    // Preparar datos
    const datos = { ...nuevoTipoJustificacion.value }
    
    const response = await axios.post('/api/parametros/tipos-justificacion', datos)
    
    // Agregar el nuevo tipo a la lista
    tiposJustificacion.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Tipo de justificación "${response.data.nombre}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear tipo de justificación:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al crear el tipo de justificación'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarTipoJustificacion = (tipo) => {
  // Crear copia del objeto
  tipoJustificacionEditando.value = { ...tipo }
  modalEditarVisible.value = true
}

const actualizarTipoJustificacion = async () => {
  try {
    guardando.value = true
    
    const datos = { ...tipoJustificacionEditando.value }
    const id = datos.id
    
    // Remover el ID y otros campos no editables
    delete datos.id
    delete datos.creado_por
    delete datos.creado_el
    delete datos.modificado_por
    delete datos.modificado_el
    delete datos.eliminado_por
    delete datos.eliminado_el
    
    const response = await axios.put(`/api/parametros/tipos-justificacion/${id}`, datos)
    
    // Actualizar en la lista
    const index = tiposJustificacion.value.findIndex(t => t.id === id)
    if (index !== -1) {
      tiposJustificacion.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Tipo de justificación "${response.data.nombre}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar tipo de justificación:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al actualizar el tipo de justificación'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (tipo) => {
  tipoJustificacionConfirmandoEliminar.value = { ...tipo }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarTipoJustificacion = async () => {
  try {
    procesandoEliminar.value = true
    const tipo = tipoJustificacionConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/tipos-justificacion/${tipo.id}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    tiposJustificacion.value = tiposJustificacion.value.filter(t => t.id !== tipo.id)
    
    notificationStore.addNotification(`Tipo de justificación "${tipo.nombre}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    tipoJustificacionConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar tipo de justificación:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el tipo de justificación'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoTipoJustificacion.value = {
    nombre: '',
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarTiposJustificacion()
})
</script>