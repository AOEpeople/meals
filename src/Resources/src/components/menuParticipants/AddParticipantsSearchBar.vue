<template>
  <Combobox
    v-slot="{ open }"
    v-model="selectedProfile"
    as="span"
    class="relative w-full"
    nullable
  >
    <div
      ref="combobox"
      class="relative w-full"
      @click="handleClick"
    >
      <ComboboxLabel v-if="slot.label">
        <slot name="label" />
      </ComboboxLabel>
      <div
        class="flex w-full flex-row items-center overflow-hidden border-[#CAD6E1] bg-white text-left text-[14px] font-medium text-[#B4C1CE] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2"
        :class="openProp ? 'rounded-t-[23px] border-x-2 border-b-[1px] border-t-2' : 'rounded-full border-2'"
      >
        <ComboboxInput
          class="w-full truncate border-none px-4 py-2 text-[#9CA3AF] focus:outline-none"
          @change="filter = $event.target.value"
        />
        <XIcon
          v-if="filter !== ''"
          class="mr-4 h-full w-10 cursor-pointer justify-self-end px-1 py-2 text-[#9CA3AF] transition-transform hover:scale-[120%]"
          aria-hidden="true"
          @click="filter = ''"
        />
      </div>
      <div
        v-if="openProp"
        class="absolute z-10 w-full"
      >
        <ComboboxOptions
          class="scrollbar-styling absolute z-0 max-h-60 w-full overflow-y-auto overflow-x-hidden rounded-b-[23px] border-x-2 border-b-2 border-[#CAD6E1] bg-white shadow-lg focus:outline-none"
          static
        >
          <li
            v-if="filteredProfiles.length === 0 && profileFilter.length > 2"
            class="truncate p-4 text-[14px] text-[#9CA3AF]"
          >
            <span class="size-full">
              {{ t('menu.notFound') }}
            </span>
          </li>
          <li
            v-else-if="filteredProfiles.length === 0 && profileFilter.length < 3"
            class="truncate p-4 text-[14px] text-[#9CA3AF]"
          >
            <span class="size-full">
              {{ t('menu.shortQuery') }}
            </span>
          </li>
          <ComboboxOption
            v-for="profile in filteredProfiles"
            :key="profile.user"
            as="template"
            :value="profile"
          >
            <LazyListItem
              :min-height="34"
              :render-on-idle="true"
              class="relative cursor-pointer px-4 py-1 text-left text-[14px] font-medium text-[#9CA3AF] hover:bg-[#FAFAFA]"
              @click="selectProfile(profile as IProfile)"
            >
              <span
                data-cy="add-part-li"
                class="size-full truncate"
              >
                {{ getDisplayName(profile as IProfile) }}
              </span>
            </LazyListItem>
          </ComboboxOption>
        </ComboboxOptions>
      </div>
    </div>
  </Combobox>
</template>

<script setup lang="ts">
import useDetectClickOutside from '@/services/useDetectClickOutside';
import { type IProfile, useProfiles } from '@/stores/profilesStore';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption, ComboboxLabel } from '@headlessui/vue';
import { computed, onMounted, ref, useSlots } from 'vue';
import { XIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';
import { refThrottled } from '@vueuse/core';
import LazyListItem from '../misc/LazyListItem.vue';

const props = defineProps<{
  weekId: number;
}>();

const { ProfilesState, fetchAbsentingProfiles } = useProfiles(props.weekId);
const { t } = useI18n();
const slot = useSlots();

const emit = defineEmits(['profileSelected']);

const combobox = ref<HTMLElement | null>(null);
const selectedProfile = ref<IProfile | null>(null);
const openProp = ref(false);

const filter = ref('');
const profileFilter = refThrottled(filter, 350);

onMounted(async () => {
  await fetchAbsentingProfiles();
});

const filteredProfiles = computed(() => {
  const output = [];
  if (profileFilter.value.length < 3) {
    return [];
  } else {
    output.push(
      ...ProfilesState.profiles.filter(
        (profile) =>
          profile.fullName.toLowerCase().includes(profileFilter.value.toLowerCase()) ||
          profile.roles.join(' ').toLowerCase().includes(profileFilter.value.toLowerCase())
      )
    );
  }

  return output.sort((a, b) => {
    if (a.fullName < b.fullName) return -1;
    else if (a.fullName > b.fullName) return 1;
    else return 0;
  });
});

function getDisplayName(profile: IProfile) {
  if (profile.roles.includes('ROLE_GUEST')) {
    return `(${t('menu.guest')}) ${profile.fullName}`;
  }
  return profile.fullName;
}

function handleClick() {
  openProp.value = true;
  useDetectClickOutside(combobox, () => (openProp.value = false));
}

function selectProfile(profile: IProfile) {
  filter.value = '';
  emit('profileSelected', profile);
}
</script>

<style scoped>
.scrollbar-styling {
  scrollbar-width: none;
}

.scrollbar-styling::-webkit-scrollbar {
  display: none;
}
</style>
