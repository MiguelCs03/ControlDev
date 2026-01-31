<template>
  <div>
    <!-- Header con título y botones -->
    <VCard class="mb-4">
      <VCardText class="d-flex justify-space-between align-center">
        <div>
          <h2 class="text-h5 mb-1">Gestión de Conceptos de Planilla</h2>
          <p class="text-body-2 mb-0">Administra los conceptos de remuneración del sistema</p>
        </div>

        <div class="d-flex align-center gap-2">
          <!-- Buscador -->
          <VTextField
            v-model="busqueda"
            label="Buscar concepto"
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
            Nuevo Concepto
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Tabla de conceptos -->
    <VCard>
      <VDataTable
        :headers="cabeceras"
        :items="conceptosFiltrados"
        :loading="cargando"
        items-per-page="10"
      >
        <!-- Columna de Nombre -->
        <template #item.nombre="{ item }">
          <span class="font-weight-medium">{{ item.nombre }}</span>
        </template>

        <!-- Columna de Tipo -->
        <template #item.tipo="{ item }">
          <VChip
            :color="getColorTipo(item.tipo)"
            size="small"
          >
            {{ getLabelTipo(item.tipo) }}
          </VChip>
        </template>

        <!-- Columna de Tipo Monto -->
        <template #item.tipo_monto="{ item }">
          <span class="font-weight-medium">{{ getLabelTipoMonto(item.tipo_monto) }}</span>
        </template>

        <!-- Columna de Monto -->
        <template #item.monto="{ item }">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-currency-dollar" size="16" color="grey" />
            <span class="font-weight-medium">
              {{ formatoMonto(item.monto, item.tipo_monto) }}
            </span>
          </div>
        </template>

        <!-- Columna de Tipo Porcentaje -->
        <template #item.porcentaje_de="{ item }">
          <span v-if="item.porcentaje_de" class="font-weight-medium">
            {{ getLabelPorcentajeDe(item.porcentaje_de) }}
          </span>
          <span v-else class="text-disabled">-</span>
        </template>

        <!-- Columna de Estado -->
        <template #item.activo="{ item }">
          <div class="d-flex align-center justify-right gap-1">
            <VIcon
              :color="item.activo ? 'success' : 'error'"
              size="20"
            >
              {{ item.activo ? 'tabler-circle-dot' : 'tabler-circle-dashed' }}
            </VIcon>
            <span class="text-caption">
              {{ item.activo ? 'Activo' : 'Inactivo' }}
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
              @click="editarConcepto(item)"
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
              tabler-receipt
            </VIcon>
            <p class="text-h6 mt-4">No hay conceptos registrados</p>
            <p class="text-body-2">Crea tu primer concepto haciendo clic en "Nuevo Concepto"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear concepto -->
    <VDialog
      v-model="modalCrearVisible"
      max-width="750px"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Crear Nuevo Concepto</span>
          <VBtn icon="tabler-x" variant="text" @click="cerrarModalCrear" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioCrear" @submit.prevent="guardarConcepto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="nuevoConcepto.nombre"
                  label="Descripción *"
                  placeholder="Ej: Bono de productividad, AFP, etc."
                  :rules="[reglas.requerido, reglas.max200]"
                  prepend-inner-icon="tabler-text-caption"
                  required
                  variant="outlined"
                  color="primary"
                />
              </VCol>

              <!-- TIPO - Radio Buttons Elegantes -->
              <VCol cols="12">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- INGRESO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.tipo === 1 ? 'border-success bg-success-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="nuevoConcepto.tipo = 1"
                  >
                    <VIcon 
                      icon="tabler-trending-up" 
                      :color="nuevoConcepto.tipo === 1 ? 'success' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.tipo === 1 ? 'text-success' : 'text-grey'">
                      INGRESO
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.tipo === 1"
                      color="success"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- EGRESO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.tipo === 2 ? 'border-error bg-error-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="nuevoConcepto.tipo = 2"
                  >
                    <VIcon 
                      icon="tabler-trending-down" 
                      :color="nuevoConcepto.tipo === 2 ? 'error' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.tipo === 2 ? 'text-error' : 'text-grey'">
                      EGRESO
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.tipo === 2"
                      color="error"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- CARGAS SOCIALES -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.tipo === 3 ? 'border-warning bg-warning-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="nuevoConcepto.tipo = 3"
                  >
                    <VIcon 
                      icon="tabler-users" 
                      :color="nuevoConcepto.tipo === 3 ? 'warning' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.tipo === 3 ? 'text-warning' : 'text-grey'">
                      CARGAS SOCIALES
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.tipo === 3"
                      color="warning"
                      class="mt-2"
                    />
                  </div>
                </div>
                <VAlert
                  v-if="!nuevoConcepto.tipo"
                  color="warning"
                  variant="tonal"
                  class="mt-2"
                  density="compact"
                >
                  <VIcon icon="tabler-alert-circle" size="16" class="me-1" />
                  Seleccione un tipo
                </VAlert>
              </VCol>

              <!-- TIPO MONTO - Radio Buttons Elegantes -->
              <VCol cols="12">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo Monto *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- PORCENTAJE -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.tipo_monto === 1 ? 'border-info bg-info-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="seleccionarTipoMonto(1)"
                  >
                    <VIcon 
                      icon="tabler-percentage" 
                      :color="nuevoConcepto.tipo_monto === 1 ? 'info' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.tipo_monto === 1 ? 'text-info' : 'text-grey'">
                      PORCENTAJE (%)
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.tipo_monto === 1"
                      color="info"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- FIJO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.tipo_monto === 2 ? 'border-primary bg-primary-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="seleccionarTipoMonto(2)"
                  >
                    <VIcon 
                      icon="tabler-currency-dollar" 
                      :color="nuevoConcepto.tipo_monto === 2 ? 'primary' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.tipo_monto === 2 ? 'text-primary' : 'text-grey'">
                      MONTO FIJO ($)
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.tipo_monto === 2"
                      color="primary"
                      class="mt-2"
                    />
                  </div>
                </div>
                <VAlert
                  v-if="!nuevoConcepto.tipo_monto"
                  color="warning"
                  variant="tonal"
                  class="mt-2"
                  density="compact"
                >
                  <VIcon icon="tabler-alert-circle" size="16" class="me-1" />
                  Seleccione un tipo de monto
                </VAlert>
              </VCol>

              <!-- MONTO -->
              <VCol cols="12">
                <VTextField
                  v-model="nuevoConcepto.monto"
                  :label="`Monto ${nuevoConcepto.tipo_monto === 1 ? '(%)' : '($)'} *`"
                  :placeholder="nuevoConcepto.tipo_monto === 1 ? 'Ej: 15.38462' : 'Ej: 100.00'"
                  type="number"
                  :rules="[reglas.requerido, reglas.min0]"
                  prepend-inner-icon="tabler-currency-dollar"
                  required
                  step="0.00001"
                  :min="0"
                  variant="outlined"
                  :color="nuevoConcepto.tipo_monto === 1 ? 'info' : 'primary'"
                >
                  <template #prepend>
                    <span v-if="nuevoConcepto.tipo_monto === 1" class="text-info font-weight-medium">%</span>
                    <span v-else class="text-primary font-weight-medium">$</span>
                  </template>
                </VTextField>
              </VCol>

              <!-- TIPO PORCENTAJE - Radio Buttons Elegantes (solo visible cuando tipo_monto = 1) -->
              <VCol cols="12" v-if="nuevoConcepto.tipo_monto === 1">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo Porcentaje *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- HABER BASICO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.porcentaje_de === 1 ? 'border-teal bg-teal-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="nuevoConcepto.porcentaje_de = 1"
                  >
                    <VIcon 
                      icon="tabler-calculator" 
                      :color="nuevoConcepto.porcentaje_de === 1 ? 'teal' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.porcentaje_de === 1 ? 'text-teal' : 'text-grey'">
                      HABER BASICO
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.porcentaje_de === 1"
                      color="teal"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- TOTAL GANADO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="nuevoConcepto.porcentaje_de === 2 ? 'border-deep-purple bg-deep-purple-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="nuevoConcepto.porcentaje_de = 2"
                  >
                    <VIcon 
                      icon="tabler-chart-bar" 
                      :color="nuevoConcepto.porcentaje_de === 2 ? 'deep-purple' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="nuevoConcepto.porcentaje_de === 2 ? 'text-deep-purple' : 'text-grey'">
                      TOTAL GANADO
                    </span>
                    <VRadio
                      :model-value="nuevoConcepto.porcentaje_de === 2"
                      color="deep-purple"
                      class="mt-2"
                    />
                  </div>
                </div>
                <VAlert
                  v-if="nuevoConcepto.tipo_monto === 1 && !nuevoConcepto.porcentaje_de"
                  color="warning"
                  variant="tonal"
                  class="mt-2"
                  density="compact"
                >
                  <VIcon icon="tabler-alert-circle" size="16" class="me-1" />
                  Seleccione un tipo de porcentaje
                </VAlert>
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
            :disabled="guardando || !formularioValido"
            @click="guardarConcepto"
            prepend-icon="tabler-check"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar concepto -->
    <VDialog
      v-model="modalEditarVisible"
      max-width="750px"
      persistent
    >
      <VCard v-if="conceptoEditando">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Editar Concepto</span>
          <VBtn icon="tabler-x" variant="text" @click="modalEditarVisible = false" />
        </VCardTitle>

        <VCardText>
          <VForm ref="formularioEditar" @submit.prevent="actualizarConcepto">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="conceptoEditando.nombre"
                  label="Descripción *"
                  :rules="[reglas.requerido, reglas.max200]"
                  prepend-inner-icon="tabler-text-caption"
                  required
                  variant="outlined"
                  color="primary"
                />
              </VCol>

              <!-- TIPO - Radio Buttons Elegantes -->
              <VCol cols="12">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- INGRESO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.tipo === 1 ? 'border-success bg-success-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="conceptoEditando.tipo = 1"
                  >
                    <VIcon 
                      icon="tabler-trending-up" 
                      :color="conceptoEditando.tipo === 1 ? 'success' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.tipo === 1 ? 'text-success' : 'text-grey'">
                      INGRESO
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.tipo === 1"
                      color="success"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- EGRESO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.tipo === 2 ? 'border-error bg-error-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="conceptoEditando.tipo = 2"
                  >
                    <VIcon 
                      icon="tabler-trending-down" 
                      :color="conceptoEditando.tipo === 2 ? 'error' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.tipo === 2 ? 'text-error' : 'text-grey'">
                      EGRESO
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.tipo === 2"
                      color="error"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- CARGAS SOCIALES -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.tipo === 3 ? 'border-warning bg-warning-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="conceptoEditando.tipo = 3"
                  >
                    <VIcon 
                      icon="tabler-users" 
                      :color="conceptoEditando.tipo === 3 ? 'warning' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.tipo === 3 ? 'text-warning' : 'text-grey'">
                      CARGAS SOCIALES
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.tipo === 3"
                      color="warning"
                      class="mt-2"
                    />
                  </div>
                </div>
              </VCol>

              <!-- TIPO MONTO - Radio Buttons Elegantes -->
              <VCol cols="12">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo Monto *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- PORCENTAJE -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.tipo_monto === 1 ? 'border-info bg-info-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="seleccionarTipoMontoEditar(1)"
                  >
                    <VIcon 
                      icon="tabler-percentage" 
                      :color="conceptoEditando.tipo_monto === 1 ? 'info' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.tipo_monto === 1 ? 'text-info' : 'text-grey'">
                      PORCENTAJE (%)
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.tipo_monto === 1"
                      color="info"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- FIJO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.tipo_monto === 2 ? 'border-primary bg-primary-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="seleccionarTipoMontoEditar(2)"
                  >
                    <VIcon 
                      icon="tabler-currency-dollar" 
                      :color="conceptoEditando.tipo_monto === 2 ? 'primary' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.tipo_monto === 2 ? 'text-primary' : 'text-grey'">
                      MONTO FIJO ($)
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.tipo_monto === 2"
                      color="primary"
                      class="mt-2"
                    />
                  </div>
                </div>
              </VCol>

              <!-- MONTO -->
              <VCol cols="12">
                <VTextField
                  v-model="conceptoEditando.monto"
                  :label="`Monto ${conceptoEditando.tipo_monto === 1 ? '(%)' : '($)'} *`"
                  type="number"
                  :rules="[reglas.requerido, reglas.min0]"
                  prepend-inner-icon="tabler-currency-dollar"
                  required
                  step="0.00001"
                  :min="0"
                  variant="outlined"
                  :color="conceptoEditando.tipo_monto === 1 ? 'info' : 'primary'"
                >
                  <template #prepend>
                    <span v-if="conceptoEditando.tipo_monto === 1" class="text-info font-weight-medium">%</span>
                    <span v-else class="text-primary font-weight-medium">$</span>
                  </template>
                </VTextField>
              </VCol>

              <!-- TIPO PORCENTAJE - Radio Buttons Elegantes (solo visible cuando tipo_monto = 1) -->
              <VCol cols="12" v-if="conceptoEditando.tipo_monto === 1">
                <VLabel class="mb-2 text-subtitle-1 font-weight-medium">Tipo Porcentaje *</VLabel>
                <div class="d-flex flex-wrap gap-3">
                  <!-- HABER BASICO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.porcentaje_de === 1 ? 'border-teal bg-teal-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="conceptoEditando.porcentaje_de = 1"
                  >
                    <VIcon 
                      icon="tabler-calculator" 
                      :color="conceptoEditando.porcentaje_de === 1 ? 'teal' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.porcentaje_de === 1 ? 'text-teal' : 'text-grey'">
                      HABER BASICO
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.porcentaje_de === 1"
                      color="teal"
                      class="mt-2"
                    />
                  </div>
                  
                  <!-- TOTAL GANADO -->
                  <div 
                    class="d-flex flex-column align-center pa-4 rounded border"
                    :class="conceptoEditando.porcentaje_de === 2 ? 'border-deep-purple bg-deep-purple-lighten-5' : 'border-grey-lighten-2'"
                    style="min-width: 140px; cursor: pointer;"
                    @click="conceptoEditando.porcentaje_de = 2"
                  >
                    <VIcon 
                      icon="tabler-chart-bar" 
                      :color="conceptoEditando.porcentaje_de === 2 ? 'deep-purple' : 'grey'" 
                      size="32"
                      class="mb-2"
                    />
                    <span class="font-weight-medium" :class="conceptoEditando.porcentaje_de === 2 ? 'text-deep-purple' : 'text-grey'">
                      TOTAL GANADO
                    </span>
                    <VRadio
                      :model-value="conceptoEditando.porcentaje_de === 2"
                      color="deep-purple"
                      class="mt-2"
                    />
                  </div>
                </div>
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
            :disabled="guardando || !formularioValidoEditar"
            @click="actualizarConcepto"
            prepend-icon="tabler-check"
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
            Eliminar Concepto
          </h3>
          
          <p class="text-body-1 mb-4">
            ¿Está seguro de eliminar el concepto 
            <strong>"{{ conceptoConfirmandoEliminar?.nombre }}"</strong>?
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
              @click="confirmarEliminarConcepto"
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
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/store/notification'

