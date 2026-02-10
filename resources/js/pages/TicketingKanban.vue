<template>
  <div>
    <!-- Header con estadísticas -->
    <VCard class="mb-4">
      <VCardText>
        <div class="d-flex justify-space-between align-center mb-4">
          <div>
            <h2 class="text-h5 mb-1">
              <VIcon icon="tabler-layout-kanban" class="me-2" />
              Sistema de Ticketing
            </h2>
            <p class="text-body-2 mb-0">Gestión de tareas y proyectos tipo Jira</p>
          </div>

          <div class="d-flex gap-2">
            <VBtn
              color="secondary"
              variant="outlined"
              prepend-icon="tabler-refresh"
              @click="cargarDatos"
            >
              Actualizar
            </VBtn>
            <VBtn
              color="primary"
              variant="tonal"
              :prepend-icon="filtroMisTareas ? 'tabler-filter-off' : 'tabler-filter'"
              @click="toggleMisTareas"
            >
              {{ filtroMisTareas ? 'Ver Todas' : 'Mis Tareas' }}
            </VBtn>
            <VBtn
              v-if="$can('create', 'TicketingKanban')"
              color="success"
              prepend-icon="tabler-plus"
              @click="mostrarModalNuevaTarea"
            >
              Nueva Tarea
            </VBtn>
          </div>
        </div>

        <!-- Estadísticas -->
        <VRow v-if="estadisticas">
          <VCol cols="12" sm="6" md="3">
            <VCard variant="outlined" color="warning" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">Mis Tareas Activas</div>
                    <div class="text-h4 font-weight-bold text-warning">
                      {{ estadisticas.mis_tareas.pendientes + estadisticas.mis_tareas.en_proceso + estadisticas.mis_tareas.en_revision }}
                    </div>
                  </div>
                  <VIcon icon="tabler-activity" size="40" color="warning" class="opacity-50" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="outlined" color="primary" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">En Proceso</div>
                    <div class="text-h4 font-weight-bold text-primary">{{ estadisticas.mis_tareas.en_proceso }}</div>
                  </div>
                  <VIcon icon="tabler-clock-play" size="40" color="primary" class="opacity-50" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="outlined" color="purple" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">En Revisión</div>
                    <div class="text-h4 font-weight-bold text-purple">{{ estadisticas.mis_tareas.en_revision }}</div>
                  </div>
                  <VIcon icon="tabler-eye-check" size="40" color="purple" class="opacity-50" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="outlined" color="success" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">Finalizadas</div>
                    <div class="text-h4 font-weight-bold text-success">{{ estadisticas.mis_tareas.finalizadas }}</div>
                  </div>
                  <VIcon icon="tabler-circle-check" size="40" color="success" class="opacity-50" />
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Filtros -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol cols="12" md="3">
            <VSelect
              v-model="filtroProyecto"
              :items="proyectos"
              item-title="nombre_completo"
              item-value="id"
              label="Proyecto"
              prepend-inner-icon="tabler-folder"
              clearable
              density="comfortable"
              hide-details
              @update:model-value="cargarKanban"
            />
          </VCol>

          <VCol cols="12" md="3">
            <VSelect
              v-model="filtroResponsable"
              :items="usuarios"
              item-title="name"
              item-value="id"
              label="Responsable"
              prepend-inner-icon="tabler-user"
              clearable
              density="comfortable"
              hide-details
              @update:model-value="cargarKanban"
            />
          </VCol>

          <VCol cols="12" md="3">
            <VSelect
              v-model="filtroPrioridad"
              :items="prioridades"
              label="Prioridad"
              prepend-inner-icon="tabler-flag"
              clearable
              density="comfortable"
              hide-details
            />
          </VCol>

          <VCol cols="12" md="3">
            <VTextField
              v-model="busqueda"
              label="Buscar tareas"
              placeholder="Buscar por título..."
              prepend-inner-icon="tabler-search"
              clearable
              density="comfortable"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Kanban Board -->
    <div class="kanban-container">
      <VRow>
        <!-- Columna Pendiente -->
        <VCol cols="12" md="3">
          <VCard class="kanban-column" color="warning" variant="outlined">
            <VCardTitle class="d-flex justify-space-between align-center pa-4">
              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-clock-pause" />
                <span>Pendiente</span>
              </div>
              <VChip color="warning" size="small">{{ tareasFiltradas.pendiente?.length || 0 }}</VChip>
            </VCardTitle>

            <VCardText class="kanban-tasks">
              <VCard
                v-for="tarea in tareasFiltradas.pendiente"
                :key="tarea.id"
                class="tarea-card mb-3"
                :class="`prioridad-${tarea.prioridad}`"
                @click="verDetalleTarea(tarea)"
              >
                <VCardText>
                  <div class="d-flex justify-space-between align-start mb-2">
                    <VChip
                      :color="getColorPrioridad(tarea.prioridad)"
                      size="x-small"
                      class="text-uppercase"
                    >
                      {{ tarea.prioridad }}
                    </VChip>
                  </div>

                  <h4 class="text-subtitle-1 font-weight-bold mb-2">{{ tarea.titulo }}</h4>

                  <p v-if="tarea.descripcion" class="text-caption text-medium-emphasis mb-3 tarea-descripcion">
                    {{ tarea.descripcion }}
                  </p>

                  <VCard variant="tonal" class="mb-3">
                    <VCardText class="py-2">
                      <div class="text-caption">
                        <strong>{{ tarea.proyecto.cliente.nombre }}</strong> · {{ tarea.proyecto.nombre }}
                      </div>
                    </VCardText>
                  </VCard>

                  <div class="d-flex justify-space-between align-center mb-2">
                    <div class="d-flex align-center gap-2">
                      <VAvatar size="28" color="warning">
                        <span class="text-caption">{{ getIniciales(tarea.responsable?.name || 'Sin asignar') }}</span>
                      </VAvatar>
                      <span class="text-caption">{{ tarea.responsable?.name || 'Sin asignar' }}</span>
                    </div>

                    <div class="text-caption text-medium-emphasis">
                      <VIcon icon="tabler-calendar" size="16" />
                      {{ formatearFecha(tarea.created_at) }}
                    </div>
                  </div>

                  <VBtn
                    block
                    size="small"
                    color="primary"
                    variant="tonal"
                    prepend-icon="tabler-player-play"
                    @click.stop="cambiarEstado(tarea.id, 'en_proceso')"
                  >
                    Iniciar
                  </VBtn>
                  
                  <VBtn
                    v-if="!tarea.responsable_id"
                    block
                    size="small"
                    color="info"
                    variant="tonal"
                    prepend-icon="tabler-user-plus"
                    class="mt-2"
                    @click.stop="asignarmeTarea(tarea)"
                  >
                    Asignarme
                  </VBtn>
                </VCardText>
              </VCard>

              <div v-if="!tareasFiltradas.pendiente?.length" class="text-center pa-6">
                <VIcon icon="tabler-inbox" size="48" class="text-medium-emphasis mb-2" />
                <p class="text-caption text-medium-emphasis">No hay tareas pendientes</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Columna En Proceso -->
        <VCol cols="12" md="3">
          <VCard class="kanban-column" color="primary" variant="outlined">
            <VCardTitle class="d-flex justify-space-between align-center pa-4">
              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-clock-play" />
                <span>En Proceso</span>
              </div>
              <VChip color="primary" size="small">{{ tareasFiltradas.en_proceso?.length || 0 }}</VChip>
            </VCardTitle>

            <VCardText class="kanban-tasks">
              <VCard
                v-for="tarea in tareasFiltradas.en_proceso"
                :key="tarea.id"
                class="tarea-card mb-3"
                :class="`prioridad-${tarea.prioridad}`"
                @click="verDetalleTarea(tarea)"
              >
                <VCardText>
                  <div class="d-flex justify-space-between align-start mb-2">
                    <VChip
                      :color="getColorPrioridad(tarea.prioridad)"
                      size="x-small"
                      class="text-uppercase"
                    >
                      {{ tarea.prioridad }}
                    </VChip>
                  </div>

                  <h4 class="text-subtitle-1 font-weight-bold mb-2">{{ tarea.titulo }}</h4>

                  <p v-if="tarea.descripcion" class="text-caption text-medium-emphasis mb-3 tarea-descripcion">
                    {{ tarea.descripcion }}
                  </p>

                  <VCard variant="tonal" class="mb-3">
                    <VCardText class="py-2">
                      <div class="text-caption">
                        <strong>{{ tarea.proyecto.cliente.nombre }}</strong> · {{ tarea.proyecto.nombre }}
                      </div>
                    </VCardText>
                  </VCard>

                  <div v-if="tarea.horas_estimadas || tarea.tiempo_total_trabajado" class="mb-2">
                    <div class="text-caption text-medium-emphasis d-flex align-center gap-1">
                      <VIcon icon="tabler-clock" size="16" />
                      <span v-if="tarea.horas_estimadas">{{ tarea.horas_estimadas }}h estimadas</span>
                      <span v-if="tarea.tiempo_total_trabajado"> · {{ tarea.tiempo_total_trabajado }}h trabajadas</span>
                    </div>
                  </div>

                  <div class="d-flex justify-space-between align-center mb-2">
                    <div class="d-flex align-center gap-2">
                      <VAvatar size="28" color="primary">
                        <span class="text-caption">{{ getIniciales(tarea.responsable?.name || 'Sin asignar') }}</span>
                      </VAvatar>
                      <span class="text-caption">{{ tarea.responsable?.name || 'Sin asignar' }}</span>
                    </div>

                    <div class="text-caption text-medium-emphasis">
                      <VIcon icon="tabler-calendar" size="16" />
                      {{ formatearFecha(tarea.fecha_inicio_trabajo) }}
                    </div>
                  </div>

                  <VBtn
                    block
                    size="small"
                    color="purple"
                    variant="tonal"
                    prepend-icon="tabler-eye-check"
                    @click.stop="cambiarEstado(tarea.id, 'en_revision')"
                  >
                    A Revisión
                  </VBtn>
                </VCardText>
              </VCard>

              <div v-if="!tareasFiltradas.en_proceso?.length" class="text-center pa-6">
                <VIcon icon="tabler-inbox" size="48" class="text-medium-emphasis mb-2" />
                <p class="text-caption text-medium-emphasis">No hay tareas en proceso</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Columna En Revisión -->
        <VCol cols="12" md="3">
          <VCard class="kanban-column" color="purple" variant="outlined">
            <VCardTitle class="d-flex justify-space-between align-center pa-4">
              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-eye-check" />
                <span>En Revisión</span>
              </div>
              <VChip color="purple" size="small">{{ tareasFiltradas.en_revision?.length || 0 }}</VChip>
            </VCardTitle>

            <VCardText class="kanban-tasks">
              <VCard
                v-for="tarea in tareasFiltradas.en_revision"
                :key="tarea.id"
                class="tarea-card mb-3"
                :class="`prioridad-${tarea.prioridad}`"
                @click="verDetalleTarea(tarea)"
              >
                <VCardText>
                  <div class="d-flex justify-space-between align-start mb-2">
                    <VChip
                      :color="getColorPrioridad(tarea.prioridad)"
                      size="x-small"
                      class="text-uppercase"
                    >
                      {{ tarea.prioridad }}
                    </VChip>
                  </div>

                  <h4 class="text-subtitle-1 font-weight-bold mb-2">{{ tarea.titulo }}</h4>

                  <p v-if="tarea.descripcion" class="text-caption text-medium-emphasis mb-3 tarea-descripcion">
                    {{ tarea.descripcion }}
                  </p>

                  <VCard variant="tonal" class="mb-3">
                    <VCardText class="py-2">
                      <div class="text-caption">
                        <strong>{{ tarea.proyecto.cliente.nombre }}</strong> · {{ tarea.proyecto.nombre }}
                      </div>
                    </VCardText>
                  </VCard>

                  <div class="d-flex justify-space-between align-center mb-2">
                    <div class="d-flex align-center gap-2">
                      <VAvatar size="28" color="purple">
                        <span class="text-caption">{{ getIniciales(tarea.responsable?.name || 'Sin asignar') }}</span>
                      </VAvatar>
                      <span class="text-caption">{{ tarea.responsable?.name || 'Sin asignar' }}</span>
                    </div>

                    <div class="text-caption text-medium-emphasis">
                      <VIcon icon="tabler-calendar" size="16" />
                      {{ formatearFecha(tarea.updated_at) }}
                    </div>
                  </div>

                  <VBtn
                    block
                    size="small"
                    color="success"
                    variant="tonal"
                    prepend-icon="tabler-circle-check"
                    @click.stop="cambiarEstado(tarea.id, 'finalizado')"
                  >
                    Finalizar
                  </VBtn>
                </VCardText>
              </VCard>

              <div v-if="!tareasFiltradas.en_revision?.length" class="text-center pa-6">
                <VIcon icon="tabler-inbox" size="48" class="text-medium-emphasis mb-2" />
                <p class="text-caption text-medium-emphasis">No hay tareas en revisión</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Columna Finalizado -->
        <VCol cols="12" md="3">
          <VCard class="kanban-column" color="success" variant="outlined">
            <VCardTitle class="d-flex justify-space-between align-center pa-4">
              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-circle-check" />
                <span>Finalizado</span>
              </div>
              <VChip color="success" size="small">{{ tareasFiltradas.finalizado?.length || 0 }}</VChip>
            </VCardTitle>

            <VCardText class="kanban-tasks">
              <VCard
                v-for="tarea in tareasFiltradas.finalizado"
                :key="tarea.id"
                class="tarea-card mb-3"
                :class="`prioridad-${tarea.prioridad}`"
                @click="verDetalleTarea(tarea)"
              >
                <VCardText>
                  <div class="d-flex justify-space-between align-start mb-2">
                    <VChip
                      :color="getColorPrioridad(tarea.prioridad)"
                      size="x-small"
                      class="text-uppercase"
                    >
                      {{ tarea.prioridad }}
                    </VChip>
                  </div>

                  <h4 class="text-subtitle-1 font-weight-bold mb-2">{{ tarea.titulo }}</h4>

                  <p v-if="tarea.descripcion" class="text-caption text-medium-emphasis mb-3 tarea-descripcion">
                    {{ tarea.descripcion }}
                  </p>

                  <VCard variant="tonal" class="mb-3">
                    <VCardText class="py-2">
                      <div class="text-caption">
                        <strong>{{ tarea.proyecto.cliente.nombre }}</strong> · {{ tarea.proyecto.nombre }}
                      </div>
                    </VCardText>
                  </VCard>

                  <div v-if="tarea.horas_estimadas || tarea.horas_reales" class="mb-2">
                    <div class="text-caption text-success d-flex align-center gap-1">
                      <VIcon icon="tabler-clock-check" size="16" />
                      <span v-if="tarea.horas_estimadas">{{ tarea.horas_estimadas }}h estimadas</span>
                      <span v-if="tarea.horas_reales"> · {{ tarea.horas_reales }}h reales</span>
                    </div>
                  </div>

                  <div class="d-flex justify-space-between align-center">
                    <div class="d-flex align-center gap-2">
                      <VAvatar size="28" color="success">
                        <span class="text-caption">{{ getIniciales(tarea.responsable?.name || 'Sin asignar') }}</span>
                      </VAvatar>
                      <span class="text-caption">{{ tarea.responsable?.name || 'Sin asignar' }}</span>
                    </div>

                    <div class="text-caption text-medium-emphasis">
                      <VIcon icon="tabler-circle-check" size="16" />
                      {{ formatearFecha(tarea.fecha_finalizacion) }}
                    </div>
                  </div>
                </VCardText>
              </VCard>

              <div v-if="!tareasFiltradas.finalizado?.length" class="text-center pa-6">
                <VIcon icon="tabler-inbox" size="48" class="text-medium-emphasis mb-2" />
                <p class="text-caption text-medium-emphasis">No hay tareas finalizadas</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- Modal de Detalles de Tarea -->
    <VDialog
      v-model="modalDetalleVisible"
      max-width="900px"
      persistent
      scrollable
    >
      <VCard v-if="tareaSeleccionada">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span>{{ tareaSeleccionada.titulo }}</span>
          <VBtn icon="tabler-x" variant="text" @click="modalDetalleVisible = false" />
        </VCardTitle>

        <VTabs v-model="tabActiva" class="mb-2">
          <VTab value="detalles">
            <VIcon icon="tabler-info-circle" class="me-2" />
            Detalles
          </VTab>
          <VTab value="adjuntos">
            <VIcon icon="tabler-paperclip" class="me-2" />
            Archivos Adjuntos
            <VChip v-if="adjuntos.length" size="x-small" color="primary" class="ms-2">
              {{ adjuntos.length }}
            </VChip>
          </VTab>
          <VTab value="bitacora">
            <VIcon icon="tabler-history" class="me-2" />
            Bitácora
          </VTab>
        </VTabs>

        <VCardText style="max-height: 600px;">
          <VWindow v-model="tabActiva">
            <!-- Tab Detalles -->
            <VWindowItem value="detalles">
              <div class="d-flex gap-2 mb-4">
                <VChip :color="getColorPrioridad(tareaSeleccionada.prioridad)">
                  {{ tareaSeleccionada.prioridad }}
                </VChip>
                <VChip :color="getColorEstado(tareaSeleccionada.estado)">
                  {{ tareaSeleccionada.estado.replace('_', ' ') }}
                </VChip>
              </div>

              <p class="mb-4">{{ tareaSeleccionada.descripcion || 'Sin descripción' }}</p>

              <VCard variant="tonal" class="mb-4">
                <VCardText>
                  <div class="text-subtitle-2 mb-2">Información del Proyecto</div>
                  <div>
                    <strong>Cliente:</strong> {{ tareaSeleccionada.proyecto.cliente.nombre }}<br>
                    <strong>Proyecto:</strong> {{ tareaSeleccionada.proyecto.nombre }}
                  </div>
                </VCardText>
              </VCard>

              <VRow class="mb-4">
                <VCol cols="6">
                  <VCard variant="tonal" color="primary">
                    <VCardText class="text-center">
                      <div class="text-caption">Horas Trabajadas</div>
                      <div class="text-h5 font-weight-bold">{{ tareaSeleccionada.tiempo_total_trabajado || 0 }}</div>
                    </VCardText>
                  </VCard>
                </VCol>
                <VCol cols="6">
                  <VCard variant="tonal">
                    <VCardText class="text-center">
                      <div class="text-caption text-medium-emphasis">Días Transcurridos</div>
                      <div class="text-h5 font-weight-bold">{{ tareaSeleccionada.dias_transcurridos || 0 }}</div>
                    </VCardText>
                  </VCard>
                </VCol>
              </VRow>

              <!-- Fechas -->
              <VCard variant="tonal" class="mb-4">
                <VCardText>
                  <div class="text-subtitle-2 mb-2">Fechas</div>
                  <div>
                    <strong>Creada:</strong> {{ formatearFechaCompleta(tareaSeleccionada.creado_en) }}<br>
                    <strong>Última modificación:</strong> {{ formatearFechaCompleta(tareaSeleccionada.modificado_en) }}<br>
                    <strong>Inicio:</strong> {{ formatearFechaCompleta(tareaSeleccionada.fecha_inicio) || 'No iniciada' }}<br>
                    <strong>Finalización:</strong> {{ formatearFechaCompleta(tareaSeleccionada.fecha_fin) || 'No finalizada' }}
                  </div>
                </VCardText>
              </VCard>

              <!-- Responsables y Auditoría -->
              <VCard variant="tonal" class="mb-4">
                <VCardText>
                  <div class="text-subtitle-2 mb-2">Responsables y Auditoría</div>
                  <div>
                    <strong>Creado por:</strong> {{ tareaSeleccionada.creado_por || '-' }}<br>
                    <strong>Asignado a:</strong> {{ tareaSeleccionada.responsable?.name || 'Sin asignar' }}<br>
                    <strong>Última modificación por:</strong> {{ tareaSeleccionada.modificado_por || '-' }}
                  </div>
                </VCardText>
              </VCard>
            </VWindowItem>

            <!-- Tab Archivos Adjuntos -->
            <VWindowItem value="adjuntos">
              <VCard variant="tonal" class="mb-3">
                <VCardText>
                  <div class="d-flex align-center justify-space-between mb-2">
                    <div class="d-flex align-center gap-2">
                      <VIcon icon="tabler-paperclip" color="primary" />
                      <div class="text-subtitle-2">Archivos Adjuntos</div>
                    </div>
                    <VChip size="small" color="primary">{{ adjuntos.length }} archivo(s)</VChip>
                  </div>
                  <p class="text-caption text-medium-emphasis mb-0">
                    Documentos y archivos relacionados con esta tarea
                  </p>
                </VCardText>
              </VCard>

              <!-- Formulario para subir archivos (solo admin) -->
              <VCard v-if="esAdmin" variant="tonal" color="info" class="mb-4">
                <VCardText>
                  <div class="text-subtitle-2 mb-3">
                    <VIcon icon="tabler-upload" class="me-2" />
                    Subir Nuevo Archivo
                  </div>
                  <VFileInput
                    v-model="archivoNuevo"
                    label="Seleccionar archivo"
                    prepend-icon="tabler-file"
                    accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.pdf,.jpg,.jpeg,.png,.gif"
                    hint="Formatos permitidos: Word, Excel, PowerPoint, TXT, PDF, Imágenes (Max 10MB)"
                    persistent-hint
                    show-size
                    :disabled="subiendoArchivo"
                  />
                  <VBtn
                    v-if="archivoNuevo && (Array.isArray(archivoNuevo) ? archivoNuevo.length > 0 : archivoNuevo)"
                    color="primary"
                    class="mt-3"
                    prepend-icon="tabler-upload"
                    :loading="subiendoArchivo"
                    :disabled="subiendoArchivo"
                    @click="subirArchivo"
                  >
                    Subir Archivo
                  </VBtn>
                </VCardText>
              </VCard>

              <!-- Lista de archivos -->
              <div v-if="cargandoAdjuntos" class="text-center py-8">
                <VProgressCircular indeterminate color="primary" />
                <p class="text-caption mt-2">Cargando archivos...</p>
              </div>

              <div v-else-if="adjuntos && adjuntos.length">
                <VCard
                  v-for="adjunto in adjuntos"
                  :key="adjunto.id"
                  class="mb-3"
                  variant="outlined"
                >
                  <VCardText>
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center gap-3 flex-grow-1">
                        <VAvatar :color="getColorTipoArchivo(adjunto.tipo_archivo)" size="48">
                          <VIcon :icon="getIconoTipoArchivo(adjunto.tipo_archivo)" size="24" />
                        </VAvatar>
                        
                        <div class="flex-grow-1">
                          <div class="text-subtitle-2 font-weight-bold">{{ adjunto.nombre_archivo }}</div>
                          <div class="text-caption text-medium-emphasis">
                            Subido por {{ adjunto.usuario?.name || 'Desconocido' }} · 
                            {{ formatearFechaCompleta(adjunto.created_at) }} · 
                            {{ formatearTamano(adjunto.tamano) }}
                          </div>
                        </div>
                      </div>

                      <div class="d-flex gap-2">
                        <VBtn
                          icon="tabler-download"
                          size="small"
                          color="primary"
                          variant="tonal"
                          @click="descargarArchivo(adjunto)"
                        />
                        <VBtn
                          v-if="esAdmin"
                          icon="tabler-trash"
                          size="small"
                          color="error"
                          variant="tonal"
                          @click="eliminarArchivo(adjunto)"
                        />
                      </div>
                    </div>
                  </VCardText>
                </VCard>
              </div>

              <div v-else class="text-center py-8">
                <VIcon icon="tabler-file-off" size="64" color="grey-lighten-2" />
                <p class="text-body-1 mt-4">No hay archivos adjuntos</p>
                <p v-if="esAdmin" class="text-caption text-medium-emphasis">
                  Usa el formulario de arriba para subir archivos
                </p>
              </div>
            </VWindowItem>

            <!-- Tab Bitácora -->
            <VWindowItem value="bitacora">
              <VCard variant="tonal" class="mb-3">
                <VCardText>
                  <div class="d-flex align-center gap-2 mb-2">
                    <VIcon icon="tabler-timeline" color="primary" />
                    <div class="text-subtitle-2">Historial de Actividad</div>
                  </div>
                  <p class="text-caption text-medium-emphasis mb-0">
                    Registro completo de todas las acciones realizadas sobre esta tarea
                  </p>
                </VCardText>
              </VCard>

              <div v-if="cargandoBitacora" class="text-center py-8">
                <VProgressCircular indeterminate color="primary" />
                <p class="text-caption mt-2">Cargando historial...</p>
              </div>

              <VTimeline v-else-if="bitacora && bitacora.length" side="end" density="compact">
                <VTimelineItem
                  v-for="(item, index) in bitacora"
                  :key="item.id"
                  :dot-color="getColorAccion(item.accion)"
                  size="small"
                >
                  <template #opposite>
                    <div class="text-caption text-medium-emphasis">
                      {{ formatearFechaCompleta(item.creado_en) }}
                    </div>
                  </template>

                  <VCard variant="tonal" :color="getColorAccion(item.accion)" class="mb-2">
                    <VCardText class="py-2">
                      <div class="d-flex align-center gap-2 mb-1">
                        <VIcon :icon="getIconoAccion(item.accion)" size="18" />
                        <strong class="text-body-2">{{ item.accion.replace('_', ' ').toUpperCase() }}</strong>
                      </div>
                      <p class="mb-2 text-body-2">{{ item.descripcion }}</p>
                      <div v-if="item.usuario" class="d-flex align-center gap-2">
                        <VAvatar size="24" :color="getColorAccion(item.accion)">
                          <span class="text-caption">{{ getIniciales(item.usuario.name) }}</span>
                        </VAvatar>
                        <span class="text-caption">{{ item.usuario.name }}</span>
                      </div>
                    </VCardText>
                  </VCard>
                </VTimelineItem>
              </VTimeline>

              <div v-else class="text-center py-8">
                <VIcon icon="tabler-inbox" size="64" color="grey-lighten-2" />
                <p class="text-body-1 mt-4">No hay actividad registrada</p>
              </div>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="modalDetalleVisible = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal Nueva Tarea -->
    <VDialog
      v-model="modalNuevaTareaVisible"
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle>
          <span class="text-h5">Nueva Tarea</span>
        </VCardTitle>

        <VCardText>
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="nuevaTarea.titulo"
                label="Título *"
                persistent-placeholder
                variant="outlined"
              />
            </VCol>
            
            <VCol cols="12">
              <VTextarea
                v-model="nuevaTarea.descripcion"
                label="Descripción"
                rows="3"
                variant="outlined"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSelect
                v-model="nuevaTarea.proyecto_id"
                :items="proyectos"
                item-title="nombre_completo"
                item-value="id"
                label="Proyecto *"
                variant="outlined"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSelect
                v-model="nuevaTarea.prioridad"
                :items="prioridades"
                label="Prioridad"
                variant="outlined"
              />
            </VCol>

             <VCol cols="12" md="6">
              <VSelect
                v-model="nuevaTarea.responsable_id"
                :items="usuarios"
                item-title="name"
                item-value="id"
                label="Responsable"
                variant="outlined"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="nuevaTarea.modulo"
                label="Módulo"
                placeholder="Ej: Autenticación, Dashboard, etc."
                variant="outlined"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="nuevaTarea.vista"
                label="Vista"
                placeholder="Ej: Login, Profile, etc."
                variant="outlined"
                clearable
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="nuevaTarea.nota"
                label="Nota"
                placeholder="Notas adicionales"
                variant="outlined"
                clearable
              />
            </VCol>

            <VCol v-if="esAdmin" cols="12">
              <VFileInput
                v-model="archivoNuevaTarea"
                label="Archivo adjunto (opcional)"
                prepend-icon="tabler-paperclip"
                accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.pdf,.jpg,.jpeg,.png,.gif"
                hint="Formatos: Word, Excel, PowerPoint, TXT, PDF, Imágenes (Max 10MB)"
                persistent-hint
                show-size
                variant="outlined"
                clearable
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            color="secondary"
            variant="text"
            @click="modalNuevaTareaVisible = false"
          >
            Cancelar
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            @click="crearTarea"
            :loading="cargando"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/store/notification'

