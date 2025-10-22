<template>
  <div>
    <h1 class="font-coolvetica text-4xl md:text-5xl my-2 text-center w-full text-gray-200">RMC: Break the Record Competition</h1>
    <p class="text-base md:text-2xl px-7 my-5 text-center">From {{ formatDate(startDate) }} CEST to {{ formatDate(endDate) }} CET, participate in the RMC: Break the Record competition hosted by BIG and Acer Predator!</p>
    <p class="text-base md:text-2xl px-7 my-5 text-center">During the competition, players will battle to earn as many ATs as possible within an hour under the new rules. Once the timer runs out, the top four players on the leaderboard will share a prize pool of 1000€!</p>
    <div class="flex flex-col xl:flex-row justify-center items-center m-4 bg-color font-coolvetica">
      <div class="flex justify-center h-[80vh] xl:h-1/2">
        <img src="@/assets/img/break-info.webp" class="px-10" />
      </div>
      <div class="lg:w-3/4 py-3">
        <div class="flex justify-center flex-row flex-wrap">
          <template v-if="eventHasStarted">
            <h1 class="font-coolvetica text-3xl md:text-4xl mt-10 text-center w-full text-gray-200">Competition Leaderboard</h1>
            <div class="w-full xl:w-3/4 min-h-60">
              <Scoreboard :type="'rmc'" :is-competition="true" />
            </div>
            <h1 class="font-coolvetica text-3xl md:text-4xl my-2 text-center w-full text-gray-200">Time Remaining</h1>
            <Countdown :target-date="endDate.toISOString()" />
          </template>
          <template v-else>
            <h1 class="font-coolvetica text-3xl md:text-4xl mt-10 text-center w-full text-gray-200">Starts in</h1>
            <Countdown :target-date="startDate.toISOString()" />
          </template>
        </div>
      </div>
    </div>
  </div>

  <div class="flex flex-col lg:flex-row lg:gap-16 p-12 bg-pattern font-coolvetica text-xl text-gray-900">
    <div class="lg:w-1/2">
      <h1 class="text-4xl my-2 text-left">Details</h1>
      <ul class="list-disc list-inside pl-4 pb-4">
        <li>Duration: {{ formatDate(startDate) }} CEST to {{ formatDate(endDate) }} CET</li>
        <li>Prize pool: 1000€
          <ul class="list-disc list-inside ml-6">
            <li>1st: 500€</li>
            <li>2nd: 300€</li>
            <li>3rd: 150€</li>
            <li>4th: 50€</li>
          </ul>
        </li>
        <li>If two players get the same number of ATs, the following tiebreakers apply:
          <ul class="list-disc list-inside ml-6">
            <li>Most gold skips</li>
            <li>Earliest upload</li>
          </ul>
        </li>
      </ul>
    </div>

    <div class="lg:w-1/2">
      <h1 class="text-4xl my-2 text-left">Rules</h1>
      <p>To participate in the competition, you need to follow these rules:</p>
      <ul class="list-disc list-inside pl-4">
        <li>Use the latest
          <a href="https://openplanet.dev/plugin/mxrandom" target="_blank">ManiaExchange Random Map Picker</a>
          plugin version
        </li>
        <li>Follow the basic <router-link :to="{ path: '/', hash: '#rules' }">RMC rules</router-link></li>
        <li>Set the goal medal to Author and mode to Challenge</li>
        <li>The following settings need to be set to their default:
          <ul class="list-disc list-inside ml-6">
            <li>Free Skips: 1 per run</li>
            <li>Duration: 60 minutes</li>
            <li>Custom filters: Disabled</li>
          </ul>
        </li>
        <li>Runs need to be livestreamed / recorded to be eligible.</li>
        <li class="ml-6">If a run is not streamed or fails to be uploaded to the leaderboard, you can send a recording to fort_tm on Discord.</li>
        <li>You can't use the following plugins during a run:
          <ul class="list-disc list-inside ml-6">
            <li>Unclaimed Checkpoint Indicator</li>
            <li>FreeCam: Show CP (Checkpoint Finder)</li>
            <li>Ghosts++</li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import Scoreboard from '@/components/Scoreboard.vue';
import Countdown from '@/components/Countdown.vue';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { formatDate } from '@/utils';

const startDate = new Date('2025-10-25T18:00:00.000Z');
const endDate = new Date('2025-10-31T22:59:59.999Z');

const now = ref(Date.now());
let timer: number | undefined;

onMounted(() => {
  timer = setInterval(() => {
    now.value = Date.now();
  }, 1000);
});

onBeforeUnmount(() => {
  clearInterval(timer);
});

const eventHasStarted = computed(() => {
  return now.value >= startDate.getTime();
});
</script>

<style>
</style>
