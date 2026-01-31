<script setup>
import navItems from '@/navigation/vertical'
import { themeConfig } from '@themeConfig'
import { fetchMenus, menus } from '@/store/menu'
import { onMounted } from 'vue'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

// Cargar menús dinámicos al montar el componente (solo si aún no se han cargado)
onMounted(async () => {
  // Solo cargar menús si no hay menús cargados todavía Y hay un token válido
  const token = localStorage.getItem('accessToken') || useCookie('accessToken').value
  
  if (menus.value.length === 0 && token) {
    console.debug('[Layout] Cargando menús al montar layout')
    try {
      await fetchMenus()
    } catch (error) {
      // Si falla, no hacer nada. El error ya se maneja en el interceptor de axios
      console.debug('[Layout] Error al cargar menús (probablemente ya se están cargando):', error.message)
    }
  } else if (menus.value.length === 0 && !token) {
    console.debug('[Layout] No hay token disponible, no se cargan menús')
  } else {
    console.debug('[Layout] Menús ya cargados, usando existentes')
  }
})
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
          />
        </IconBtn>

        <!-- Hidden: Search -->
        <template v-if="false">
          <NavSearchBar class="ms-lg-n3" />
        </template>

        <VSpacer />

        <!-- Hidden: Language Switcher -->
        <template v-if="false">
          <NavBarI18n
            v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
            :languages="themeConfig.app.i18n.langConfig"
          />
        </template>
        <NavbarThemeSwitcher />
        <!-- Hidden: Shortcuts & Notifications -->
        <template v-if="false">
          <NavbarShortcuts />
          <NavBarNotifications class="me-1" />
        </template>
        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <TheCustomizer />
  </VerticalNavLayout>
</template>