// Define metadata para la página
definePage({
  meta: {
    section: 'TicketingKanban',
    action: 'read',
    subject: 'TicketingKanban',
  },
})

const notificationStore = useNotificationStore()

// Estados
const cargando = ref(false)
const cargandoBitacora = ref(false)
const kanbanData = ref({})
const estadisticas = ref(null)
const proyectos = ref([])
const usuarios = ref([])
const tareaSeleccionada = ref(null)
const modalDetalleVisible = ref(false)
const modalNuevaTareaVisible = ref(false)
const nuevaTarea = ref({
  titulo: '',
  descripcion: '',
  prioridad: 'media',
  proyecto_id: null,
  responsable_id: null,
  modulo: '',
  vista: '',
  nota: ''
})
const bitacora = ref([])
const tabActiva = ref('detalles')

// Variables para archivos adjuntos
const adjuntos = ref([])
const cargandoAdjuntos = ref(false)
const archivoNuevo = ref(null)
const archivoNuevaTarea = ref(null)
const subiendoArchivo = ref(false)

// Filtros
const filtroProyecto = ref(null)
const filtroResponsable = ref(null)
const filtroPrioridad = ref(null)
const busqueda = ref('')
const filtroMisTareas = ref(false)
const userData = ref(JSON.parse(localStorage.getItem('userData') || '{}'))

