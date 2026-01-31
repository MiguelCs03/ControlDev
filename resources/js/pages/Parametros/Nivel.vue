<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Niveles</h2>
          <p class="text-body-2 mb-0">Administra los niveles salariales del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar nivel"
            placeholder="Buscar por nombre o categoría..."
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
            Nuevo Nivel
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de niveles -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="nivelesFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de Nombre -->
        <template #item.nombre="{ item }">
          <span class="font-weight-medium">{{ item.nombre }}</span>
        </template>

        <!-- Columna de Categoría -->
        <template #item.categoria="{ item }">
          <VChip
            :color="getColorCategoria(item.categoria)"
            size="small"
          >
            {{ item.categoria }}
          </VChip>
        </template>

        <!-- Columna de 80% -->
        <template #item.ochenta="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="grey" />
            <span class="font-weight-medium">{{ formatoMoneda(item.ochenta) }}</span>
          </div>
        </template>

        <!-- Columna de 90% -->
        <template #item.noventa="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="grey" />
            <span class="font-weight-medium">{{ formatoMoneda(item.noventa) }}</span>
          </div>
        </template>

        <!-- Columna de Midpoint -->
        <template #item.midpoint="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="primary" />
            <span class="font-weight-medium text-primary">{{ formatoMoneda(item.midpoint) }}</span>
          </div>
        </template>

        <!-- Columna de 110% -->
        <template #item.ciento_diez="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="grey" />
            <span class="font-weight-medium">{{ formatoMoneda(item.ciento_diez) }}</span>
          </div>
        </template>

        <!-- Columna de 120% -->
        <template #item.ciento_veinte="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="grey" />
            <span class="font-weight-medium">{{ formatoMoneda(item.ciento_veinte) }}</span>
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
              @click="editarNivel(item)"
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
              tabler-chart-bar
            </VIcon>
            <p class="text-h6 mt-4">No hay niveles registrados</p>
            <p class="text-body-2">Crea tu primer nivel haciendo clic en "Nuevo Nivel"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear nivel -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="500px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Nivel</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarNivel">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoNivel.nombre"
                  label="Nombre del Nivel *"
                  placeholder="Ej: Junior Developer"
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-tag"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="nuevoNivel.categoria"
                  label="Categoría *"
                  placeholder="Ej: Desarrollo, Diseño, Administración"
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-category"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="nuevoNivel.midpoint"
                  label="Midpoint Salarial ($) *"
                  placeholder="Ej: 35000"
                  type="number"
                  :rules="[reglas.requerido, reglas.minimo]"
                  prepend-inner-icon="tabler-currency-dollar"
                  required
                  :min="0"
                >
                  <template #prepend>
                    <span class="text-disabled">$</span>
                  </template>
                </VTextField>
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
            @click="guardarNivel"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar nivel -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="500px"
      persistent
    >
      <VCard v-if="nivelEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Nivel</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarNivel">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nivelEditando.nombre"
                  label="Nombre del Nivel *"
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-tag"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="nivelEditando.categoria"
                  label="Categoría *"
                  :rules="[reglas.requerido, reglas.max100]"
                  prepend-inner-icon="tabler-category"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="nivelEditando.midpoint"
                  label="Midpoint Salarial ($) *"
                  type="number"
                  :rules="[reglas.requerido, reglas.minimo]"
                  prepend-inner-icon="tabler-currency-dollar"
                  required
                  :min="0"
                >
                  <template #prepend>
                    <span class="text-disabled">$</span>
                  </template>
                </VTextField>
              </VCol>

              <VCol cols="12">
                <VAlert
                  color="info"
                  variant="tonal"
                  class="mb-4"
                >
                  <VAlertTitle>Valores calculados</VAlertTitle>
                  <div class="text-caption">
                    <div>80%: ${{ formatoMoneda(calcularPorcentaje(nivelEditando.midpoint, 0.80)) }}</div>
                    <div>90%: ${{ formatoMoneda(calcularPorcentaje(nivelEditando.midpoint, 0.90)) }}</div>
                    <div>110%: ${{ formatoMoneda(calcularPorcentaje(nivelEditando.midpoint, 1.10)) }}</div>
                    <div>120%: ${{ formatoMoneda(calcularPorcentaje(nivelEditando.midpoint, 1.20)) }}</div>
                  </div>
                </VAlert>
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
            @click="actualizarNivel"
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
            Eliminar Nivel
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el nivel 
            <strong>"{{ nivelConfirmandoEliminar?.nombre }}"</strong>?
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
              @click="confirmarEliminarNivel"
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
    section: 'parametros-niveles',
    action: 'read',
    subject: 'parametros-niveles',
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
const niveles = ref([])
const nivelEditando = ref(null)
const nivelConfirmandoEliminar = ref(null)

