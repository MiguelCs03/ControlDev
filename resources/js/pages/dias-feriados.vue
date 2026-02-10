<template>
  <div>
    <!-- Header -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">📅 Gestión de Días Feriados</h2>
          <p class="text-body-2 mb-0">Configura los días no laborables para cálculos precisos en reportes</p>
        </div>
      </VCardText>
    </VCard>

    <!-- Controles y acciones rápidas -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VSelect
              v-model="anioSeleccionado"
              :items="aniosDisponibles"
              label="Año"
              prepend-inner-icon="tabler-calendar"
              variant="outlined"
              density="comfortable"
              @update:model-value="cargarFeriados"
            />
          </VCol>
          <VCol cols="12" md="9" class="d-flex align-center gap-2 flex-wrap">
            <VBtn
              color="info"
              prepend-icon="tabler-download"
              @click="importarFeriadosBolivia"
              :loading="importando"
              size="small"
            >
              Importar Feriados Bolivia {{ anioSeleccionado }}
            </VBtn>
            <VAlert
              type="info"
              variant="tonal"
              density="compact"
              class="mb-0"
            >
              Los fines de semana se muestran automáticamente en naranja. Solo marca como feriados los días especiales.
            </VAlert>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Leyenda de colores -->
    <VCard class="mb-4">
      <VCardText>
        <div class="text-subtitle-2 mb-3 font-weight-bold">🎨 Leyenda de Colores</div>
        <div class="d-flex gap-4 flex-wrap">
          <div class="d-flex align-center gap-2">
            <div class="legend-box-new" style="background: #e3f2fd; border: 1px solid #1565c0;"></div>
            <span class="text-body-2 font-weight-medium">Día laborable</span>
          </div>
          <div class="d-flex align-center gap-2">
            <div class="legend-box-new" style="background: #ffe0b2; border: 1px solid #ff9800;"></div>
            <span class="text-body-2 font-weight-medium">Fin de semana (automático)</span>
          </div>
          <div class="d-flex align-center gap-2">
            <div class="legend-box-new" style="background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%); border: 2px solid #d32f2f;"></div>
            <span class="text-body-2 font-weight-medium">Feriado especial</span>
          </div>
          <div class="d-flex align-center gap-2">
            <div class="legend-box-new" style="background: linear-gradient(135deg, #e1bee7 0%, #ce93d8 100%); border: 2px solid #7b1fa2;"></div>
            <span class="text-body-2 font-weight-medium">Feriado recurrente</span>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Calendario anual (12 meses) -->
    <VRow>
      <VCol
        v-for="mes in 12"
        :key="mes"
        cols="12"
        md="6"
        lg="4"
        xl="3"
      >
        <VCard>
          <VCardTitle class="text-center bg-primary text-white">
            {{ nombreMes(mes) }}
          </VCardTitle>
          <VCardText class="pa-2">
            <!-- Días de la semana -->
            <div class="calendar-grid mb-1">
              <div
                v-for="dia in ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']"
                :key="dia"
                class="calendar-header"
              >
                {{ dia }}
              </div>
            </div>
            
            <!-- Días del mes -->
            <div class="calendar-grid">
              <!-- Espacios vacíos antes del primer día -->
              <div
                v-for="n in primerDiaSemana(mes)"
                :key="`empty-${n}`"
                class="calendar-day empty"
              ></div>
              
              <!-- Días del mes -->
              <div
                v-for="dia in diasEnMes(mes)"
                :key="dia"
                :class="claseDia(mes, dia)"
                @click="toggleDia(mes, dia)"
                :title="titulodia(mes, dia)"
              >
                <div class="dia-numero">{{ dia }}</div>
                <div v-if="esFeriado(mes, dia)" class="dia-nombre">
                  {{ nombreFeriado(mes, dia) }}
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Diálogo para editar/crear feriado -->
    <VDialog v-model="dialogo" max-width="500">
      <VCard>
        <VCardTitle class="d-flex align-center gap-2">
          <VIcon icon="tabler-calendar-event" />
          {{ modoEdicion ? 'Editar Feriado' : 'Nuevo Feriado' }}
        </VCardTitle>

        <VCardText>
          <VForm ref="formRef">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="formulario.nombre"
                  label="Nombre del feriado"
                  prepend-inner-icon="tabler-text"
                  variant="outlined"
                  :rules="[v => !!v || 'El nombre es requerido']"
                  required
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="formulario.fecha"
                  label="Fecha"
                  type="date"
                  prepend-inner-icon="tabler-calendar"
                  variant="outlined"
                  :rules="[v => !!v || 'La fecha es requerida']"
                  required
                  readonly
                />
              </VCol>

              <VCol cols="12">
                <VTextarea
                  v-model="formulario.descripcion"
                  label="Descripción (opcional)"
                  prepend-inner-icon="tabler-file-text"
                  variant="outlined"
                  rows="2"
                />
              </VCol>

              <VCol cols="12" md="6">
                <VSwitch
                  v-model="formulario.recurrente"
                  label="Recurrente (cada año)"
                  color="warning"
                  inset
                />
              </VCol>

              <VCol cols="12" md="6">
                <VSwitch
                  v-model="formulario.activo"
                  label="Activo"
                  color="success"
                  inset
                />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>

        <VCardActions>
          <VBtn
            v-if="modoEdicion"
            color="error"
            variant="text"
            @click="eliminarFeriado"
            :loading="eliminando"
          >
            Eliminar
          </VBtn>
          <VSpacer />
          <VBtn
            variant="text"
            @click="cerrarDialogo"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            @click="guardarFeriado"
            :loading="guardando"
          >
            {{ modoEdicion ? 'Actualizar' : 'Crear' }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar para notificaciones -->
    <VSnackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.message }}
    </VSnackbar>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Define metadata para la página