// Verificar si el usuario es admin - variable reactiva que se actualiza
const esAdmin = ref(false)

// Función para verificar si el usuario es admin
const verificarEsAdmin = async () => {
  try {
    // Obtener el usuario actual desde el backend
    const response = await axios.get('/api/profile')
    const user = response.data
    
    console.log('Usuario obtenido:', user)
    console.log('Roles del usuario:', user.roles)
    
    // Verificar si tiene rol de admin
    if (user.roles && Array.isArray(user.roles)) {
      esAdmin.value = user.roles.some(role => 
        ['admin', 'administrador', 'administrator'].includes(role.nombre?.toLowerCase())
      )
      console.log('Es administrador:', esAdmin.value)
    }
  } catch (error) {
    console.error('Error al verificar rol de admin:', error)
    // Fallback: intentar desde localStorage
    const roles = userData.value.roles || []
    esAdmin.value = roles.some(role => 
      ['admin', 'administrador', 'administrator'].includes(role.nombre?.toLowerCase())
    )
    console.log('Es administrador (fallback):', esAdmin.value)
  }
}

// Opciones
const prioridades = ['baja', 'media', 'alta', 'urgente']

// Computed
const tareasFiltradas = computed(() => {
  if (!busqueda.value && !filtroPrioridad.value) return kanbanData.value

  const filtered = {}
  
  Object.keys(kanbanData.value).forEach(estado => {
    filtered[estado] = (kanbanData.value[estado] || []).filter(tarea => {
      let matchBusqueda = true
      let matchPrioridad = true

      if (busqueda.value) {
        const query = busqueda.value.toLowerCase()
        matchBusqueda = tarea.titulo.toLowerCase().includes(query) ||
          (tarea.descripcion && tarea.descripcion.toLowerCase().includes(query)) ||
          tarea.proyecto.nombre.toLowerCase().includes(query) ||
          tarea.proyecto.cliente.nombre.toLowerCase().includes(query)
      }

      if (filtroPrioridad.value) {
        matchPrioridad = tarea.prioridad === filtroPrioridad.value
      }

      return matchBusqueda && matchPrioridad
    })
  })

  return filtered
})

