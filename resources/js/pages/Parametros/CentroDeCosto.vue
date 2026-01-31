<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Centros de Costo</h2>
          <p class="text-body-2 mb-0">Administra los centros de costo del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar centro de costo"
            placeholder="Buscar por descripción, unidad o subcentro..."
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
            Nuevo Centro de Costo
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de centros de costo -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="centrosCostoFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de Unidad -->
        <template #item.unidad="{ item }">
          <span>{{ item.unidad?.DESCRIPCION || 'N/A' }}</span>
        </template>

        <!-- Columna de Subcentro -->
        <template #item.subcentro="{ item }">
          <span>{{ item.subcentro?.DESCRIPCION || 'N/A' }}</span>
        </template>

        <!-- Columna de Código Base -->
        <template #item.codigo_base="{ item }">
          <span>{{ item.codigo_base || 'N/A' }}</span>
        </template>

        <!-- Columna de Estado -->
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
              @click="editarCentroCosto(item)"
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
              tabler-calculator
            </VIcon>
            <p class="text-h6 mt-4">No hay centros de costo registrados</p>
            <p class="text-body-2">Crea tu primer centro de costo haciendo clic en "Nuevo Centro de Costo"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear centro de costo -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Centro de Costo</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarCentroCosto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoCentroCosto.DESCRIPCION"
                  label="Descripción del centro de costo"
                  placeholder="Ej: Centro de Costo Administrativo"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-briefcase"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <!-- Select de Unidad con búsqueda visible -->
                <VAutocomplete
                  v-model="nuevoCentroCosto.COD_UNIDAD"
                  label="Unidad *"
                  :items="unidades"
                  item-title="DESCRIPCION"
                  item-value="COD_UNIDAD"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building"
                  :loading="cargandoDependencias"
                  clearable
                  required
                  placeholder="Escriba para buscar..."
                  :filter="filtrarUnidades"
                >
                  <template #no-data>
                    <VListItem>
                      <VListItemTitle>
                        No se encontraron unidades que coincidan con "{{ busquedaUnidad }}"
                      </VListItemTitle>
                    </VListItem>
                  </template>
                  <template #item="{ props, item }">
                    <VListItem v-bind="props">
                      <template #title>
                        <span>{{ item.raw.DESCRIPCION }}</span>
                      </template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <VCol cols="12" md="6">
                <!-- Select de Subcentro con búsqueda visible -->
                <VAutocomplete
                  v-model="nuevoCentroCosto.COD_SUBCENTRO"
                  label="Subcentro *"
                  :items="subcentros"
                  item-title="DESCRIPCION"
                  item-value="COD_SUBCENTRO"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building-community"
                  :loading="cargandoDependencias"
                  clearable
                  required
                  placeholder="Escriba para buscar..."
                  :filter="filtrarSubcentros"
                >
                  <template #no-data>
                    <VListItem>
                      <VListItemTitle>
                        No se encontraron subcentros que coincidan con "{{ busquedaSubcentro }}"
                      </VListItemTitle>
                    </VListItem>
                  </template>
                  <template #item="{ props, item }">
                    <VListItem v-bind="props">
                      <template #title>
                        <span>{{ item.raw.DESCRIPCION }}</span>
                      </template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="nuevoCentroCosto.codigo_base"
                  label="Código Base (Opcional)"
                  placeholder="Ej: 1001"
                  type="number"
                  prepend-inner-icon="tabler-number"
                  :min="0"
                />
              </VCol>
            </VRow>
            <small class="text-caption text-disabled">* Campos obligatorios</small>
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
            @click="guardarCentroCosto"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar centro de costo -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="600px"
      persistent
    >
      <VCard v-if="centroCostoEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Centro de Costo</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarCentroCosto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="centroCostoEditando.DESCRIPCION"
                  label="Descripción del centro de costo"
                  :rules="[reglas.requerido, reglas.max50]"
                  prepend-inner-icon="tabler-briefcase"
                  required
                />
              </VCol>

              <VCol cols="12" md="6">
                <!-- Select de Unidad con búsqueda visible -->
                <VAutocomplete
                  v-model="centroCostoEditando.COD_UNIDAD"
                  label="Unidad *"
                  :items="unidades"
                  item-title="DESCRIPCION"
                  item-value="COD_UNIDAD"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building"
                  :loading="cargandoDependencias"
                  clearable
                  required
                  placeholder="Escriba para buscar..."
                  :filter="filtrarUnidades"
                >
                  <template #no-data>
                    <VListItem>
                      <VListItemTitle>
                        No se encontraron unidades que coincidan con "{{ busquedaUnidad }}"
                      </VListItemTitle>
                    </VListItem>
                  </template>
                  <template #item="{ props, item }">
                    <VListItem v-bind="props">
                      <template #title>
                        <span>{{ item.raw.DESCRIPCION }}</span>
                      </template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <VCol cols="12" md="6">
                <!-- Select de Subcentro con búsqueda visible -->
                <VAutocomplete
                  v-model="centroCostoEditando.COD_SUBCENTRO"
                  label="Subcentro *"
                  :items="subcentros"
                  item-title="DESCRIPCION"
                  item-value="COD_SUBCENTRO"
                  :rules="[reglas.requerido]"
                  prepend-inner-icon="tabler-building-community"
                  :loading="cargandoDependencias"
                  clearable
                  required
                  placeholder="Escriba para buscar..."
                  :filter="filtrarSubcentros"
                >
                  <template #no-data>
                    <VListItem>
                      <VListItemTitle>
                        No se encontraron subcentros que coincidan con "{{ busquedaSubcentro }}"
                      </VListItemTitle>
                    </VListItem>
                  </template>
                  <template #item="{ props, item }">
                    <VListItem v-bind="props">
                      <template #title>
                        <span>{{ item.raw.DESCRIPCION }}</span>
                      </template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="centroCostoEditando.codigo_base"
                  label="Código Base (Opcional)"
                  type="number"
                  prepend-inner-icon="tabler-number"
                  :min="0"
                />
              </VCol>
            </VRow>
            <small class="text-caption text-disabled">* Campos obligatorios</small>
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
            @click="actualizarCentroCosto"
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
            Eliminar Centro de Costo
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el centro de costo 
            <strong>"{{ centroCostoConfirmandoEliminar?.DESCRIPCION }}"</strong>?
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
              @click="confirmarEliminarCentroCosto"
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
    section: 'parametros-centros-costo',
    action: 'read',
    subject: 'parametros-centros-costo',
  },
})