definePage({
  meta: {
    section: 'TicketingReportes',
    action: 'read',
    subject: 'TicketingReportes',
  },
})

// Estado
const feriados = ref([])
const cargando = ref(false)
const guardando = ref(false)
const eliminando = ref(false)
const importando = ref(false)
const marcandoDomingos = ref(false)
const marcandoFinesDeSemana = ref(false)
const dialogo = ref(false)
const modoEdicion = ref(false)
const formRef = ref(null)
const anioSeleccionado = ref(new Date().getFullYear())

// Formulario
const formulario = ref({
  id: null,
  nombre: '',
  fecha: '',
  descripcion: '',
  recurrente: false,
  activo: true,
})

// Snackbar
const snackbar = ref({
  show: false,
  message: '',
  color: 'success',
})

// Años disponibles para filtro
const aniosDisponibles = computed(() => {
  const anioActual = new Date().getFullYear()
  const anios = []
  for (let i = anioActual - 2; i <= anioActual + 5; i++) {
    anios.push(i)
  }
  return anios
})

// Estadísticas del año
const totalFeriados = computed(() => {
  return feriados.value.filter(f => f.activo).length
})

const totalFinesDeSemana = computed(() => {
  let count = 0
  const inicio = new Date(anioSeleccionado.value, 0, 1)
  const fin = new Date(anioSeleccionado.value, 11, 31)
  
  for (let d = new Date(inicio); d <= fin; d.setDate(d.getDate() + 1)) {
    const diaSemana = d.getDay()
    if (diaSemana === 0 || diaSemana === 6) {
      count++
    }
  }
  return count
})

const diasLaborables = computed(() => {
  const totalDias = 365 // Simplificado, podría calcular si es año bisiesto
  return totalDias - totalFinesDeSemana.value - totalFeriados.value
})

// Funciones de calendario
const nombreMes = (mes) => {
  const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ]
  return meses[mes - 1]
}

const diasEnMes = (mes) => {
  return new Date(anioSeleccionado.value, mes, 0).getDate()
}

const primerDiaSemana = (mes) => {
  // getDay() devuelve 0 para Domingo, 1 para Lunes, etc.
  // Queremos que Domingo sea 0, así que no necesitamos ajustar
  return new Date(anioSeleccionado.value, mes - 1, 1).getDay()
}

const esFeriado = (mes, dia) => {
  const fecha = `${anioSeleccionado.value}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
  return feriados.value.some(f => {
    // Normalizar la fecha del feriado a solo YYYY-MM-DD
    const fechaFeriado = f.fecha.split('T')[0]
    return fechaFeriado === fecha && f.activo
  })
}

const nombreFeriado = (mes, dia) => {
  const fecha = `${anioSeleccionado.value}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
  const feriado = feriados.value.find(f => {
    const fechaFeriado = f.fecha.split('T')[0]
    return fechaFeriado === fecha && f.activo
  })
  return feriado ? feriado.nombre : ''
}

