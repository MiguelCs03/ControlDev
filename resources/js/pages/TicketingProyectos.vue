<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Proyectos</h2>
          <p class="text-body-2 mb-0">Administra los proyectos del sistema de ticketing</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar proyecto"
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
            Nuevo Proyecto
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de proyectos -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="proyectosFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna Cliente -->
        <template #item.cliente="{ item }">
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-building" size="20" color="primary" />
            <span>{{ item.cliente.nombre }}</span>
          </div>
        </template>

        <!-- Columna Estado -->
        <template #item.estado="{ item }">
          <VChip
            :color="getColorEstado(item.estado)"
            size="small"
          >
            {{ item.estado }}
          </VChip>
        </template>

        <!-- Columna Progreso -->
        <template #item.progreso="{ item }">
          <div>
            <VProgressLinear
              :model-value="item.progreso"
              :color="getColorProgreso(item.progreso)"
              height="8"
              rounded
            />
            <span class="text-caption">{{ item.progreso }}%</span>
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
              @click="editarProyecto(item)"
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
              tabler-folder-off
            </VIcon>
            <p class="text-h6 mt-4">No hay proyectos registrados</p>
            <p class="text-body-2">Crea tu primer proyecto haciendo clic en "Nuevo Proyecto"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear proyecto -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Proyecto</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarProyecto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoProyecto.nombre"
                  label="Nombre del proyecto"
                  placeholder="Ej: Sistema de Gestión"
                  :rules="[reglas.requerido, reglas.max255]"
                  prepend-inner-icon="tabler-folder"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="nuevoProyecto.descripcion"
                  label="Descripción"
                  placeholder="Describe el proyecto..."
                  rows="3"
                  prepend-inner-icon="tabler-file-text"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="nuevoProyecto.cliente_id"
                  :items="clientes"
                  item-title="nombre"
                  item-value="id"
                  label="Cliente"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="nuevoProyecto.fecha_inicio"
                  label="Fecha de inicio"
                  type="date"
                  prepend-inner-icon="tabler-calendar"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="nuevoProyecto.fecha_fin_estimada"
                  label="Fecha fin estimada"
                  type="date"
                  prepend-inner-icon="tabler-calendar-due"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="nuevoProyecto.estado"
                  :items="estados"
                  label="Estado"
                  prepend-inner-icon="tabler-flag"
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
            @click="guardarProyecto"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar proyecto -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="600px"
      persistent
    >
      <VCard v-if="proyectoEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Proyecto</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarProyecto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="proyectoEditando.nombre"
                  label="Nombre del proyecto"
                  :rules="[reglas.requerido, reglas.max255]"
                  prepend-inner-icon="tabler-folder"
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="proyectoEditando.descripcion"
                  label="Descripción"
                  rows="3"
                  prepend-inner-icon="tabler-file-text"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="proyectoEditando.cliente_id"
                  :items="clientes"
                  item-title="nombre"
                  item-value="id"
                  label="Cliente"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="proyectoEditando.fecha_inicio"
                  label="Fecha de inicio"
                  type="date"
                  prepend-inner-icon="tabler-calendar"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField
                  v-model="proyectoEditando.fecha_fin_estimada"
                  label="Fecha fin estimada"
                  type="date"
                  prepend-inner-icon="tabler-calendar-due"
                />
              </VCol>

              <VCol cols="12">
                <VSelect
                  v-model="proyectoEditando.estado"
                  :items="estados"
                  label="Estado"
                  prepend-inner-icon="tabler-flag"
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
            @click="actualizarProyecto"
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
            Eliminar Proyecto
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el proyecto 
            <strong>"{{ proyectoConfirmandoEliminar?.nombre }}"</strong>?
          </p>
          <p class="text-body-2 text-warning mb-4">
            Esta acción eliminará también todas las tareas asociadas.
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
              @click="confirmarEliminarProyecto"
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
    section: 'ticketing-proyectos',
    action: 'read',
    subject: 'ticketing-proyectos',
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
const proyectos = ref([])
const clientes = ref([])
const proyectoEditando = ref(null)
const proyectoConfirmandoEliminar = ref(null)

