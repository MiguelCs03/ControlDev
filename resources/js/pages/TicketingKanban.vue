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
            <VCard variant="tonal" color="warning" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">Mis Tareas Activas</div>
                    <div class="text-h4 font-weight-bold">
                      {{ estadisticas.mis_tareas.pendientes + estadisticas.mis_tareas.en_proceso + estadisticas.mis_tareas.en_revision }}
                    </div>
                  </div>
                  <VIcon icon="tabler-activity" size="40" class="text-medium-emphasis" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="tonal" color="primary" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">En Proceso</div>
                    <div class="text-h4 font-weight-bold">{{ estadisticas.mis_tareas.en_proceso }}</div>
                  </div>
                  <VIcon icon="tabler-clock-play" size="40" class="text-medium-emphasis" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="tonal" color="purple" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">En Revisión</div>
                    <div class="text-h4 font-weight-bold">{{ estadisticas.mis_tareas.en_revision }}</div>
                  </div>
                  <VIcon icon="tabler-eye-check" size="40" class="text-medium-emphasis" />
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" sm="6" md="3">
            <VCard variant="tonal" color="success" class="stat-card">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <div class="text-caption text-medium-emphasis mb-1">Finalizadas</div>
                    <div class="text-h4 font-weight-bold">{{ estadisticas.mis_tareas.finalizadas }}</div>
                  </div>
                  <VIcon icon="tabler-circle-check" size="40" class="text-medium-emphasis" />
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
          <VCard class="kanban-column" color="warning" variant="tonal">
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
          <VCard class="kanban-column" color="primary" variant="tonal">
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
          <VCard class="kanban-column" color="purple" variant="tonal">
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
          <VCard class="kanban-column" color="success" variant="tonal">
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/store/notification'

// Define metadata para la página
definePage({
  meta: {
    section: 'ticketing-kanban',
    action: 'read',
    subject: 'ticketing-kanban',
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
  responsable_id: null
})
const bitacora = ref([])
const tabActiva = ref('detalles')

// Filtros
const filtroProyecto = ref(null)
const filtroResponsable = ref(null)
const filtroPrioridad = ref(null)
const busqueda = ref('')

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
  try {
    await axios.patch(`/api/tareas/${tareaId}/estado`, { estado: nuevoEstado })
    notificationStore.addNotification(`Tarea movida a ${nuevoEstado.replace('_', ' ')}`, 'success')
    await cargarKanban()
    await cargarEstadisticas()
  } catch (error) {
    console.error('Error al cambiar estado:', error)
    notificationStore.addNotification('Error al cambiar el estado de la tarea', 'error')
  }
}

const verDetalleTarea = async (tarea) => {
  try {
    const response = await axios.get(`/api/tareas/${tarea.id}`)
    tareaSeleccionada.value = response.data
    tabActiva.value = 'detalles'
    modalDetalleVisible.value = true
    
    // Cargar bitácora
    cargarBitacora(tarea.id)
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
    responsable_id: null
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
    await axios.post('/api/tareas', nuevaTarea.value)
    
    notificationStore.addNotification('Tarea creada exitosamente', 'success')
    modalNuevaTareaVisible.value = false
    await cargarDatos()
  } catch (error) {
    console.error('Error al crear tarea:', error)
    notificationStore.addNotification('Error al crear la tarea', 'error')
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

// Cargar datos al montar
onMounted(() => {
  cargarDatos()
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