const notificationStore = useNotificationStore()

// Estados
const cargando = ref(false)
const cargandoDependencias = ref(false)
const guardando = ref(false)
const procesandoEliminar = ref(false)
const busqueda = ref('')
const busquedaUnidad = ref('')
const busquedaSubcentro = ref('')
const modalCrearVisible = ref(false)
const modalEditarVisible = ref(false)
const modalConfirmacionEliminarVisible = ref(false)

// Datos
const centrosCosto = ref([])
const unidades = ref([])
const subcentros = ref([])
const centroCostoEditando = ref(null)
const centroCostoConfirmandoEliminar = ref(null)

// Formulario nuevo centro de costo
const nuevoCentroCosto = ref({
  DESCRIPCION: '',
  COD_UNIDAD: null,
  COD_SUBCENTRO: null,
  codigo_base: null,
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Descripción', key: 'DESCRIPCION', sortable: true },
  { title: 'Unidad', key: 'unidad', sortable: true },
  { title: 'Subcentro', key: 'subcentro', sortable: true },
  { title: 'Código Base', key: 'codigo_base', sortable: true },
  { title: 'Estado', key: 'ACTIVO', sortable: false },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  max50: value => (value || '').length <= 50 || 'Máximo 50 caracteres',
}

// Filtrar centros de costo
const centrosCostoFiltrados = computed(() => {
  if (!busqueda.value) return centrosCosto.value

  const busquedaLower = busqueda.value.toLowerCase()
  return centrosCosto.value.filter(centro => {
    return (
      centro.DESCRIPCION.toLowerCase().includes(busquedaLower) ||
      (centro.codigo_base && centro.codigo_base.toString().includes(busquedaLower)) ||
      (centro.unidad?.DESCRIPCION?.toLowerCase().includes(busquedaLower) || false) ||
      (centro.subcentro?.DESCRIPCION?.toLowerCase().includes(busquedaLower) || false)
    )
  })
})

// Funciones de filtrado para los autocomplete
const filtrarUnidades = (item, queryText, itemText) => {
  const searchText = queryText.toLowerCase()
  const descripcion = item.raw.DESCRIPCION.toLowerCase()
  
  // Guardar la búsqueda actual
  busquedaUnidad.value = queryText
  
  return descripcion.includes(searchText)
}

const filtrarSubcentros = (item, queryText, itemText) => {
  const searchText = queryText.toLowerCase()
  const descripcion = item.raw.DESCRIPCION.toLowerCase()
  
  // Guardar la búsqueda actual
  busquedaSubcentro.value = queryText
  
  return descripcion.includes(searchText)
}

// Métodos
const cargarCentrosCosto = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/centros-costo')
    centrosCosto.value = response.data
  } catch (error) {
    console.error('Error al cargar centros de costo:', error)
    notificationStore.addNotification('Error al cargar los centros de costo', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarDependencias = async () => {
  try {
    cargandoDependencias.value = true
    const response = await axios.get('/api/parametros/centros-costo/dependencias')
    unidades.value = response.data.unidades
    subcentros.value = response.data.subcentros
  } catch (error) {
    console.error('Error al cargar dependencias:', error)
    notificationStore.addNotification('Error al cargar unidades y subcentros', 'error')
  } finally {
    cargandoDependencias.value = false
  }
}

const mostrarModalCrear = () => {
  modalCrearVisible.value = true
}

const cerrarModalCrear = () => {
  modalCrearVisible.value = false
  resetearFormularioCrear()
  // Limpiar búsquedas
  busquedaUnidad.value = ''
  busquedaSubcentro.value = ''
}

const guardarCentroCosto = async () => {
  try {
    guardando.value = true
    
    // Preparar datos
    const datos = { ...nuevoCentroCosto.value }
    
    // Convertir valores numéricos
    if (datos.codigo_base) datos.codigo_base = parseInt(datos.codigo_base)
    
    const response = await axios.post('/api/parametros/centros-costo', datos)
    
    // Agregar el nuevo centro de costo a la lista
    centrosCosto.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    busquedaUnidad.value = ''
    busquedaSubcentro.value = ''
    
    // Mostrar notificación
    notificationStore.addNotification(`Centro de costo "${response.data.DESCRIPCION}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear centro de costo:', error)
    const errorMessage = error.response?.data?.message || 'Error al crear el centro de costo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarCentroCosto = (centro) => {
  // Crear copia del objeto
  centroCostoEditando.value = {
    ...centro,
    COD_UNIDAD: centro.COD_UNIDAD,
    COD_SUBCENTRO: centro.COD_SUBCENTRO,
  }
  modalEditarVisible.value = true
}

const actualizarCentroCosto = async () => {
  try {
    guardando.value = true
    const datos = { ...centroCostoEditando.value }
    const id = datos.COD_CENTRO
    
    // Remover el ID y relaciones de los datos a actualizar
    delete datos.COD_CENTRO
    delete datos.unidad
    delete datos.subcentro
    delete datos.COD_CENTRO_BD // NO se puede editar
    
    // Convertir valores numéricos
    if (datos.codigo_base) datos.codigo_base = parseInt(datos.codigo_base)
    
    const response = await axios.put(`/api/parametros/centros-costo/${id}`, datos)
    
    // Actualizar en la lista
    const index = centrosCosto.value.findIndex(c => c.COD_CENTRO === id)
    if (index !== -1) {
      centrosCosto.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Centro de costo "${response.data.DESCRIPCION}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar centro de costo:', error)
    const errorMessage = error.response?.data?.message || 'Error al actualizar el centro de costo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (centro) => {
  centroCostoConfirmandoEliminar.value = { ...centro }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarCentroCosto = async () => {
  try {
    procesandoEliminar.value = true
    const centro = centroCostoConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/centros-costo/${centro.COD_CENTRO}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    centrosCosto.value = centrosCosto.value.filter(c => c.COD_CENTRO !== centro.COD_CENTRO)
    
    notificationStore.addNotification(`Centro de costo "${centro.DESCRIPCION}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    centroCostoConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar centro de costo:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el centro de costo'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoCentroCosto.value = {
    DESCRIPCION: '',
    COD_UNIDAD: null,
    COD_SUBCENTRO: null,
    codigo_base: null,
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarCentrosCosto()
  cargarDependencias()
})
</script>