// Métodos
const cargarDatos = async () => {
  await Promise.all([
    cargarProyectos(),
    cargarUsuarios(),
    cargarEstadisticas(),
    cargarKanban()
  ])
}

// Exportar mis tareas a Excel
const exportarMisTareas = () => {
  const params = new URLSearchParams()
  
  if (filtros.value.proyectoId) {
    params.append('proyecto_id', filtros.value.proyectoId)
  }
  
  const url = `/api/exportar/mis-tareas?${params.toString()}`
  window.open(url, '_blank')
}

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
    estadisticas.value = response.data
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}

const cargarKanban = async () => {
  try {
    cargando.value = true
    
    let url = '/api/tareas-kanban?'
    if (filtroProyecto.value) url += `proyecto_id=${filtroProyecto.value}&`
    if (filtroResponsable.value) url += `responsable_id=${filtroResponsable.value}&`
    
    const response = await axios.get(url)
    kanbanData.value = response.data
  } catch (error) {
    console.error('Error al cargar kanban:', error)
    notificationStore.addNotification('Error al cargar las tareas', 'error')
  } finally {
    cargando.value = false
  }
}

const cambiarEstado = async (tareaId, nuevoEstado) => {
  // Optimistic UI update: Find and move task locally first
  let tareaMovida = null
  let estadoAnterior = null

  // Buscar en qué lista está
  for (const estado in kanbanData.value) {
    const index = kanbanData.value[estado].findIndex(t => t.id === tareaId)
    if (index !== -1) {
      tareaMovida = kanbanData.value[estado][index]
      estadoAnterior = estado
      // Remover de lista actual
      kanbanData.value[estado].splice(index, 1)
      break
    }
  }

  // Agregar a nueva lista
  if (tareaMovida) {
    tareaMovida.estado = nuevoEstado
    if (!kanbanData.value[nuevoEstado]) kanbanData.value[nuevoEstado] = []
    kanbanData.value[nuevoEstado].unshift(tareaMovida)
  }

  try {
    await axios.patch(`/api/tareas/${tareaId}/estado`, { estado: nuevoEstado })
    notificationStore.addNotification(`Tarea movida a ${nuevoEstado.replace('_', ' ')}`, 'success')
    // Background refresh to ensure consistency
    cargarKanban()
    cargarEstadisticas()
  } catch (error) {
    console.error('Error al cambiar estado:', error)
    notificationStore.addNotification('Error al cambiar el estado de la tarea', 'error')
    // Revert visual change on error
    if (tareaMovida && estadoAnterior) {
      cargarKanban() // Reload to restore correct state
    }
  }
}

