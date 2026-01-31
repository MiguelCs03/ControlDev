<script setup>
import miscUnderMaintenance from '@images/pages/misc-under-maintenance.png'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import miscMaskDark from '@images/pages/misc-mask-dark.png'
import miscMaskLight from '@images/pages/misc-mask-light.png'

// Define metadata para la página
definePage({
  meta: {
    section: 'dashboard-inicio',
    action: 'read',
    subject: 'dashboard-inicio',
  },
})

const authThemeMask = useGenerateImageVariant(miscMaskLight, miscMaskDark)

const icons = [
  'tabler-box', 
  'tabler-truck', 
  'tabler-building-warehouse',
  'tabler-device-laptop', 
  'tabler-chart-bar', 
  'tabler-users',
  'tabler-settings', 
  'tabler-shopping-cart', 
  'tabler-report-money',
  'tabler-scan', 
  'tabler-qrcode', 
  'tabler-receipt'
]
</script>

<template>
  <div class="misc-wrapper">
    <VContainer class="fill-height">
      <VRow
        no-gutters
        class="align-center justify-center text-center"
      >
        <VCol
          cols="12"
          md="8"
          class="text-center"
        >
          <!-- Illustration -->
          <div class="illustration-container mb-12">
            <img
              :src="miscUnderMaintenance"
              alt="En Construcción"
              class="misc-illustration flip-in-diag-1-br"
            />
            <div class="mask-bg d-none d-md-block">
              <img
                :src="authThemeMask"
                class="mask-image"
              />
            </div>
          </div>

          <!-- Content -->
          <div class="text-content slide-in-bottom">
            <h1 class="text-h3 font-weight-bold mb-3 text-primary">
              🚧 En Construcción 🚧
            </h1>
            <p class="text-h6 text-medium-emphasis mb-8">
              Estamos trabajando arduamente en esta sección para brindarte nuevas y emocionantes funcionalidades.
              <br>¡Mantente al tanto!
            </p>

            <!-- Icons Marquee -->
            <div class="marquee-wrapper mb-8 mt-6">
              <div class="marquee-track">
                <!-- Set 1 -->
                <div class="marquee-group">
                  <div v-for="(icon, index) in icons" :key="index" class="marquee-icon-container">
                    <VIcon :icon="icon" size="32" class="marquee-icon text-medium-emphasis" />
                  </div>
                </div>
                <!-- Set 2 (Duplicate for loop) -->
                <div class="marquee-group">
                  <div v-for="(icon, index) in icons" :key="'dup-'+index" class="marquee-icon-container">
                    <VIcon :icon="icon" size="32" class="marquee-icon text-medium-emphasis" />
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-center gap-4 mt-8">
              <VBtn
                variant="text"
                color="secondary"
                href="/"
              >
                Volver al Inicio
              </VBtn>
            </div>
          </div>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.misc-wrapper {
  min-height: 80vh;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.illustration-container {
  position: relative;
  display: inline-block;
  
  .misc-illustration {
    max-width: 100%;
    max-height: 400px;
    z-index: 2;
    position: relative;
    filter: drop-shadow(0 10px 20px rgba(var(--v-theme-primary), 0.2));
    animation: float 6s ease-in-out infinite;
  }

  .mask-bg {
    position: absolute;
    bottom: -10%;
    left: 50%;
    transform: translateX(-50%);
    width: 120%;
    z-index: 1;
    opacity: 0.5;
    
    .mask-image {
      width: 100%;
    }
  }
}

.marquee-wrapper {
  width: 100%;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  overflow: hidden;
  mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
}

.marquee-track {
  display: flex;
  width: fit-content;
  animation: scroll-left 20s linear infinite;
  
  &:hover {
    animation-play-state: paused;
  }
}

.marquee-group {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.marquee-icon-container {
  padding: 0 2rem;
  transition: transform 0.3s ease;
  
  &:hover {
    transform: scale(1.2);
    
    .marquee-icon {
      color: rgb(var(--v-theme-primary)) !important;
    }
  }
}

@keyframes scroll-left {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

/* Animations */
@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
  100% { transform: translateY(0px); }
}

.slide-in-bottom {
  animation: slide-in-bottom 0.8s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
}

@keyframes slide-in-bottom {
  0% {
    transform: translateY(100px);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.flip-in-diag-1-br {
	animation: flip-in-diag-1-br 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
}

@keyframes flip-in-diag-1-br {
  0% {
    transform: rotate3d(1, 1, 0, -80deg);
    opacity: 0;
  }
  100% {
    transform: rotate3d(1, 1, 0, 0deg);
    opacity: 1;
  }
}
</style>
