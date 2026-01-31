<template>
  <VContainer class="position-fixed" style="top: 20px; right: 20px; max-width: 400px; z-index: 9999; pointer-events: none;">
    <transition-group
      name="alert"
      tag="div"
      class="d-flex flex-column gap-2"
    >
      <VAlert
        v-for="notification in notificationStore.notifications"
        :key="notification.id"
        :type="notification.type"
        :title="getTitleByType(notification.type)"
        closable
        class="pointer-events-auto"
        @click:close="notificationStore.removeNotification(notification.id)"
      >
        {{ notification.message }}
      </VAlert>
    </transition-group>
  </VContainer>
</template>

<script setup>
import { useNotificationStore } from '@/store/notification'

const notificationStore = useNotificationStore()

const getTitleByType = (type) => {
  const titles = {
    success: '✓ Éxito',
    error: '✗ Error',
    warning: '⚠ Advertencia',
    info: 'ℹ Información',
  }
  return titles[type] || 'Información'
}
</script>

<style scoped>
.pointer-events-auto {
  pointer-events: auto;
}

.alert-enter-active,
.alert-leave-active {
  transition: all 0.3s ease;
}

.alert-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.alert-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