const asignarmeTarea = async (tarea) => {
  try {
    const usuarioId = userData.value.id
    if (!usuarioId) {
      notificationStore.addNotification('No se pudo identificar al usuario', 'error')
      return
    }

    // Actualizar localmente primero
    tarea.responsable_id = usuarioId
    tarea.responsable = { 
      id: usuarioId, 
      name: userData.value.fullName || userData.value.username || 'Yo' 
    }

    await axios.put(`/api/tareas/${tarea.id}`, { responsable_id: usuarioId })
    notificationStore.addNotification('Tarea asignada correctamente', 'success')
    cargarKanban()
  } catch (error) {
    console.error('Error al asignar tarea:', error)
    notificationStore.addNotification('Error al asignarte la tarea', 'error')
    cargarKanban() // Revertir cambios
  }
}

const toggleMisTareas = () => {
  filtroMisTareas.value = !filtroMisTareas.value
  if (filtroMisTareas.value) {
    filtroResponsable.value = userData.value.id
  } else {
    filtroResponsable.value = null
  }
  cargarKanban()
}

const verDetalleTarea = async (tarea) => {
  try {
    const response = await axios.get(`/api/tareas/${tarea.id}`)
    tareaSeleccionada.value = response.data
    tabActiva.value = 'detalles'
    modalDetalleVisible.value = true
    
    // Cargar bitácora y adjuntos
    cargarBitacora(tarea.id)
    cargarAdjuntos(tarea.id)
  } catch (error) {
    console.error('Error al cargar detalle:', error)
    notificationStore.addNotification('Error al cargar los detalles de la tarea', 'error')
  }
}

