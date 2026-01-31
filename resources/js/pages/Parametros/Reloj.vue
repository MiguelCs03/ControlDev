<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Relojes</h2>
          <p class="text-body-2 mb-0">Administra los relojes del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar reloj"
            placeholder="Buscar por nombre o IP..."
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
            Nuevo Reloj
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de relojes -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="relojesFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de IP -->
        <template #item.IP="{ item }">
          <VChip size="small" variant="tonal" color="info">
            {{ item.IP }}
          </VChip>
        </template>

        <!-- Columna de Estado - CON ÍCONOS -->
        <template #item.ESTADO="{ item }">
          <div class="d-flex align-center justify-right gap-1">
            <VIcon
              :color="item.ESTADO === 1 ? 'success' : 'error'"
              size="20"
            >
              {{ item.ESTADO === 1 ? 'tabler-circle-dot' : 'tabler-circle' }}
            </VIcon>
            <span class="text-caption">
              {{ item.ESTADO === 1 ? 'Conectado' : 'Desconectado' }}
            </span>
          </div>
        </template>


        <!-- Columna de Última Descarga -->
        <template #item.ULTIMA_DESCARGA="{ item }">
          {{ formatearFecha(item.ULTIMA_DESCARGA) || 'No registrada' }}
        </template>

        <!-- Columna de Última Descarga Buena -->
        <template #item.ULTIMA_DESCARGA_BUENA="{ item }">
          {{ formatearFecha(item.ULTIMA_DESCARGA_BUENA) || 'No registrada' }}
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
              @click="editarReloj(item)"
            />

            <!-- Eliminar -->
            <VBtn
              icon="tabler-trash"
              size="small"
              color="error"
              variant="text"
              @click="mostrarConfirmacionEliminar(item)"
            />

            <!-- Ver detalles -->
            <VBtn
              icon="tabler-eye"
              size="small"
              color="info"
              variant="text"
              @click="verDetalles(item)"
            />
          </div>
        </template>

        <!-- Sin datos -->
        <template #no-data>
          <div class="text-center py-6">
            <VIcon size="64" color="grey-lighten-2">
              tabler-clock-off
            </VIcon>
            <p class="text-h6 mt-4">No hay relojes registrados</p>
            <p class="text-body-2">Crea tu primer reloj haciendo clic en "Nuevo Reloj"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear reloj -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Reloj</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarReloj">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoReloj.NOMBRE"
                  label="Nombre del reloj"
                  placeholder="Ej: Reloj Oficina Principal"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-clock"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="nuevoReloj.OC1"
                  label="OC1"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                  @input="generarIP"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="nuevoReloj.OC2"
                  label="OC2"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                  @input="generarIP"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="nuevoReloj.OC3"
                  label="OC3"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                  @input="generarIP"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="nuevoReloj.OC4"
                  label="OC4"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                  @input="generarIP"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="ipGenerada"
                  label="IP Generada"
                  readonly
                  prepend-inner-icon="tabler-network"
                  hint="La IP se genera automáticamente a partir de los OC"
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
            @click="guardarReloj"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar reloj -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="relojEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Reloj</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarReloj">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="relojEditando.NOMBRE"
                  label="Nombre del reloj"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-clock"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="relojEditando.OC1"
                  label="OC1"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="relojEditando.OC2"
                  label="OC2"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="relojEditando.OC3"
                  label="OC3"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                />
              </VCol>

              <VCol cols="3">
                <VTextField
                  v-model="relojEditando.OC4"
                  label="OC4"
                  type="number"
                  min="0"
                  max="255"
                  :rules="[reglas.requerido, reglas.oc]"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  :value="`${relojEditando.OC1}.${relojEditando.OC2}.${relojEditando.OC3}.${relojEditando.OC4}`"
                  label="IP"
                  readonly
                  prepend-inner-icon="tabler-network"
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
            @click="actualizarReloj"
          >
            Actualizar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal ver detalles -->
    <VDialog
      v-model="modalDetallesVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="relojDetalles">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Detalles del Reloj</span>
          <VBtn icon="tabler-x" variant="text" @click="modalDetallesVisible = false" />
        </VCardTitle>

        <VCardText>
          <VList lines="two">
            <VListItem>
              <VListItemTitle class="font-weight-bold">Nombre</VListItemTitle>
              <VListItemSubtitle>{{ relojDetalles.NOMBRE }}</VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">IP</VListItemTitle>
              <VListItemSubtitle>
                <VChip size="small">{{ relojDetalles.IP }}</VChip>
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Estado</VListItemTitle>
              <VListItemSubtitle>
                <div class="d-flex align-center gap-2">
                  <VIcon
                    :color="relojDetalles.ESTADO === 1 ? 'success' : 'error'"
                    size="20"
                  >
                    {{ relojDetalles.ESTADO === 1 ? 'tabler-circle-dot' : 'tabler-circle' }}
                  </VIcon>
                  <span>{{ relojDetalles.ESTADO === 1 ? 'Conectado' : 'Desconectado' }}</span>
                </div>
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Activo</VListItemTitle>
              <VListItemSubtitle>
                <VChip
                  :color="relojDetalles.ACTIVO ? 'success' : 'error'"
                  size="small"
                >
                  {{ relojDetalles.ACTIVO ? 'ACTIVO' : 'INACTIVO' }}
                </VChip>
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Última Descarga</VListItemTitle>
              <VListItemSubtitle>
                {{ formatearFecha(relojDetalles.ULTIMA_DESCARGA) || 'No registrada' }}
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Última Descarga Buena</VListItemTitle>
              <VListItemSubtitle>
                {{ formatearFecha(relojDetalles.ULTIMA_DESCARGA_BUENA) || 'No registrada' }}
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Creado por</VListItemTitle>
              <VListItemSubtitle>
                Usuario ID: {{ relojDetalles.CREADO_POR || 'No especificado' }}
              </VListItemSubtitle>
            </VListItem>

            <VListItem>
              <VListItemTitle class="font-weight-bold">Fecha creación</VListItemTitle>
              <VListItemSubtitle>
                {{ formatearFecha(relojDetalles.CREADO_EL) }}
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="success" @click="modalDetallesVisible = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal de confirmación para eliminar -->
    <VDialog
      v-model="modalConfirmacionVisible"
      max-width="400px"
      persistent
    >
      <VCard>
        <VCardText class="text-center pt-6">
          <!-- Icono grande de advertencia -->
          <VIcon
            color="warning"
            size="64"
            class="mb-4"
          >
            tabler-alert-triangle
          </VIcon>
          
          <h3 class="text-h5 mb-2">
            Eliminar Reloj
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el reloj <strong>"{{ relojConfirmando?.NOMBRE }}"</strong>?
            <br>
            <span class="text-caption text-medium-emphasis">
              Esta acción es irreversible. El reloj se marcará como inactivo en el sistema.
            </span>
          </p>

          <div class="d-flex justify-center gap-3 pt-2">
            <VBtn
              color="grey"
              variant="outlined"
              :disabled="procesandoEliminar"
              @click="modalConfirmacionVisible = false"
            >
              Cancelar
            </VBtn>
            <VBtn
              color="error"
              :loading="procesandoEliminar"
              :disabled="procesandoEliminar"
              @click="confirmarEliminar"
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
    section: 'parametros-relojes',
    action: 'read',
    subject: 'parametros-relojes',
  },
})