// Formulario nuevo proyecto
const nuevoProyecto = ref({
  nombre: '',
  descripcion: '',
  cliente_id: null,
  estado: 'activo',
  fecha_inicio: null,
  fecha_fin_estimada: null,
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Nombre', key: 'nombre', sortable: true },
  { title: 'Cliente', key: 'cliente', sortable: false },
  { title: 'Estado', key: 'estado', sortable: true },
  { title: 'Progreso', key: 'progreso', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

// Opciones
const estados = ['activo', 'completado', 'pausado', 'cancelado']

// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  max255: value => value.length <= 255 || 'Máximo 255 caracteres',
}

// Filtrar proyectos
const proyectosFiltrados = computed(() => {
  if (!busqueda.value) return proyectos.value

  const busquedaLower = busqueda.value.toLowerCase()
  return proyectos.value.filter(proyecto => {
    return proyecto.nombre.toLowerCase().includes(busquedaLower) ||
      proyecto.cliente?.nombre.toLowerCase().includes(busquedaLower)
  })
})

// Métodos
const cargarProyectos = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/proyectos')
    proyectos.value = response.data
  } catch (error) {
    console.error('Error al cargar proyectos:', error)
    notificationStore.addNotification('Error al cargar los proyectos', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarClientes = async () => {
  try {
    const response = await axios.get('/api/clientes')
    clientes.value = response.data
  } catch (error) {
    console.error('Error al cargar clientes:', error)
  }
}

const mostrarModalCrear = () => {
  modalCrearVisible.value = true
}

const cerrarModalCrear = () => {
  modalCrearVisible.value = false
  resetearFormularioCrear()
}

const guardarProyecto = async () => {
  try {
    guardando.value = true
    const datos = { ...nuevoProyecto.value }
    
    const response = await axios.post('/api/proyectos', datos)
    
    // Agregar el nuevo proyecto a la lista
    proyectos.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Proyecto "${response.data.nombre}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear proyecto:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear el proyecto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarProyecto = (proyecto) => {
  proyectoEditando.value = { 
    ...proyecto,
    // Convertir fechas a formato YYYY-MM-DD para el input type="date"
    fecha_inicio: proyecto.fecha_inicio ? proyecto.fecha_inicio.split('T')[0] : null,
    fecha_fin_estimada: proyecto.fecha_fin_estimada ? proyecto.fecha_fin_estimada.split('T')[0] : null,
  }
  modalEditarVisible.value = true
}

const actualizarProyecto = async () => {
  try {
    guardando.value = true
    const datos = { ...proyectoEditando.value }
    const id = datos.id
    
    // Remover el ID de los datos a actualizar
    delete datos.id
    delete datos.cliente
    delete datos.progreso
    delete datos.tareas_pendientes
    delete datos.created_at
    delete datos.updated_at
    
    const response = await axios.put(`/api/proyectos/${id}`, datos)
    
    // Actualizar en la lista
    const index = proyectos.value.findIndex(p => p.id === id)
    if (index !== -1) {
      proyectos.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Proyecto "${response.data.nombre}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar proyecto:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar el proyecto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (proyecto) => {
  proyectoConfirmandoEliminar.value = { ...proyecto }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarProyecto = async () => {
  try {
    procesandoEliminar.value = true
    const proyecto = proyectoConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/proyectos/${proyecto.id}`)
    
    // Remover de la lista
    proyectos.value = proyectos.value.filter(p => p.id !== proyecto.id)
    
    notificationStore.addNotification(`Proyecto "${proyecto.nombre}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    proyectoConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar proyecto:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el proyecto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoProyecto.value = {
    nombre: '',
    descripcion: '',
    cliente_id: null,
    estado: 'activo',
    fecha_inicio: null,
    fecha_fin_estimada: null,
  }
}

const getColorEstado = (estado) => {
  const colores = {
    activo: 'success',
    completado: 'primary',
    pausado: 'warning',
    cancelado: 'error'
  }
  return colores[estado] || 'grey'
}

const getColorProgreso = (progreso) => {
  if (progreso >= 80) return 'success'
  if (progreso >= 50) return 'primary'
  if (progreso >= 25) return 'warning'
  return 'error'
}

// Cargar datos al inicio
onMounted(() => {
  cargarProyectos()
  cargarClientes()
})
</script>
