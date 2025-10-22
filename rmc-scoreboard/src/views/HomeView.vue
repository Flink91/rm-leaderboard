<template>
  <div class="">
    <div class="flex justify-center pb-6 px-4 md:px-12">
      <router-link to="/breaktherecord" class="md:w-4/5 xl:w-1/2 flex justify-center">
        <img src="@/assets/img/break-banner.webp" class="w-full"/>
      </router-link>
    </div>

    <div class="flex flex-col xl:flex-row min-h-screen bg-gradient">
      <div class="xl:w-1/2">
        <Scoreboard :type="'rmc'" />
      </div>

      <div class="xl:w-1/2">
        <Scoreboard :type="'rms'" />
      </div>
    </div>
    <div class="flex flex-row justify-around items-center bg-gray-100 text-gray-900 lg_p-10 p-12">
      <img src="@/assets/img/peepocheer.webp" />
      <h1 class="md:text-2xl align-middle text-purple-700">
        Help this project by
        <a
          target="_blank"
          href="https://www.twitch.tv/flinktm"
          class="underline text-blue-500 hover:text-blue-700"
          >dropping a sub!</a
        >
      </h1>
      <h1 class="md:text-2xl align-middle text-purple-700">
        Or by
        <a
          target="_blank"
          href="https://patreon.com/Greep"
          class="underline text-blue-500 hover:text-blue-700"
          >becoming a sponsor!</a
        >
      </h1>
      <img src="@/assets/img/peepocheer.webp" />
    </div>
    <div class="flex bg-pattern text-gray-900 lg_p-10 p-12">
      <Rules />
    </div>
    <div class="flex bg-color lg:p-10 p-4">
      <Howto />
    </div>
    <div class="flex bg-pattern text-gray-900 lg_p-10 p-12">
      <Videos />
    </div>
    <div class="flex bg-footer border-t-8 border-black lg:p-10 p-4">
      <About />
    </div>
  </div>
</template>

<script setup lang="ts">
import Scoreboard from '@/components/Scoreboard.vue';
import Rules from '@/components/Rules.vue';
import Howto from '@/components/Howto.vue';
import About from '@/components/About.vue';
import Videos from '@/components/Videos.vue';
</script>