const cargarBitacora = async (tareaId) => {
  try {
    cargandoBitacora.value = true
    const response = await axios.get(`/api/tareas/${tareaId}/bitacora`)
    bitacora.value = response.data
  } catch (error) {
    console.error('Error al cargar bitácora:', error)
    bitacora.value = []
  } finally {
    cargandoBitacora.value = false
  }
}

const mostrarModalNuevaTarea = () => {
  nuevaTarea.value = {
    titulo: '',
    descripcion: '',
    prioridad: 'media',
    proyecto_id: null,
    responsable_id: null,
    modulo: '',
    vista: '',
    nota: ''
  }
  modalNuevaTareaVisible.value = true
}

const crearTarea = async () => {
  if (!nuevaTarea.value.titulo || !nuevaTarea.value.proyecto_id) {
    notificationStore.addNotification('Por favor completa los campos obligatorios (Título y Proyecto)', 'warning')
    return
  }
  
  try {
    cargando.value = true
    
    console.log('=== CREANDO TAREA ===')
    console.log('Datos de la tarea:', nuevaTarea.value)
    console.log('Archivo adjunto:', archivoNuevaTarea.value)
    console.log('Es admin:', esAdmin.value)
    
    const response = await axios.post('/api/tareas', nuevaTarea.value)
    const tareaCreada = response.data.tarea
    
    console.log('Tarea creada exitosamente:', tareaCreada)
    
    // Si hay un archivo adjunto y el usuario es admin, subirlo
    if (archivoNuevaTarea.value && esAdmin.value) {
      console.log('Intentando subir archivo adjunto...')
      console.log('Tipo de archivoNuevaTarea:', typeof archivoNuevaTarea.value)
      console.log('Es array:', Array.isArray(archivoNuevaTarea.value))
      console.log('Contenido:', archivoNuevaTarea.value)
      
      // Obtener el archivo (VFileInput devuelve un array)
      const archivo = Array.isArray(archivoNuevaTarea.value) 
        ? archivoNuevaTarea.value[0] 
        : archivoNuevaTarea.value
      
      console.log('Archivo extraído:', archivo)
      console.log('Nombre del archivo:', archivo?.name)
      console.log('Tamaño del archivo:', archivo?.size)
      
      if (archivo && archivo instanceof File) {
        try {
          const formData = new FormData()
          formData.append('archivo', archivo)
          
          console.log('FormData creado, enviando a:', `/api/tareas/${tareaCreada.id}/adjuntos`)
          
          const uploadResponse = await axios.post(
            `/api/tareas/${tareaCreada.id}/adjuntos`,
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            }
          )
          
          console.log('Archivo subido exitosamente:', uploadResponse.data)
          notificationStore.addNotification('Tarea creada y archivo adjuntado exitosamente', 'success')
        } catch (errorArchivo) {
          console.error('Error al subir archivo:', errorArchivo)
          console.error('Respuesta del error:', errorArchivo.response?.data)
          notificationStore.addNotification('Tarea creada pero hubo un error al subir el archivo: ' + (errorArchivo.response?.data?.message || errorArchivo.message), 'warning')
        }
      } else {
        console.warn('El archivo no es válido o no es una instancia de File')
        notificationStore.addNotification('Tarea creada exitosamente', 'success')
      }
    } else {
      console.log('No hay archivo para subir o el usuario no es admin')
      notificationStore.addNotification('Tarea creada exitosamente', 'success')
    }
    
    modalNuevaTareaVisible.value = false
    
    // Resetear formulario
    nuevaTarea.value = {
      titulo: '',
      descripcion: '',
      prioridad: 'media',
      proyecto_id: null,
      responsable_id: null,
      modulo: '',
      vista: '',
      nota: ''
    }
    archivoNuevaTarea.value = null
    
    await cargarDatos()
  } catch (error) {
    console.error('Error al crear tarea:', error)
    console.error('Respuesta del error:', error.response?.data)
    notificationStore.addNotification('Error al crear la tarea: ' + (error.response?.data?.message || error.message), 'error')
  } finally {
    cargando.value = false
  }
}

