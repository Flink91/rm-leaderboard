<template>
  <nav class="p-4 font-coolvetica md:text-xl text-md">
    <div class="container mx-auto flex items-center justify-between">
      <!-- Left Links -->
      <div class="flex space-x-4 items-center">
        <router-link to="/" class="text-white border-b-4 p-1 hover:border-white">Home</router-link>
        <router-link :to="{ path: '/', hash: '#rules' }" class="text-white border-b-4 p-1 hover:border-white">Rules</router-link>
      </div>

      <!-- Logo -->
      <div class="flex-shrink-0 w-32 md:w-80">
        <img src="@/assets/img/logo.webp" alt="Logo" class="w-full" />
      </div>

      <!-- Right Links -->
      <div class="flex space-x-4 items-center">
        <router-link :to="{ path: '/', hash: '#howto' }" class="text-white border-b-4 p-1 hover:border-white">How to Play</router-link>
        <router-link :to="{ path: '/', hash: '#about' }" class="text-white border-b-4 p-1 hover:border-white">About</router-link>
      </div>
    </div>
  </nav>
</template>

<style scoped>
a {
  text-decoration: none;
}
</style>
