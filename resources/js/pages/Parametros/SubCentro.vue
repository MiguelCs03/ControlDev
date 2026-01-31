<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Subcentros</h2>
          <p class="text-body-2 mb-0">Administra los subcentros del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar subcentro"
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
            Nuevo Subcentro
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de subcentros -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="subcentrosFiltrados"
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
              @click="editarSubcentro(item)"
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
              tabler-building-community
            </VIcon>
            <p class="text-h6 mt-4">No hay subcentros registrados</p>
            <p class="text-body-2">Crea tu primer subcentro haciendo clic en "Nuevo Subcentro"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear subcentro -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Subcentro</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarSubcentro">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoSubcentro.DESCRIPCION"
                  label="Descripción del subcentro"
                  placeholder="Ej: Subcentro Administrativo"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-building-community"
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
            @click="guardarSubcentro"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar subcentro -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="subcentroEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Subcentro</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarSubcentro">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="subcentroEditando.DESCRIPCION"
                  label="Descripción del subcentro"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-building-community"
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
            @click="actualizarSubcentro"
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
            Eliminar Subcentro
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el subcentro 
            <strong>"{{ subcentroConfirmandoEliminar?.DESCRIPCION }}"</strong>?
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
              @click="confirmarEliminarSubcentro"
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
    section: 'parametros-subcentros',
    action: 'read',
    subject: 'parametros-subcentros',
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
const subcentros = ref([])
const subcentroEditando = ref(null)
const subcentroConfirmandoEliminar = ref(null)

// Formulario nuevo subcentro
const nuevoSubcentro = ref({
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

// Filtrar subcentros
const subcentrosFiltrados = computed(() => {
  if (!busqueda.value) return subcentros.value

  const busquedaLower = busqueda.value.toLowerCase()
  return subcentros.value.filter(subcentro => {
    return subcentro.DESCRIPCION.toLowerCase().includes(busquedaLower)
  })
})

// Métodos
const cargarSubcentros = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/subcentros')
    subcentros.value = response.data
  } catch (error) {
    console.error('Error al cargar subcentros:', error)
    notificationStore.addNotification('Error al cargar los subcentros', 'error')
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

const guardarSubcentro = async () => {
  try {
    guardando.value = true
    const datos = { ...nuevoSubcentro.value }
    
    const response = await axios.post('/api/parametros/subcentros', datos)
    
    // Agregar el nuevo subcentro a la lista
    subcentros.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Subcentro "${response.data.DESCRIPCION}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear subcentro:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear el subcentro'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarSubcentro = (subcentro) => {
  subcentroEditando.value = { ...subcentro }
  modalEditarVisible.value = true
}

const actualizarSubcentro = async () => {
  try {
    guardando.value = true
    const datos = { ...subcentroEditando.value }
    const id = datos.COD_SUBCENTRO
    
    // Remover el ID de los datos a actualizar
    delete datos.COD_SUBCENTRO
    
    const response = await axios.put(`/api/parametros/subcentros/${id}`, datos)
    
    // Actualizar en la lista
    const index = subcentros.value.findIndex(s => s.COD_SUBCENTRO === id)
    if (index !== -1) {
      subcentros.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Subcentro "${response.data.DESCRIPCION}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar subcentro:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar el subcentro'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (subcentro) => {
  subcentroConfirmandoEliminar.value = { ...subcentro }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarSubcentro = async () => {
  try {
    procesandoEliminar.value = true
    const subcentro = subcentroConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/subcentros/${subcentro.COD_SUBCENTRO}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    subcentros.value = subcentros.value.filter(s => s.COD_SUBCENTRO !== subcentro.COD_SUBCENTRO)
    
    notificationStore.addNotification(`Subcentro "${subcentro.DESCRIPCION}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    subcentroConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar subcentro:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el subcentro'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoSubcentro.value = {
    DESCRIPCION: '',
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarSubcentros()
})
</script>