<template>
  <div>
    <div v-if="!isCompetition" class="flex flex-col md:flex-row gap-2 items-center m-2">
      <h1 class="font-coolvetica text-4xl pl-2 my-2 text-left">
        {{ props.type?.toUpperCase() }} Records
      </h1>
      <div class="flex flex-row gap-2 w-full md:w-auto">
        <Dropdown :options="options" @update:selected="handleTimeSelection" />
        <Dropdown :options="optionsObjective" @update:selected="handleObjectiveSelection" />
        <Dropdown :options="optionsCategories" @update:selected="handleCategorySelection" />
      </div>
      <v-icon v-if="loading" name="fa-spinner" fill="white" animation="spin" />
    </div>

    <p v-if="!isCompetition" class="pl-4">{{ descriptionText }}</p>
      
    <div v-if="paginatedData.length === 0" class="pl-4">
      <p class="italic p-8 text-center">No entries</p>
    </div>
    <div
      v-for="(item, index) in paginatedData"
      :key="index"
      class="scoreboard m-2 my-4 md:m-4 text-sm md:text-lg font-coolvetica"
    >
      <div
        class="flex flex-col p-4 bg-slate-900 md:flex-row md:justify-between"
        :class="{ 'gradient-border': index === 0 && currentPage === 1 }"
      >
        <div class="flex items-center justify-between md:justify-start md:w-full">
          <div class="flex-shrink-0 w-1/12 flex items-center">
            <span>{{ formatRank((currentPage - 1) * itemsPerPage + index + 1) }}</span>
          </div>
          <div class="flex-shrink-0 w-1/4 flex items-center">
            <v-icon
              v-visible="index === 0 && currentPage === 1"
              name="fa-trophy"
              fill="gold"
              animation="pulse"
              speed="slow"
              class="mr-2"
            />
            <span><a class="no-underline hover:bg-transparent" :href="'https://www.trackmania.io/#/player/' + item.accountId" target="_blank">{{ item.displayName }}</a></span>
          </div>
          <div class="flex-shrink-0 w-1/2 flex flex-row items-center">
            <img :src="objectiveImages.at" class="h-6 mx-2" />
            <span>{{ item.goals }}</span>
            
            <!-- For 'rmc' type -->
            <template v-if="props.type === 'rmc' && selectedObjective != 'bronze'">
              <img :src="objectiveImages.secondary" class="h-6 mx-2" />
              <span>{{ isRMC(item) ? item.belowGoals : '' }}</span>
            </template>

            <!-- For 'rms' type -->
            <template v-if="props.type === 'rms'">
              <img :src="skipImage" class="h-6 mx-2" />
              <span>{{ item.skips }}</span>
              <span>{{ isRMS(item) && item.timeSurvived != null ? ("&nbsp;|&nbsp;" + formatTimeSurvived(item.timeSurvived)) : "" }}</span>
            </template>
          </div>
          <div class="flex-shrink-0 w-1/12 flex items-center justify-end md:hidden">
            <!-- space for icon links -->
          </div>
        </div>
        <div class="flex items-center justify-between md:w-1/4 mt-2 md:mt-0 self-end">
          <span>{{ formatTimeStamp(item.submitTime) }}</span>
          <div class="flex-shrink-0 w-1/12 items-center justify-end hidden md:flex">
            <!-- space for icon links -->
          </div>
        </div>
        <div class="flex items-center justify-between md:w-1/8 mt-2 md:mt-0 self-end">
          <span v-visible="item.videoLink"><a :href="item.videoLink" target="_blank">   <v-icon name="fa-play-circle" fill="gold" /></a></span>
          <div class="flex-shrink-0 w-1/12 items-center justify-end hidden md:flex">
            <!-- space for icon links -->
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="paginatedData.length > 0" class="flex justify-between p-4">
      <button v-visible="currentPage > 1" @click="prevPage">Previous</button>
      <span>Page {{ currentPage }} of {{ totalPages }} ({{ sortedData.length }} results)</span>
      <button v-visible="currentPage < totalPages" @click="nextPage">Next</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import Dropdown from './Dropdown.vue';

import type { RecordDataRMC, RecordDataRMS } from '@/types';

// Import images
import atImage from '@/assets/img/at.png';
import goldImage from '@/assets/img/gold.png';
import silverImage from '@/assets/img/silver.png';
import bronzeImage from '@/assets/img/bronze.png';
import skipImage from '@/assets/img/skip.png';
import wrImage from '@/assets/img/wr.png';
import { isRMC, isRMS, formatTimeStamp, formatTimeSurvived, formatRank } from '@/utils';

// Define props
const props = defineProps<{
  type: 'rmc' | 'rms',
  isCompetition?: boolean
}>();

