<template>
  <div>
    <!-- Header -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">📊 Reportes de Tareas</h2>
          <p class="text-body-2 mb-0">Exporta reportes de tareas en Excel con filtros personalizados</p>
        </div>
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
              :items="usuarios"
              item-title="name"
              item-value="id"
              label="Usuario / Responsable"
              prepend-inner-icon="tabler-user"
              clearable
              variant="outlined"
              density="comfortable"
            />
          </VCol>

          <!-- Botón de Exportar -->
          <VCol cols="12" md="4" class="d-flex align-center">
            <VBtn
              color="success"
              prepend-icon="tabler-file-spreadsheet"
              block
              size="large"
              @click="exportarReporte"
            >
              Exportar a Excel
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
            <li><strong>Por proyecto:</strong> Filtra tareas de un proyecto específico</li>
            <li><strong>Por usuario:</strong> Filtra tareas asignadas a un usuario</li>
            <li><strong>Combinado:</strong> Usa ambos filtros para reportes más específicos</li>
          </ul>
        </VAlert>

        <VDivider class="my-4" />

        <!-- Resumen de tareas -->
        <div v-if="estadisticas">
          <h3 class="text-h6 mb-3">Resumen General</h3>
          <VRow>
            <VCol cols="12" sm="6" md="3">
              <VCard variant="tonal" color="primary">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption">Total</div>
                      <div class="text-h4">{{ estadisticas.total }}</div>
                    </div>
                    <VIcon icon="tabler-list" size="40" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="tonal" color="warning">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption">Pendientes</div>
                      <div class="text-h4">{{ estadisticas.pendientes }}</div>
                    </div>
                    <VIcon icon="tabler-clock-pause" size="40" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="tonal" color="info">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption">En Proceso</div>
                      <div class="text-h4">{{ estadisticas.en_proceso }}</div>
                    </div>
                    <VIcon icon="tabler-hourglass" size="40" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>

            <VCol cols="12" sm="6" md="3">
              <VCard variant="tonal" color="success">
                <VCardText>
                  <div class="d-flex align-center justify-space-between">
                    <div>
                      <div class="text-caption">Finalizadas</div>
                      <div class="text-h4">{{ estadisticas.finalizadas }}</div>
                    </div>
                    <VIcon icon="tabler-circle-check" size="40" />
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Estado
const proyectos = ref([])
const usuarios = ref([])
const estadisticas = ref(null)
const filtros = ref({
  proyecto_id: null,
  usuario_id: null,
})

// Cargar datos iniciales
onMounted(() => {
  cargarProyectos()
  cargarUsuarios()
  cargarEstadisticas()
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
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
  }
}

const cargarEstadisticas = async () => {
  try {
    const response = await axios.get('/api/dashboard-tareas')
    estadisticas.value = {
      total: response.data.todas_tareas.total,
      pendientes: response.data.todas_tareas.pendientes,
      en_proceso: response.data.todas_tareas.en_proceso,
      finalizadas: response.data.todas_tareas.finalizadas,
    }
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}

const exportarReporte = async () => {
  const params = {}

  if (filtros.value.proyecto_id) {
    params.proyecto_id = filtros.value.proyecto_id
  }

  if (filtros.value.usuario_id) {
    params.usuario_id = filtros.value.usuario_id
  }

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
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