const notificationStore = useNotificationStore()

// Estados
const cargando = ref(false)
const guardando = ref(false)
const busqueda = ref('')
const modalCrearVisible = ref(false)
const modalEditarVisible = ref(false)
const modalDetallesVisible = ref(false)
const modalConfirmacionVisible = ref(false)
const procesandoEliminar = ref(false)

// Datos
const relojes = ref([])
const relojEditando = ref(null)
const relojDetalles = ref(null)
const relojConfirmando = ref(null)
const ipGenerada = ref('')

// Formulario nuevo reloj
const nuevoReloj = ref({
  NOMBRE: '',
  OC1: '',
  OC2: '',
  OC3: '',
  OC4: '',
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Nombre', key: 'NOMBRE', sortable: true },
  { title: 'IP', key: 'IP', sortable: true },
  { title: 'Estado', key: 'ESTADO', sortable: true },
  { title: 'Última Descarga', key: 'ULTIMA_DESCARGA', sortable: true },
  { title: 'Última Descarga Buena', key: 'ULTIMA_DESCARGA_BUENA', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 150 },
]

// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  oc: value => (value >= 0 && value <= 255) || 'Valor debe ser entre 0 y 255',
}

// Filtrar relojes
const relojesFiltrados = computed(() => {
  if (!busqueda.value) return relojes.value

  const busquedaLower = busqueda.value.toLowerCase()
  return relojes.value.filter(reloj => {
    return (
      reloj.NOMBRE.toLowerCase().includes(busquedaLower) ||
      reloj.IP.toLowerCase().includes(busquedaLower)
    )
  })
})