const esRecurrente = (mes, dia) => {
  const fecha = `${anioSeleccionado.value}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
  const feriado = feriados.value.find(f => {
    const fechaFeriado = f.fecha.split('T')[0]
    return fechaFeriado === fecha && f.activo
  })
  return feriado ? feriado.recurrente : false
}

const esFinDeSemana = (mes, dia) => {
  const fecha = new Date(anioSeleccionado.value, mes - 1, dia)
  const diaSemana = fecha.getDay()
  return diaSemana === 0 || diaSemana === 6 // Domingo o Sábado
}

const claseDia = (mes, dia) => {
  const clases = ['calendar-day']
  
  if (esFeriado(mes, dia)) {
    if (esRecurrente(mes, dia)) {
      clases.push('feriado-recurrente')
    } else {
      clases.push('feriado')
    }
  } else if (esFinDeSemana(mes, dia)) {
    clases.push('fin-semana')
  }
  
  return clases.join(' ')
}

const titulodia = (mes, dia) => {
  if (esFeriado(mes, dia)) {
    return nombreFeriado(mes, dia)
  } else if (esFinDeSemana(mes, dia)) {
    return 'Fin de semana'
  }
  return 'Clic para marcar como feriado'
}

const toggleDia = (mes, dia) => {
  const fecha = `${anioSeleccionado.value}-${String(mes).padStart(2, '0')}-${String(dia).padStart(2, '0')}`
  const feriadoExistente = feriados.value.find(f => {
    const fechaFeriado = f.fecha.split('T')[0]
    return fechaFeriado === fecha
  })
  
  if (feriadoExistente) {
    // Editar feriado existente
    editarFeriado(feriadoExistente)
  } else {
    // Crear nuevo feriado
    abrirDialogoNuevo(fecha)
  }
}

// Funciones CRUD
const cargarFeriados = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/dias-feriados', {
      params: { 
        anio: anioSeleccionado.value,
        _t: Date.now() // Timestamp para evitar caché
      }
    })
    
    // Filtrar feriados válidos (con fecha y nombre)
    feriados.value = response.data.filter(f => {
      return f.fecha && f.nombre && f.fecha.length > 0
    })
    
    console.log('Feriados cargados:', feriados.value.length)
    console.log('Feriados totales del servidor:', response.data.length)
  } catch (error) {
    console.error('Error al cargar feriados:', error)
    mostrarSnackbar('Error al cargar feriados', 'error')
  } finally {
    cargando.value = false
  }
}

const abrirDialogoNuevo = (fecha = null) => {
  modoEdicion.value = false
  formulario.value = {
    id: null,
    nombre: '',
    fecha: fecha || '',
    descripcion: '',
    recurrente: false,
    activo: true,
  }
  dialogo.value = true
}

const editarFeriado = (feriado) => {
  modoEdicion.value = true
  formulario.value = {
    id: feriado.id,
    nombre: feriado.nombre,
    fecha: feriado.fecha,
    descripcion: feriado.descripcion || '',
    recurrente: feriado.recurrente,
    activo: feriado.activo,
  }
  dialogo.value = true
}

const guardarFeriado = async () => {
  guardando.value = true
  try {
    if (modoEdicion.value) {
      await axios.put(`/api/dias-feriados/${formulario.value.id}`, formulario.value)
      mostrarSnackbar('Feriado actualizado exitosamente', 'success')
    } else {
      await axios.post('/api/dias-feriados', formulario.value)
      mostrarSnackbar('Feriado creado exitosamente', 'success')
    }
    cerrarDialogo()
    cargarFeriados()
  } catch (error) {
    console.error('Error al guardar feriado:', error)
    mostrarSnackbar('Error al guardar feriado', 'error')
  } finally {
    guardando.value = false
  }
}

const eliminarFeriado = async () => {
  console.log('=== ELIMINAR FERIADO ===')
  console.log('formulario.value:', formulario.value)
  console.log('ID a eliminar:', formulario.value.id)
  
  if (!formulario.value.id) {
    mostrarSnackbar('No se puede eliminar: ID no válido', 'error')
    return
  }
  
  const idAEliminar = formulario.value.id
  
  console.log('Enviando DELETE a:', `/api/dias-feriados/${idAEliminar}`)
  
  eliminando.value = true
  try {
    const response = await axios.delete(`/api/dias-feriados/${idAEliminar}`)
    console.log('Respuesta del servidor:', response.data)
    
    // Eliminar del array local inmediatamente
    const feriadosAntes = feriados.value.length
    feriados.value = feriados.value.filter(f => f.id !== idAEliminar)
    console.log(`Feriados antes: ${feriadosAntes}, después: ${feriados.value.length}`)
    
    mostrarSnackbar('Feriado eliminado exitosamente', 'success')
    cerrarDialogo()
    
    // Recargar para asegurar sincronización con el servidor
    await new Promise(resolve => setTimeout(resolve, 300))
    await cargarFeriados()
  } catch (error) {
    console.error('Error al eliminar feriado:', error)
    console.error('Respuesta de error:', error.response?.data)
    mostrarSnackbar(error.response?.data?.message || 'Error al eliminar feriado', 'error')
  } finally {
    eliminando.value = false
  }
}

const importarFeriadosBolivia = async () => {
  importando.value = true
  try {
    const response = await axios.post('/api/importar-feriados-bolivia', {
      anio: anioSeleccionado.value,
    })
    mostrarSnackbar(response.data.message, 'success')
    cargarFeriados()
  } catch (error) {
    console.error('Error al importar feriados:', error)
    mostrarSnackbar('Error al importar feriados', 'error')
  } finally {
    importando.value = false
  }
}

const marcarDomingos = async () => {
  marcandoDomingos.value = true
  try {
    const response = await axios.post('/api/marcar-domingos', {
      anio: anioSeleccionado.value,
    })
    mostrarSnackbar(response.data.message, 'success')
    cargarFeriados()
  } catch (error) {
    console.error('Error al marcar domingos:', error)
    mostrarSnackbar('Error al marcar domingos', 'error')
  } finally {
    marcandoDomingos.value = false
  }
}

const marcarFinesDeSemana = async () => {
  marcandoFinesDeSemana.value = true
  try {
    const response = await axios.post('/api/marcar-fines-de-semana', {
      anio: anioSeleccionado.value,
    })
    mostrarSnackbar(response.data.message, 'success')
    cargarFeriados()
  } catch (error) {
    console.error('Error al marcar fines de semana:', error)
    mostrarSnackbar('Error al marcar fines de semana', 'error')
  } finally {
    marcandoFinesDeSemana.value = false
  }
}

const cerrarDialogo = () => {
  dialogo.value = false
  formulario.value = {
    id: null,
    nombre: '',
    fecha: '',
    descripcion: '',
    recurrente: false,
    activo: true,
  }
}

const mostrarSnackbar = (message, color = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color,
  }
}

// Cargar datos al montar
onMounted(() => {
  cargarFeriados()
})
</script>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}

.calendar-header {
  text-align: center;
  font-weight: bold;
  font-size: 0.7rem;
  padding: 4px 2px;
  color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.1);
  border-radius: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4px 2px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  background-color: #e3f2fd;
  min-height: 45px;
  max-height: 55px;
  position: relative;
  border: 1px solid rgba(0, 0, 0, 0.08);
  font-size: 0.875rem;
}

.calendar-day.empty {
  background-color: transparent;
  cursor: default;
  border: none;
}

.calendar-day:not(.empty):hover {
  transform: scale(1.05);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
  z-index: 1;
}

.calendar-day.fin-semana {
  background-color: #ffe0b2;
  border: 1px solid #ff9800;
}

.calendar-day.feriado {
  background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
  border: 2px solid #d32f2f;
  box-shadow: 0 2px 4px rgba(211, 47, 47, 0.3);
}

.calendar-day.feriado-recurrente {
  background: linear-gradient(135deg, #e1bee7 0%, #ce93d8 100%);
  border: 2px solid #7b1fa2;
  box-shadow: 0 2px 4px rgba(123, 31, 162, 0.3);
}

.dia-numero {
  font-weight: 700;
  font-size: 0.95rem;
  margin-bottom: 1px;
  color: #1565c0;
  line-height: 1.1;
}

.calendar-day.fin-semana .dia-numero {
  color: #bf360c;
  font-weight: 800;
}

.calendar-day.feriado .dia-numero,
.calendar-day.feriado-recurrente .dia-numero {
  color: #ffffff;
  font-weight: 900;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
}

.dia-nombre {
  font-size: 0.55rem;
  text-align: center;
  line-height: 1.1;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  font-weight: 600;
  color: #424242;
}

.calendar-day.feriado .dia-nombre,
.calendar-day.feriado-recurrente .dia-nombre {
  color: #ffffff;
  font-weight: 700;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
}

.legend-box {
  width: 28px;
  height: 28px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.2);
}

.legend-box-new {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  flex-shrink: 0;
}
</style>
