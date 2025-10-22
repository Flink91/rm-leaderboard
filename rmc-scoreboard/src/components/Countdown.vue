<script setup lang="ts">
import {ref, onMounted, onBeforeUnmount } from 'vue';
import { formatNumber } from '@/utils';

const props = defineProps<{
  targetDate: string;
}>();

let endDate = ref(new Date());
let interval = ref(0);

let days = ref(0);
let hours = ref(0);
let minutes = ref(0);
let seconds = ref(0);

const SECOND_MS = 1000;
const MINUTE_MS = SECOND_MS * 60;
const HOUR_MS = MINUTE_MS * 60;
const DAY_MS = HOUR_MS * 24;

function updateTimer() {
  const remaining = endDate.value.getTime() - Date.now();

  days.value = Math.max(0, Math.floor(remaining / DAY_MS));
  hours.value = Math.max(0, Math.floor((remaining % DAY_MS) / HOUR_MS));
  minutes.value = Math.max(0, Math.floor((remaining % HOUR_MS) / MINUTE_MS));
  seconds.value = Math.max(0, Math.floor((remaining % MINUTE_MS) / SECOND_MS));

  if (remaining <= 0) {
    clearInterval(interval.value);
  }
}

onMounted(() => {
  endDate.value = new Date(props.targetDate);
  interval.value = setInterval(updateTimer, 1000);
});

onBeforeUnmount(() => {
  clearInterval(interval.value);
});

</script>

<template>
  <div class="flex items-center my-10 space-x-5 text-gray-400">
    <div class="flex flex-col items-center">
      <div class="text-4xl font-bold">{{ formatNumber(days) }}</div>
      <div class="text-xs font-bold">DAYS</div>
    </div>

    <div class="text-4xl font-bold">:</div>

    <div class="flex flex-col items-center">
      <div class="text-4xl font-bold">{{ formatNumber(hours) }}</div>
      <div class="text-xs font-bold">HOURS</div>
    </div>

    <div class="text-4xl font-bold">:</div>

    <div class="flex flex-col items-center">
      <div class="text-4xl font-bold">{{ formatNumber(minutes) }}</div>
      <div class="text-xs font-bold">MINUTES</div>
    </div>

    <div class="text-4xl font-bold">:</div>

    <div class="flex flex-col items-center">
      <div class="text-4xl font-bold">{{ formatNumber(seconds) }}</div>
      <div class="text-xs font-bold">SECONDS</div>
    </div>
  </div>
</template>
