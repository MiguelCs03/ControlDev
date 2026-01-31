<template>
  <div class="login-container">
    <VCard
      class="mx-auto"
      max-width="400"
    >
      <VCardTitle class="text-h5">
        Iniciar Sesión
      </VCardTitle>
      <VCardText>
        <VForm @submit.prevent="handleLogin">
          <VTextField
            v-model="email"
            label="Correo Electrónico"
            type="email"
            required
            prepend-icon="tabler-mail"
          />
          <VTextField
            v-model="password"
            label="Contraseña"
            type="password"
            required
            prepend-icon="tabler-lock"
          />
          <VBtn
            type="submit"
            color="primary"
            block
          >
            Iniciar Sesión
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'

const email = ref('')
const password = ref('')

const handleLogin = async () => {
  try {
    const response = await axios.post('/api/login', { email: email.value, password: password.value })

    console.log('Login exitoso:', response.data)

    // Guardar token y redirigir al usuario
  } catch (error) {
    console.error('Error en el login:', error.response?.data || error)
    alert('Credenciales incorrectas')
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f5f5f5;
}
</style>