// Formulario nuevo nivel
const nuevoNivel = ref({
  nombre: '',
  categoria: '',
  midpoint: null,
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Nivel', key: 'nombre', sortable: true },
  { title: 'Categoría', key: 'categoria', sortable: true },
  { title: '80%', key: 'ochenta', sortable: true },
  { title: '90%', key: 'noventa', sortable: true },
  { title: 'Midpoint', key: 'midpoint', sortable: true },
  { title: '110%', key: 'ciento_diez', sortable: true },
  { title: '120%', key: 'ciento_veinte', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]
// Reglas de validación
const reglas = {
  requerido: value => !!value || 'Campo requerido',
  max100: value => (value || '').length <= 100 || 'Máximo 100 caracteres',
  minimo: value => (value || 0) >= 0 || 'Debe ser mayor o igual a 0',
}

// Computed properties
const nivelesFiltrados = computed(() => {
  if (!busqueda.value) return niveles.value

  const busquedaLower = busqueda.value.toLowerCase()
  return niveles.value.filter(nivel => {
    return (
      nivel.nombre.toLowerCase().includes(busquedaLower) ||
      nivel.categoria.toLowerCase().includes(busquedaLower)
    )
  })
})

// Métodos
const formatoMoneda = (valor) => {
  return parseInt(valor || 0).toLocaleString('es-ES')
}

const calcularPorcentaje = (midpoint, porcentaje) => {
  const valor = parseInt(midpoint || 0)
  return Math.round(valor * porcentaje)
}

const getColorCategoria = (categoria) => {
  const colores = {
    'Desarrollo': 'primary',
    'Diseño': 'info',
    'Administración': 'success',
    'Ventas': 'warning',
    'Marketing': 'pink',
    'Soporte': 'teal',
    'Gerencia': 'purple',
  }
  return colores[categoria] || 'grey'
}

const cargarNiveles = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/niveles')
    niveles.value = response.data
  } catch (error) {
    console.error('Error al cargar niveles:', error)
    notificationStore.addNotification('Error al cargar los niveles', 'error')
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

const guardarNivel = async () => {
  try {
    guardando.value = true
    
    // Preparar datos
    const datos = { 
      ...nuevoNivel.value,
      midpoint: parseInt(nuevoNivel.value.midpoint) || 0
    }
    
    const response = await axios.post('/api/parametros/niveles', datos)
    
    // Agregar cálculos al nuevo nivel para mostrarlo inmediatamente
    const nuevoNivelConCalculos = {
      ...response.data,
      ochenta: calcularPorcentaje(response.data.midpoint, 0.80),
      noventa: calcularPorcentaje(response.data.midpoint, 0.90),
      ciento_diez: calcularPorcentaje(response.data.midpoint, 1.10),
      ciento_veinte: calcularPorcentaje(response.data.midpoint, 1.20)
    }
    
    niveles.value.push(nuevoNivelConCalculos)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Nivel "${response.data.nombre}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear nivel:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al crear el nivel'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarNivel = (nivel) => {
  // Crear copia del objeto
  nivelEditando.value = { ...nivel }
  modalEditarVisible.value = true
}

const actualizarNivel = async () => {
  try {
    guardando.value = true
    
    const datos = { ...nivelEditando.value }
    const id = datos.id
    
    // Remover campos calculados y otros campos no editables
    delete datos.id
    delete datos.ochenta
    delete datos.noventa
    delete datos.ciento_diez
    delete datos.ciento_veinte
    delete datos.creado_por
    delete datos.creado_el
    delete datos.modificado_por
    delete datos.modificado_el
    
    // Asegurar que midpoint sea número
    datos.midpoint = parseInt(datos.midpoint) || 0
    
    const response = await axios.put(`/api/parametros/niveles/${id}`, datos)
    
    // Recalcular valores para actualizar en la lista
    const nivelActualizado = {
      ...response.data,
      ochenta: calcularPorcentaje(response.data.midpoint, 0.80),
      noventa: calcularPorcentaje(response.data.midpoint, 0.90),
      ciento_diez: calcularPorcentaje(response.data.midpoint, 1.10),
      ciento_veinte: calcularPorcentaje(response.data.midpoint, 1.20)
    }
    
    // Actualizar en la lista
    const index = niveles.value.findIndex(n => n.id === id)
    if (index !== -1) {
      niveles.value[index] = nivelActualizado
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Nivel "${response.data.nombre}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar nivel:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al actualizar el nivel'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (nivel) => {
  nivelConfirmandoEliminar.value = { ...nivel }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarNivel = async () => {
  try {
    procesandoEliminar.value = true
    const nivel = nivelConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/niveles/${nivel.id}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    niveles.value = niveles.value.filter(n => n.id !== nivel.id)
    
    notificationStore.addNotification(`Nivel "${nivel.nombre}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    nivelConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar nivel:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el nivel'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoNivel.value = {
    nombre: '',
    categoria: '',
    midpoint: null,
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarNiveles()
})
</script>