const getIniciales = (nombre) => {
  return nombre
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase()
}

const formatearFecha = (fecha) => {
  if (!fecha) return '-'
  
  const date = new Date(fecha)
  const now = new Date()
  const diff = Math.floor((now - date) / (1000 * 60 * 60 * 24))
  
  if (diff === 0) return 'Hoy'
  if (diff === 1) return 'Ayer'
  if (diff < 7) return `Hace ${diff} días`
  if (diff < 30) return `Hace ${Math.floor(diff / 7)} semanas`
  
  return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' })
}

const formatearFechaCompleta = (fecha) => {
  if (!fecha) return '-'
  
  const date = new Date(fecha)
  return date.toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getColorPrioridad = (prioridad) => {
  const colores = {
    baja: 'grey',
    media: 'warning',
    alta: 'orange',
    urgente: 'error'
  }
  return colores[prioridad] || 'grey'
}

const getColorEstado = (estado) => {
  const colores = {
    pendiente: 'warning',
    en_proceso: 'primary',
    en_revision: 'purple',
    finalizado: 'success'
  }
  return colores[estado] || 'grey'
}

const getColorAccion = (accion) => {
  const colores = {
    creada: 'success',
    asignada: 'info',
    iniciada: 'primary',
    en_proceso: 'primary',
    pausada: 'warning',
    reanudada: 'primary',
    enviada_revision: 'purple',
    en_revision: 'purple',
    finalizada: 'success',
    finalizado: 'success',
    cancelada: 'error',
    modificada: 'info'
  }
  return colores[accion] || 'grey'
}

const getIconoAccion = (accion) => {
  const iconos = {
    creada: 'tabler-plus',
    asignada: 'tabler-user-check',
    iniciada: 'tabler-player-play',
    en_proceso: 'tabler-clock-play',
    pausada: 'tabler-player-pause',
    reanudada: 'tabler-player-play',
    enviada_revision: 'tabler-eye-check',
    en_revision: 'tabler-eye-check',
    finalizada: 'tabler-circle-check',
    finalizado: 'tabler-circle-check',
    cancelada: 'tabler-x',
    modificada: 'tabler-edit'
  }
  return iconos[accion] || 'tabler-timeline'
}

// Funciones para archivos adjuntos
const cargarAdjuntos = async (tareaId) => {
  try {
    cargandoAdjuntos.value = true
    const response = await axios.get(`/api/tareas/${tareaId}/adjuntos`)
    adjuntos.value = response.data
  } catch (error) {
    console.error('Error al cargar adjuntos:', error)
    if (error.response?.status !== 403) {
      notificationStore.addNotification('Error al cargar los archivos adjuntos', 'error')
    }
  } finally {
    cargandoAdjuntos.value = false
  }
}

const subirArchivo = async () => {
  console.log('=== Iniciando subida de archivo ===')
  console.log('archivoNuevo.value:', archivoNuevo.value)
  console.log('tareaSeleccionada.value:', tareaSeleccionada.value)
  
  // Validar que haya archivo y tarea
  if (!archivoNuevo.value || !tareaSeleccionada.value) {
    console.warn('Validación fallida:', {
      archivoNuevo: archivoNuevo.value,
      tareaSeleccionada: tareaSeleccionada.value
    })
    notificationStore.addNotification('Por favor seleccione un archivo', 'warning')
    return
  }
  
  // Obtener el archivo (puede ser File directo o array de Files)
  const archivo = Array.isArray(archivoNuevo.value) 
    ? archivoNuevo.value[0] 
    : archivoNuevo.value
  
  if (!archivo) {
    console.warn('No se pudo obtener el archivo')
    notificationStore.addNotification('Por favor seleccione un archivo', 'warning')
    return
  }
  
  try {
    subiendoArchivo.value = true
    const formData = new FormData()
    formData.append('archivo', archivo)
    
    console.log('FormData creado:', {
      archivo: archivo,
      tareaId: tareaSeleccionada.value.id
    })
    
    const response = await axios.post(
      `/api/tareas/${tareaSeleccionada.value.id}/adjuntos`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )
    
    console.log('Respuesta del servidor:', response.data)
    
    adjuntos.value.unshift(response.data.adjunto)
    archivoNuevo.value = null
    notificationStore.addNotification('Archivo subido exitosamente', 'success')
  } catch (error) {
    console.error('Error completo:', error)
    console.error('Error response:', error.response)
    console.error('Error data:', error.response?.data)
    
    const mensaje = error.response?.data?.message || error.response?.data?.error || 'Error al subir el archivo'
    notificationStore.addNotification(mensaje, 'error')
  } finally {
    subiendoArchivo.value = false
  }
}

const descargarArchivo = async (adjunto) => {
  try {
    console.log('=== Iniciando descarga ===')
    console.log('Archivo:', adjunto.nombre_archivo)
    console.log('Tipo:', adjunto.tipo_archivo)
    
    // Usar axios con responseType arraybuffer para archivos binarios
    const response = await axios.get(`/api/adjuntos/${adjunto.id}/download`, {
      responseType: 'arraybuffer', // Importante para archivos binarios
    })
    
    console.log('=== Respuesta recibida ===')
    console.log('Status:', response.status)
    console.log('Content-Type:', response.headers['content-type'])
    console.log('Content-Length:', response.headers['content-length'])
    console.log('Data type:', typeof response.data)
    console.log('Data es ArrayBuffer:', response.data instanceof ArrayBuffer)
    console.log('Tamaño de data:', response.data.byteLength)
    
    // Verificar que recibimos un ArrayBuffer
    if (!(response.data instanceof ArrayBuffer)) {
      throw new Error('La respuesta no es un ArrayBuffer')
    }
    
    // Crear blob desde arraybuffer con el tipo MIME correcto
    const mimeType = response.headers['content-type'] || adjunto.tipo_archivo || 'application/octet-stream'
    const blob = new Blob([response.data], { type: mimeType })
    
    console.log('=== Blob creado ===')
    console.log('Tipo de blob:', blob.type)
    console.log('Tamaño de blob:', blob.size)
    
    // Crear URL y descargar
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = adjunto.nombre_archivo
    link.style.display = 'none'
    document.body.appendChild(link)
    link.click()
    
    console.log('=== Descarga iniciada ===')
    
    // Limpiar
    setTimeout(() => {
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      console.log('=== Limpieza completada ===')
    }, 100)
    
    notificationStore.addNotification('Archivo descargado correctamente', 'success')
  } catch (error) {
    console.error('=== Error en descarga ===')
    console.error('Error:', error)
    console.error('Response:', error.response)
    const mensaje = error.response?.data?.message || error.message || 'Error al descargar el archivo'
    notificationStore.addNotification(mensaje, 'error')
  }
}


const eliminarArchivo = async (adjunto) => {
  if (!confirm(`¿Estás seguro de eliminar el archivo "${adjunto.nombre_archivo}"?`)) return
  
  try {
    await axios.delete(`/api/adjuntos/${adjunto.id}`)
    adjuntos.value = adjuntos.value.filter(a => a.id !== adjunto.id)
    notificationStore.addNotification('Archivo eliminado exitosamente', 'success')
  } catch (error) {
    console.error('Error al eliminar archivo:', error)
    const mensaje = error.response?.data?.message || 'Error al eliminar el archivo'
    notificationStore.addNotification(mensaje, 'error')
  }
}

const formatearTamano = (bytes) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const getIconoTipoArchivo = (tipoArchivo) => {
  if (!tipoArchivo) return 'tabler-file'
  
  const tipo = tipoArchivo.toLowerCase()
  
  if (tipo.includes('word') || tipo.includes('document')) return 'tabler-file-text'
  if (tipo.includes('excel') || tipo.includes('spreadsheet')) return 'tabler-file-spreadsheet'
  if (tipo.includes('powerpoint') || tipo.includes('presentation')) return 'tabler-presentation'
  if (tipo.includes('pdf')) return 'tabler-file-type-pdf'
  if (tipo.includes('image') || tipo.includes('jpeg') || tipo.includes('png') || tipo.includes('gif')) return 'tabler-photo'
  if (tipo.includes('text')) return 'tabler-file-text'
  
  return 'tabler-file'
}

const getColorTipoArchivo = (tipoArchivo) => {
  if (!tipoArchivo) return 'grey'
  
  const tipo = tipoArchivo.toLowerCase()
  
  if (tipo.includes('word') || tipo.includes('document')) return 'blue'
  if (tipo.includes('excel') || tipo.includes('spreadsheet')) return 'green'
  if (tipo.includes('powerpoint') || tipo.includes('presentation')) return 'orange'
  if (tipo.includes('pdf')) return 'red'
  if (tipo.includes('image') || tipo.includes('jpeg') || tipo.includes('png') || tipo.includes('gif')) return 'purple'
  if (tipo.includes('text')) return 'grey'
  
  return 'grey'
}

// Watcher para debugging del archivo
watch(archivoNuevo, (newVal) => {
  console.log('archivoNuevo cambió:', {
    valor: newVal,
    tipo: typeof newVal,
    esArray: Array.isArray(newVal),
    length: newVal?.length,
    contenido: newVal
  })
})

// Cargar datos al montar
onMounted(async () => {
  verificarEsAdmin()
  await cargarDatos()
  
  // Verificar si hay un parámetro de tarea en la URL (desde correo electrónico)
  const urlParams = new URLSearchParams(window.location.search)
  const tareaId = urlParams.get('tarea')
  
  if (tareaId) {
    console.log('Parámetro de tarea detectado:', tareaId)
    
    // Esperar un momento para que los datos se carguen completamente
    setTimeout(() => {
      // Buscar la tarea en todos los estados del kanban
      let tareaEncontrada = null
      
      for (const estado in kanbanData.value) {
        const tareas = kanbanData.value[estado] || []
        tareaEncontrada = tareas.find(t => t.id === parseInt(tareaId))
        if (tareaEncontrada) break
      }
      
      if (tareaEncontrada) {
        console.log('Tarea encontrada, abriendo modal:', tareaEncontrada)
        verDetalleTarea(tareaEncontrada)
        
        // Limpiar el parámetro de la URL sin recargar la página
        const newUrl = window.location.pathname
        window.history.replaceState({}, '', newUrl)
      } else {
        console.warn('Tarea no encontrada con ID:', tareaId)
        notificationStore.addNotification('No se pudo encontrar la tarea especificada', 'warning')
      }
    }, 500)
  }
})
</script>

<style lang="scss" scoped>
.stat-card {
  transition: transform 0.2s ease;

  &:hover {
    transform: translateY(-2px);
  }
}

.kanban-container {
  min-height: 60vh;
}

.kanban-column {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.kanban-tasks {
  flex: 1;
  overflow-y: auto;
  max-height: 70vh;
}

.tarea-card {
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: currentColor;
  }

  &.prioridad-baja::before {
    background: rgb(var(--v-theme-grey));
  }

  &.prioridad-media::before {
    background: rgb(var(--v-theme-warning));
  }

  &.prioridad-alta::before {
    background: rgb(var(--v-theme-orange));
  }

  &.prioridad-urgente::before {
    background: rgb(var(--v-theme-error));
    animation: pulse 2s infinite;
  }

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  }
}

.tarea-descripcion {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}
</style>