// Métodos
const cargarRelojes = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/relojes/activos')
    relojes.value = response.data
  } catch (error) {
    console.error('Error al cargar relojes:', error)
    notificationStore.addNotification('Error al cargar los relojes', 'error')
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

const generarIP = () => {
  const { OC1, OC2, OC3, OC4 } = nuevoReloj.value
  if (OC1 && OC2 && OC3 && OC4) {
    ipGenerada.value = `${OC1}.${OC2}.${OC3}.${OC4}`
  } else {
    ipGenerada.value = ''
  }
}

const guardarReloj = async () => {
  try {
    guardando.value = true
    const datos = { ...nuevoReloj.value }
    
    const response = await axios.post('/api/parametros/relojes', datos)
    
    // Agregar el nuevo reloj a la lista
    relojes.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar mensaje de éxito
    notificationStore.addNotification(`Reloj "${response.data.NOMBRE}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear reloj:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear el reloj'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarReloj = (reloj) => {
  relojEditando.value = { ...reloj }
  modalEditarVisible.value = true
}

const actualizarReloj = async () => {
  try {
    guardando.value = true
    const datos = { ...relojEditando.value }
    const id = datos.COD_RELOJ
    
    // Remover el ID de los datos a actualizar
    delete datos.COD_RELOJ
    
    const response = await axios.put(`/api/parametros/relojes/${id}`, datos)
    
    // Actualizar en la lista
    const index = relojes.value.findIndex(r => r.COD_RELOJ === id)
    if (index !== -1) {
      relojes.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Reloj "${response.data.NOMBRE}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar reloj:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar el reloj'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (reloj) => {
  relojConfirmando.value = { ...reloj }
  modalConfirmacionVisible.value = true
}

const confirmarEliminar = async () => {
  try {
    procesandoEliminar.value = true
    const reloj = relojConfirmando.value
    
    // Llamar al endpoint DELETE (eliminación lógica)
    await axios.delete(`/api/parametros/relojes/${reloj.COD_RELOJ}`)
    
    // Recargar el listado completo para que no aparezca el registro
    await cargarRelojes()
    
    notificationStore.addNotification(`Reloj "${reloj.NOMBRE}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionVisible.value = false
    relojConfirmando.value = null
  } catch (error) {
    console.error('Error al eliminar reloj:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el reloj'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const verDetalles = (reloj) => {
  relojDetalles.value = { ...reloj }
  modalDetallesVisible.value = true
}

const formatearFecha = (fecha) => {
  if (!fecha) return ''
  return new Date(fecha).toLocaleString('es-ES')
}

const resetearFormularioCrear = () => {
  nuevoReloj.value = {
    NOMBRE: '',
    OC1: '',
    OC2: '',
    OC3: '',
    OC4: '',
  }
  ipGenerada.value = ''
}

// Cargar datos al inicio
onMounted(() => {
  cargarRelojes()
})
</script>