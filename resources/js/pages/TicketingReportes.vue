    <template>
  <div>
    <!-- Header -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">📊 Reportes de Tareas</h2>
          <p class="text-body-2 mb-0">Exporta reportes de tareas en Excel con filtros personalizados</p>
        </div>
        <VBtn
          color="info"
          prepend-icon="tabler-calendar-event"
          variant="elevated"
          @click="$router.push('/dias-feriados')"
        >
          Configurar Feriados
        </VBtn>
      </VCardText>
    </VCard>

    <!-- Filtros y Exportación -->
    <VCard>
      <VCardText>
        <VRow>
          <!-- Filtro de Proyecto -->
          <VCol cols="12" md="4">
            <VSelect
              v-model="filtros.proyecto_id"
              :items="proyectos"
              item-title="nombre_completo"
              item-value="id"
              label="Proyecto"
              prepend-inner-icon="tabler-briefcase"
              clearable
              variant="outlined"
              density="comfortable"
            />
          </VCol>

          <!-- Filtro de Usuario (solo para admin) -->
          <VCol cols="12" md="4">
            <VSelect
              v-model="filtros.usuario_id"
              :items="usuariosFiltrados"
              item-title="name"
              item-value="id"
              label="Usuario / Responsable"
              prepend-inner-icon="tabler-user"
              clearable
              variant="outlined"
              density="comfortable"
              :disabled="cargandoUsuarios"
              :loading="cargandoUsuarios"
              :hint="filtros.proyecto_id ? 'Todos los desarrolladores incluidos por defecto' : 'Selecciona un proyecto primero para ver solo sus desarrolladores'"
              persistent-hint
            />
          </VCol>

          <!-- Rango de fechas -->
          <VCol cols="12" md="3">
            <VTextField
              v-model="filtros.fecha_desde"
              label="Fecha desde"
              type="date"
              prepend-inner-icon="tabler-calendar-event"
              variant="outlined"
              density="comfortable"
              clearable
            />
          </VCol>

          <VCol cols="12" md="3">
            <VTextField
              v-model="filtros.fecha_hasta"
              label="Fecha hasta"
              type="date"
              prepend-inner-icon="tabler-calendar-event"
              variant="outlined"
              density="comfortable"
              clearable
              :min="filtros.fecha_desde || undefined"
            />
          </VCol>

          <!-- Botones de acción -->
          <VCol cols="12" md="2" class="d-flex align-center gap-2">
            <VBtn
              color="primary"
              prepend-icon="tabler-eye"
              size="large"
              @click="cargarVistaPrevia"
              :loading="cargandoPrevia"
              variant="outlined"
              block
            >
              Vista Previa
            </VBtn>
          </VCol>
          <VCol cols="12" md="2" class="d-flex align-center">
            <VBtn
              color="success"
              prepend-icon="tabler-file-spreadsheet"
              size="large"
              @click="exportarReporte"
              :disabled="!hayTareas"
              block
            >
              Exportar
            </VBtn>
          </VCol>
        </VRow>

        <VDivider class="my-4" />

        <!-- Información de ayuda -->
        <VAlert
          color="info"
          variant="tonal"
          icon="tabler-info-circle"
        >
          <VAlertTitle>
            ¿Cómo usar los reportes?
          </VAlertTitle>
          <ul class="mt-2">
            <li><strong>Sin filtros:</strong> Exporta todas las tareas del sistema</li>
            <li><strong>Por proyecto:</strong> Filtra tareas de un proyecto específico (todos los desarrolladores)</li>
            <li><strong>Por proyecto + desarrollador:</strong> Selecciona un proyecto y luego un desarrollador específico</li>
            <li><strong>Por desarrollador:</strong> Selecciona solo un desarrollador para ver todos sus proyectos</li>
            <li><strong>Días laborables:</strong> Los reportes calculan automáticamente días laborables excluyendo fines de semana y feriados configurados</li>
          </ul>
        </VAlert>

        <!-- Información de días no laborables del mes -->
        <VAlert
          v-if="diasNoLaborables.length > 0"
          color="warning"
          variant="tonal"
          icon="tabler-calendar-off"
          class="mt-4"
        >
          <VAlertTitle>
            Días No Laborables del Mes Actual ({{ mesActual }})
          </VAlertTitle>
          <div class="mt-2">
            <VChip
              v-for="dia in diasNoLaborables.slice(0, 10)"
              :key="dia.fecha"
              size="small"
              :color="dia.tipo === 'feriado' ? 'error' : 'default'"
              class="me-2 mb-2"
            >
              {{ formatearFechaCorta(dia.fecha) }} - {{ dia.nombre }}
            </VChip>
            <div v-if="diasNoLaborables.length > 10" class="text-caption mt-2">
              Y {{ diasNoLaborables.length - 10 }} días más...
            </div>
          </div>
        </VAlert>

        <VDivider class="my-4" />

        <!-- Vista previa de tareas -->
        <div v-if="mostrarPrevia">
          <VAlert
            v-if="!hayTareas && tareasPrevia.length === 0"
            type="warning"
            variant="tonal"
            icon="tabler-alert-circle"
            class="mb-4"
          >
            <VAlertTitle>Sin Resultados</VAlertTitle>
            <template v-if="filtros.proyecto_id && filtros.usuario_id">
              Este usuario no tiene actividades realizadas en este proyecto.
            </template>
            <template v-else-if="filtros.proyecto_id">
              No hay tareas registradas en este proyecto.
            </template>
            <template v-else-if="filtros.usuario_id">
              Este usuario no tiene tareas asignadas.
            </template>
            <template v-else>
              No hay tareas en el sistema.
            </template>
          </VAlert>

          <VCard v-if="hayTareas" class="mb-4">
            <VCardTitle class="d-flex align-center justify-space-between">
              <span>📋 Vista Previa de Tareas ({{ tareasPrevia.length }} tareas)</span>
              <VChip color="primary" size="small">
                {{ nombreFiltroActual }}
              </VChip>
            </VCardTitle>
            <VDivider />
            
            <!-- Vista agrupada por USUARIO (cuando se selecciona proyecto sin usuario) -->
            <VCardText v-if="modoVisualizacion === 'por_usuario'" class="pa-0">
              <VExpansionPanels>
                <VExpansionPanel
                  v-for="(grupo, index) in tareasPorUsuario"
                  :key="index"
                >
                  <VExpansionPanelTitle>
                    <div class="d-flex align-center justify-space-between w-100 pe-4">
                      <div class="d-flex align-center gap-2">
                        <VIcon icon="tabler-user" size="20" />
                        <span class="font-weight-medium">{{ grupo.usuario }}</span>
                      </div>
                      <VChip color="info" size="small">
                        {{ grupo.tareas.length }} tareas
                      </VChip>
                    </div>
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <div class="table-responsive">
                      <table class="v-table v-table--density-default">
                        <thead>
                          <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Módulo</th>
                            <th>Vista</th>
                            <th>Cosas Realizadas</th>
                            <th>Estado</th>
                            <th>Nota</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="tarea in grupo.tareas" :key="tarea.id">
                            <td>{{ formatearFecha(tarea.fecha_inicio) }}</td>
                            <td>{{ formatearFecha(tarea.fecha_fin) }}</td>
                            <td>{{ tarea.modulo || '-' }}</td>
                            <td>{{ tarea.vista || '-' }}</td>
                            <td class="text-truncate" style="max-width: 300px;">
                              {{ tarea.descripcion || '-' }}
                            </td>
                            <td>
                              <VChip :color="getEstadoColor(tarea.estado)" size="small">
                                {{ tarea.estado }}
                              </VChip>
                            </td>
                            <td>{{ tarea.nota || '-' }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </VExpansionPanelText>
                </VExpansionPanel>
              </VExpansionPanels>
            </VCardText>

            <!-- Vista agrupada por PROYECTO (cuando se selecciona usuario sin proyecto) -->
            <VCardText v-else-if="modoVisualizacion === 'por_proyecto'" class="pa-0">
              <VExpansionPanels>
                <VExpansionPanel
                  v-for="(grupo, index) in tareasPorProyecto"
                  :key="index"
                >
                  <VExpansionPanelTitle>
                    <div class="d-flex align-center justify-space-between w-100 pe-4">
                      <div class="d-flex align-center gap-2">
                        <VIcon icon="tabler-briefcase" size="20" />
                        <span class="font-weight-medium">{{ grupo.proyecto }}</span>
                      </div>
                      <VChip color="info" size="small">
                        {{ grupo.tareas.length }} tareas
                      </VChip>
                    </div>
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <div class="table-responsive">
                      <table class="v-table v-table--density-default">
                        <thead>
                          <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Módulo</th>
                            <th>Vista</th>
                            <th>Cosas Realizadas</th>
                            <th>Estado</th>
                            <th>Nota</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="tarea in grupo.tareas" :key="tarea.id">
                            <td>{{ formatearFecha(tarea.fecha_inicio) }}</td>
                            <td>{{ formatearFecha(tarea.fecha_fin) }}</td>
                            <td>{{ tarea.modulo || '-' }}</td>
                            <td>{{ tarea.vista || '-' }}</td>
                            <td class="text-truncate" style="max-width: 300px;">
                              {{ tarea.descripcion || '-' }}
                            </td>
                            <td>
                              <VChip :color="getEstadoColor(tarea.estado)" size="small">
                                {{ tarea.estado }}
                              </VChip>
                            </td>
                            <td>{{ tarea.nota || '-' }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </VExpansionPanelText>
                </VExpansionPanel>
              </VExpansionPanels>
            </VCardText>

            <!-- Vista SIMPLE (cuando se seleccionan ambos filtros o ninguno) -->
            <VCardText v-else class="pa-0">
              <div class="table-responsive">
                <table class="v-table v-table--density-default">
                  <thead>
                    <tr>
                      <th>Fecha Inicio</th>
                      <th>Fecha Fin</th>
                      <th>Proyecto</th>
                      <th>Usuario</th>
                      <th>Módulo</th>
                      <th>Vista</th>
                      <th>Cosas Realizadas</th>
                      <th>Estado</th>
                      <th>Nota</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="tarea in tareasPrevia.slice(0, 20)" :key="tarea.id">
                      <td>{{ formatearFecha(tarea.fecha_inicio) }}</td>
                      <td>{{ formatearFecha(tarea.fecha_fin) }}</td>
                      <td>{{ tarea.proyecto?.nombre || '-' }}</td>
                      <td>{{ tarea.responsable?.name || '-' }}</td>
                      <td>{{ tarea.modulo || '-' }}</td>
                      <td>{{ tarea.vista || '-' }}</td>
                      <td class="text-truncate" style="max-width: 300px;">
                        {{ tarea.descripcion || '-' }}
                      </td>
                      <td>
                        <VChip :color="getEstadoColor(tarea.estado)" size="small">
                          {{ tarea.estado }}
                        </VChip>
                      </td>
                      <td>{{ tarea.nota || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <VAlert
                v-if="tareasPrevia.length > 20"
                type="info"
                variant="tonal"
                density="compact"
                class="ma-4"
              >
                Mostrando las primeras 20 tareas. El reporte completo incluirá todas las {{ tareasPrevia.length }} tareas.
              </VAlert>
            </VCardText>
          </VCard>
        </div>

        <VDivider class="my-4" />

        <!-- Resumen de tareas -->
        <div v-if="estadisticas">
          <h3 class="text-h6 mb-3">Resumen General</h3>
          <VRow>
            <VCol cols="12" sm="6" md="3">
              <VCard variant="elevated" color="primary" class="text-white">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption opacity-90">Total</div>
                      <div class="text-h4 font-weight-bold">{{ estadisticas.total }}</div>
                    </div>
                    <VIcon icon="tabler-list" size="40" class="opacity-75" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="elevated" color="warning" class="text-white">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption opacity-90">Pendientes</div>
                      <div class="text-h4 font-weight-bold">{{ estadisticas.pendientes }}</div>
                    </div>
                    <VIcon icon="tabler-clock-pause" size="40" class="opacity-75" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="elevated" color="info" class="text-white">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption opacity-90">En Proceso</div>
                      <div class="text-h4 font-weight-bold">{{ estadisticas.en_proceso }}</div>
                    </div>
                    <VIcon icon="tabler-hourglass" size="40" class="opacity-75" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="elevated" color="success" class="text-white">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption opacity-90">Finalizadas</div>
                      <div class="text-h4 font-weight-bold">{{ estadisticas.finalizadas }}</div>
                    </div>
                    <VIcon icon="tabler-circle-check" size="40" class="opacity-75" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
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
const proyectos = ref([])
const usuarios = ref([])
const usuariosFiltrados = ref([])
const cargandoUsuarios = ref(false)
const estadisticas = ref(null)
const diasNoLaborables = ref([])
const mesActual = ref('')
const filtros = ref({
  proyecto_id: null,
  usuario_id: null,
  fecha_desde: null,
  fecha_hasta: null,
})

// Vista previa
const tareasPrevia = ref([])
const mostrarPrevia = ref(false)
const cargandoPrevia = ref(false)

// Computed properties
const hayTareas = computed(() => tareasPrevia.value.length > 0)

const nombreFiltroActual = computed(() => {
  if (filtros.value.proyecto_id && filtros.value.usuario_id) {
    const proyecto = proyectos.value.find(p => p.id === filtros.value.proyecto_id)
    const usuario = usuariosFiltrados.value.find(u => u.id === filtros.value.usuario_id)
    return `${proyecto?.nombre || 'Proyecto'} - ${usuario?.name || 'Usuario'}`
  } else if (filtros.value.proyecto_id) {
    const proyecto = proyectos.value.find(p => p.id === filtros.value.proyecto_id)
    return `${proyecto?.nombre || 'Proyecto'} (Todos los desarrolladores)`
  } else if (filtros.value.usuario_id) {
    const usuario = usuarios.value.find(u => u.id === filtros.value.usuario_id)
    return `${usuario?.name || 'Usuario'} (Todos los proyectos)`
  }
  return 'Todas las tareas'
})

// Agrupar tareas por usuario (cuando se selecciona proyecto sin usuario)
const tareasPorUsuario = computed(() => {
  if (!filtros.value.proyecto_id || filtros.value.usuario_id) {
    return []
  }
  
  const grupos = {}
  tareasPrevia.value.forEach(tarea => {
    const usuarioId = tarea.responsable?.id || 'sin_asignar'
    const usuarioNombre = tarea.responsable?.name || 'Sin asignar'
    
    if (!grupos[usuarioId]) {
      grupos[usuarioId] = {
        usuario: usuarioNombre,
        tareas: []
      }
    }
    grupos[usuarioId].tareas.push(tarea)
  })
  
  return Object.values(grupos)
})

// Agrupar tareas por proyecto (cuando se selecciona usuario sin proyecto)
const tareasPorProyecto = computed(() => {
  if (!filtros.value.usuario_id || filtros.value.proyecto_id) {
    return []
  }
  
  const grupos = {}
  tareasPrevia.value.forEach(tarea => {
    const proyectoId = tarea.proyecto?.id || 'sin_proyecto'
    const proyectoNombre = tarea.proyecto?.nombre || 'Sin proyecto'
    
    if (!grupos[proyectoId]) {
      grupos[proyectoId] = {
        proyecto: proyectoNombre,
        tareas: []
      }
    }
    grupos[proyectoId].tareas.push(tarea)
  })
  
  return Object.values(grupos)
})

// Determinar el modo de visualización
const modoVisualizacion = computed(() => {
  if (filtros.value.proyecto_id && !filtros.value.usuario_id) {
    return 'por_usuario' // Proyecto seleccionado, mostrar por usuario
  } else if (filtros.value.usuario_id && !filtros.value.proyecto_id) {
    return 'por_proyecto' // Usuario seleccionado, mostrar por proyecto
  }
  return 'simple' // Ambos o ninguno seleccionado
})

// Cargar datos iniciales
onMounted(() => {
  cargarProyectos()
  cargarUsuarios()
  cargarEstadisticas()
  cargarDiasNoLaborables()
})


// Funciones
const cargarProyectos = async () => {
  try {
    const response = await axios.get('/api/proyectos')
    proyectos.value = response.data.map(p => ({
      ...p,
      nombre_completo: `${p.nombre} (${p.cliente.nombre})`
    }))
  } catch (error) {
    console.error('Error al cargar proyectos:', error)
  }
}

const cargarUsuarios = async () => {
  try {
    const response = await axios.get('/api/users')
    usuarios.value = response.data
    usuariosFiltrados.value = response.data
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
  }
}

// Cargar usuarios de un proyecto específico
const cargarUsuariosDelProyecto = async (proyectoId) => {
  if (!proyectoId) {
    usuariosFiltrados.value = usuarios.value
    return
  }
  
  cargandoUsuarios.value = true
  try {
    const response = await axios.get(`/api/proyectos/${proyectoId}/usuarios`)
    // Agregar opción "Todos los desarrolladores" al inicio
    usuariosFiltrados.value = [
      { id: null, name: '\ud83d\udc65 Todos los desarrolladores' },
      ...response.data
    ]
  } catch (error) {
    console.error('Error al cargar usuarios del proyecto:', error)
    usuariosFiltrados.value = [
      { id: null, name: '\ud83d\udc65 Todos los desarrolladores' },
      ...usuarios.value
    ]
  } finally {
    cargandoUsuarios.value = false
  }
}

// Watch para cuando cambia el proyecto seleccionado
watch(() => filtros.value.proyecto_id, (nuevoProyectoId) => {
  // Limpiar el usuario seleccionado cuando cambia el proyecto
  filtros.value.usuario_id = null
  
  if (nuevoProyectoId) {
    cargarUsuariosDelProyecto(nuevoProyectoId)
  } else {
    usuariosFiltrados.value = usuarios.value
  }
})

const cargarEstadisticas = async () => {
  try {
    const response = await axios.get('/api/dashboard-tareas')
    estadisticas.value = {
      total: response.data.todas_las_tareas.total,
      pendientes: response.data.todas_las_tareas.pendientes,
      en_proceso: response.data.todas_las_tareas.en_proceso,
      finalizadas: response.data.todas_las_tareas.finalizadas,
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}


const exportarReporte = async () => {
  const params = {}

  if (filtros.value.proyecto_id) params.proyecto_id = filtros.value.proyecto_id
  if (filtros.value.usuario_id)  params.usuario_id  = filtros.value.usuario_id
  if (filtros.value.fecha_desde) params.fecha_desde = filtros.value.fecha_desde
  if (filtros.value.fecha_hasta) params.fecha_hasta = filtros.value.fecha_hasta

  try {
    const response = await axios.get('/api/exportar/tareas-admin', {
      params,
      responseType: 'blob',
      withCredentials: true,
    })

    // Obtener filename desde headers (si está presente)
    const disposition = response.headers['content-disposition'] || ''
    let filename = 'reporte_tareas.xlsx'
    const match = disposition.match(/filename\*?=([^;]+)/)
    if (match) {
      filename = match[1].replace(/UTF-8''/, '').replace(/"/g, '')
    }

    const blob = new Blob([response.data])
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(downloadUrl)
  } catch (error) {
    console.error('Error exportando reporte:', error)
    if (error.response && error.response.status === 401) {
      // Usuario no autenticado: redirigir a login o mostrar mensaje
      // Ajusta esto según tu flujo de autenticación SPA
      window.location.href = '/login'
    }
  }
}

const cargarDiasNoLaborables = async () => {
  try {
    const now = new Date()
    const mes = now.getMonth() + 1
    const anio = now.getFullYear()
    
    // Actualizar el nombre del mes
    mesActual.value = now.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
    
    const response = await axios.get('/api/dias-no-laborables', {
      params: { mes, anio }
    })
    
    diasNoLaborables.value = response.data.dias_no_laborables || []
  } catch (error) {
    console.error('Error al cargar días no laborables:', error)
  }
}

const formatearFechaCorta = (fecha) => {
  const date = new Date(fecha + 'T00:00:00')
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short'
  })
}

const formatearFecha = (fecha) => {
  if (!fecha) return '-'
  const date = new Date(fecha + 'T00:00:00')
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getEstadoColor = (estado) => {
  const colores = {
    'pendiente': 'warning',
    'en_proceso': 'info',
    'en_revision': 'purple',
    'finalizado': 'success',
    'cancelado': 'error'
  }
  return colores[estado] || 'default'
}

const cargarVistaPrevia = async () => {
  cargandoPrevia.value = true
  mostrarPrevia.value = true
  
  try {
    const params = {
      with: 'proyecto,responsable' // Incluir relaciones
    }
    
    if (filtros.value.proyecto_id) params.proyecto_id   = filtros.value.proyecto_id
    if (filtros.value.usuario_id)  params.responsable_id = filtros.value.usuario_id
    if (filtros.value.fecha_desde) params.fecha_desde    = filtros.value.fecha_desde
    if (filtros.value.fecha_hasta) params.fecha_hasta    = filtros.value.fecha_hasta

    const response = await axios.get('/api/tareas', { params })
    tareasPrevia.value = response.data
  } catch (error) {
    console.error('Error al cargar vista previa:', error)
    tareasPrevia.value = []
  } finally {
    cargandoPrevia.value = false
  }
}
</script>

<style scoped>
.table-responsive {
  overflow-x: auto;
  max-width: 100%;
}

.v-table {
  width: 100%;
  border-collapse: collapse;
}

.v-table thead th {
  background-color: rgb(var(--v-theme-surface));
  font-weight: 600;
  text-align: left;
  padding: 12px 16px;
  border-bottom: 2px solid rgb(var(--v-theme-primary));
  white-space: nowrap;
}

.v-table tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
}

.v-table tbody tr:hover {
  background-color: rgba(var(--v-theme-on-surface), 0.04);
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