// Initialize reactive variables
const currentYear = new Date().getFullYear();
const years = Array.from({ length: currentYear - 2021 + 1 }, (_, i) =>
  (currentYear - i).toString()
);

const options = ref([...years, 'all']);
const optionsObjective = ref(['author', 'gold', 'silver', 'bronze', 'WR']);
const optionsCategories = ref(['standard', 'classic']);

const selectedTime = ref<string | null>(currentYear.toString());
const selectedObjective = ref<string | null>('author');
const selectedCategory = ref<string | null>('standard');

const loading = ref(false);
const rmcData = ref<RecordDataRMC[]>([]);
const rmsData = ref<RecordDataRMS[]>([]);

// Define computed properties
const headers = computed(() => {
  return props.type === 'rmc'
    ? [
        { title: 'DisplayName', key: 'displayName' },
        { title: 'Goals', key: 'goals' },
        { title: 'Below Goals', key: 'belowGoals' },
        { title: 'SubmitTime', key: 'submitTime' },
        { title: 'Verified', key: 'verified' }
      ]
    : [
        { title: 'DisplayName', key: 'displayName' },
        { title: 'Goals', key: 'goals' },
        { title: 'Skips', key: 'skips' },
        { title: 'SubmitTime', key: 'submitTime' },
        { title: 'Time Survived', key: 'timeSurvived' }
      ];
});

const data = computed(() => {
  return props.type === 'rmc' ? rmcData.value : rmsData.value;
});

const sortedData = computed(() => {
  return data.value.slice().sort((a, b) => {
    if (props.type === 'rmc') {
      if (isRMC(a) && isRMC(b)) {
        if (b.goals !== a.goals) {
          return b.goals - a.goals;
        } else if ((b.belowGoals ?? 0) !== (a.belowGoals ?? 0)) {
          return (b.belowGoals ?? 0) - (a.belowGoals ?? 0);
        } else {
          return new Date(a.submitTime).getTime() - new Date(b.submitTime).getTime();
        }
      }
    } else if (props.type === 'rms') {
      if (isRMS(a) && isRMS(b)) {
        if (b.goals !== a.goals) {
          return b.goals - a.goals;
        } else {
          return b.timeSurvived - a.timeSurvived;
        }
      }
    }
    return 0; // Default return if types are not matched
  });
});

const totalPages = computed(() => {
  return Math.ceil(sortedData.value.length / itemsPerPage.value);
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedData.value.slice(start, end);
});

// Pagination methods
const currentPage = ref(1);
const itemsPerPage = ref(10);

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Fetch data from API
const fetchData = async (year: string | null, objective: string | null, category: string | null) => {
  loading.value = true;
  try {
    rmcData.value = [];
    rmsData.value = [];
    if (props.type === 'rmc') {
      if (props.isCompetition) {
        const response = await axios.get('https://www.flinkblog.de/RMC/api/breaktherecord.php');
        rmcData.value = response.data;
      } else {
        const response = await axios.get('https://www.flinkblog.de/RMC/api/rmc.php', {
          params: { year, objective, category }
        });
        rmcData.value = response.data;
      }
    } else {
      const response = await axios.get('https://www.flinkblog.de/RMC/api/rms.php', {
        params: { year, objective, category }
      });
      rmsData.value = response.data;
    }
  } catch (error) {
    console.error(`Error fetching ${props.type} data:`, error);
  } finally {
    loading.value = false;
  }
};

const objectiveImages = computed(() => {
  switch (selectedObjective.value?.toLowerCase()) {
    case 'gold':
      return { at: goldImage, secondary: silverImage };
    case 'silver':
      return { at: silverImage, secondary: bronzeImage };
    case 'bronze':
      return { at: bronzeImage, secondary: skipImage };
    case 'wr':
      return { at: wrImage, secondary: atImage };
    default:
      return { at: atImage, secondary: goldImage };
  }
});
// Event handlers
const handleTimeSelection = (selected: string) => {
  selectedTime.value = selected;
  fetchData(selectedTime.value, selectedObjective.value, selectedCategory.value);
};

const handleObjectiveSelection = (selected: string) => {
  selectedObjective.value = selected;
  fetchData(selectedTime.value, selectedObjective.value, selectedCategory.value);
};

const handleCategorySelection = (selected: string) => {
  selectedCategory.value = selected;
  fetchData(selectedTime.value, selectedObjective.value, selectedCategory.value);
}

const descriptionText = computed(() => {
  return props.type === 'rmc'
    ? 'Collect as many medals as possible within an hour!'
    : 'Survive as long as you can, every goal medal replenishes your timer!';
});

// Watchers
onMounted(() => {
  fetchData(selectedTime.value, selectedObjective.value, selectedCategory.value);
});

watch([selectedTime, selectedObjective, selectedCategory], ([newTime, newObjective, newCategory]) => {
  fetchData(newTime, newObjective, newCategory);
});
</script>