// Define metadata para la página
definePage({
  meta: {
    section: 'parametros-conceptos',
    action: 'read',
    subject: 'parametros-conceptos',
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
const conceptos = ref([])
const conceptoEditando = ref(null)
const conceptoConfirmandoEliminar = ref(null)

// Formulario nuevo concepto
const nuevoConcepto = ref({
  nombre: '',
  tipo: null,
  tipo_monto: null,
  monto: null,
  porcentaje_de: null,
})

// Cabeceras de la tabla
const cabeceras = [
  { title: 'Descripción', key: 'nombre', sortable: true },
  { title: 'Tipo', key: 'tipo', sortable: true },
  { title: 'Tipo Monto', key: 'tipo_monto', sortable: true },
  { title: 'Monto', key: 'monto', sortable: true },
  { title: 'Tipo Porcentaje', key: 'porcentaje_de', sortable: true },
  { title: 'Estado', key: 'activo', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 },
]

// Reglas de validación
const reglas = {
  requerido: value => {
    if (value === null || value === undefined || value === '') {
      return 'Campo requerido'
    }
    return true
  },
  max200: value => (value || '').length <= 200 || 'Máximo 200 caracteres',
  min0: value => {
    const num = parseFloat(value || 0)
    return num >= 0 || 'Debe ser mayor o igual a 0'
  },
}

// Computed properties
const conceptosFiltrados = computed(() => {
  if (!busqueda.value) return conceptos.value

  const busquedaLower = busqueda.value.toLowerCase()
  return conceptos.value.filter(concepto => {
    return concepto.nombre.toLowerCase().includes(busquedaLower)
  })
})

// Validación del formulario
const formularioValido = computed(() => {
  const { nombre, tipo, tipo_monto, monto, porcentaje_de } = nuevoConcepto.value
  
  // Verificar que todos los campos requeridos estén completos
  if (!nombre || !tipo || !tipo_monto || monto === null || monto === '' || monto === undefined) {
    return false
  }
  
  // Si es porcentaje, validar que tenga tipo porcentaje
  if (tipo_monto === 1 && !porcentaje_de) {
    return false
  }
  
  // Validar que el monto sea un número válido
  const montoNum = parseFloat(monto)
  if (isNaN(montoNum) || montoNum < 0) {
    return false
  }
  
  return true
})

const formularioValidoEditar = computed(() => {
  if (!conceptoEditando.value) return false
  
  const { nombre, tipo, tipo_monto, monto, porcentaje_de } = conceptoEditando.value
  
  if (!nombre || !tipo || !tipo_monto || monto === null || monto === '' || monto === undefined) {
    return false
  }
  
  // Si es porcentaje, validar que tenga tipo porcentaje
  if (tipo_monto === 1 && !porcentaje_de) {
    return false
  }
  
  // Validar que el monto sea un número válido
  const montoNum = parseFloat(monto)
  if (isNaN(montoNum) || montoNum < 0) {
    return false
  }
  
  return true
})

// Métodos
const seleccionarTipoMonto = (value) => {
  nuevoConcepto.value.tipo_monto = value
  if (value === 2) { // Si cambia a FIJO
    nuevoConcepto.value.porcentaje_de = null
  }
}

const seleccionarTipoMontoEditar = (value) => {
  conceptoEditando.value.tipo_monto = value
  if (value === 2) { // Si cambia a FIJO
    conceptoEditando.value.porcentaje_de = null
  }
}

const formatoMonto = (monto, tipoMonto) => {
  const valor = parseFloat(monto || 0)
  if (tipoMonto === 1) { // Porcentaje
    return `${valor.toFixed(5)}%`
  } else { // Fijo
    return `$${valor.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }
}

const getLabelTipo = (tipo) => {
  const labels = {
    1: 'INGRESO',
    2: 'EGRESO',
    3: 'CARGAS SOCIALES',
  }
  return labels[tipo] || 'Desconocido'
}

const getLabelTipoMonto = (tipoMonto) => {
  const labels = {
    1: '%',
    2: 'FIJO',
  }
  return labels[tipoMonto] || 'Desconocido'
}

const getLabelPorcentajeDe = (porcentajeDe) => {
  const labels = {
    1: 'HABER BASICO',
    2: 'TOTAL GANADO',
  }
  return labels[porcentajeDe] || 'Desconocido'
}

const getColorTipo = (tipo) => {
  const colores = {
    1: 'success', // INGRESO
    2: 'error',   // EGRESO
    3: 'warning', // CARGAS SOCIALES
  }
  return colores[tipo] || 'grey'
}

const cargarConceptos = async () => {
  try {
    cargando.value = true
    const response = await axios.get('/api/parametros/conceptos')
    conceptos.value = response.data
  } catch (error) {
    console.error('Error al cargar conceptos:', error)
    notificationStore.addNotification('Error al cargar los conceptos', 'error')
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

const guardarConcepto = async () => {
  try {
    guardando.value = true
    
    // Validar formulario antes de enviar
    if (!formularioValido.value) {
      throw new Error('Por favor complete todos los campos obligatorios')
    }
    
    // Preparar datos
    const datos = { 
      ...nuevoConcepto.value,
      monto: parseFloat(nuevoConcepto.value.monto) || 0
    }
    
    const response = await axios.post('/api/parametros/conceptos', datos)
    
    // Agregar el nuevo concepto a la lista
    conceptos.value.push(response.data)
    
    // Cerrar modal y resetear
    modalCrearVisible.value = false
    resetearFormularioCrear()
    
    // Mostrar notificación
    notificationStore.addNotification(`Concepto "${response.data.nombre}" creado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al crear concepto:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al crear el concepto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const editarConcepto = (concepto) => {
  // Crear copia del objeto
  conceptoEditando.value = { ...concepto }
  modalEditarVisible.value = true
}

const actualizarConcepto = async () => {
  try {
    guardando.value = true
    
    // Validar formulario antes de enviar
    if (!formularioValidoEditar.value) {
      throw new Error('Por favor complete todos los campos obligatorios')
    }
    
    const datos = { ...conceptoEditando.value }
    const id = datos.id
    
    // Remover el ID y otros campos no editables
    delete datos.id
    delete datos.creado_por
    delete datos.creado_el
    delete datos.modificado_por
    delete datos.modificado_el
    delete datos.eliminado_por
    delete datos.eliminado_el
    
    // Asegurar que monto sea número
    datos.monto = parseFloat(datos.monto) || 0
    
    const response = await axios.put(`/api/parametros/conceptos/${id}`, datos)
    
    // Actualizar en la lista
    const index = conceptos.value.findIndex(c => c.id === id)
    if (index !== -1) {
      conceptos.value[index] = response.data
    }
    
    modalEditarVisible.value = false
    notificationStore.addNotification(`Concepto "${response.data.nombre}" actualizado exitosamente`, 'success')
  } catch (error) {
    console.error('Error al actualizar concepto:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Error al actualizar el concepto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    guardando.value = false
  }
}

const mostrarConfirmacionEliminar = (concepto) => {
  conceptoConfirmandoEliminar.value = { ...concepto }
  modalConfirmacionEliminarVisible.value = true
}

const confirmarEliminarConcepto = async () => {
  try {
    procesandoEliminar.value = true
    const concepto = conceptoConfirmandoEliminar.value
    
    // Llamar al endpoint DELETE
    const response = await axios.delete(`/api/parametros/conceptos/${concepto.id}`)
    
    // Remover de la lista (ya que solo se muestran activos)
    conceptos.value = conceptos.value.filter(c => c.id !== concepto.id)
    
    notificationStore.addNotification(`Concepto "${concepto.nombre}" eliminado exitosamente`, 'success')
    
    // Cerrar modal
    modalConfirmacionEliminarVisible.value = false
    conceptoConfirmandoEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar concepto:', error)
    const errorMessage = error.response?.data?.message || 'Error al eliminar el concepto'
    notificationStore.addNotification(errorMessage, 'error')
  } finally {
    procesandoEliminar.value = false
  }
}

const resetearFormularioCrear = () => {
  nuevoConcepto.value = {
    nombre: '',
    tipo: null,
    tipo_monto: null,
    monto: null,
    porcentaje_de: null,
  }
}

// Cargar datos al inicio
onMounted(() => {
  cargarConceptos()
})
</script>