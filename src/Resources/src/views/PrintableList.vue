<template>
  <div class="text-center">
    <svg
      class="mr-2 inline min-w-[36px] align-text-bottom text-primary"
      width="36"
      height="36"
      viewBox="0 0 14 20"
      fill="#173d7a"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g clip-path="url(#clip0_2_216)">
        <path
          d="M14.0016 5.55655C14.0016 2.92526 12.7979 0 10.7548 0C8.71168 0 7.49414 2.92526 7.49414 5.55655C7.49414 7.66446 8.43418 8.94784 9.71416 9.36727L9.04815 18.1036C9.02993 18.3462 9.06058 18.5902 9.1382 18.8199C9.21581 19.0497 9.33869 19.2604 9.49907 19.4386C9.65945 19.6168 9.85385 19.7587 10.07 19.8553C10.2861 19.952 10.5193 20.0012 10.7548 20C10.9913 20.0004 11.2253 19.9508 11.4426 19.8543C11.6599 19.7578 11.8557 19.6164 12.0182 19.4388C12.1806 19.2612 12.3062 19.0511 12.3872 18.8215C12.4682 18.5919 12.5029 18.3476 12.4892 18.1036L11.7954 9.36727C13.0789 8.92633 14.0189 7.64295 14.0189 5.55655H14.0016Z"
          fill="#173d7a"
        />
        <path
          d="M5.34241 0.587805H5.27997C5.20346 0.588722 5.12789 0.60539 5.05768 0.636835C4.98747 0.66828 4.92403 0.713872 4.87107 0.770945C4.8181 0.828019 4.77667 0.89543 4.74921 0.969239C4.72174 1.04305 4.70878 1.12177 4.71109 1.20082L4.85678 5.83247C4.85143 5.98205 4.79142 6.12397 4.68882 6.22963C4.58623 6.33528 4.4487 6.39681 4.30395 6.40181C4.1592 6.40681 4.01799 6.35491 3.9088 6.25658C3.79961 6.15824 3.73055 6.02078 3.71555 5.87191L3.67046 1.1578C3.66682 1.00503 3.60506 0.859891 3.49857 0.753862C3.39208 0.647833 3.24944 0.589457 3.10158 0.59139H3.01833C2.87047 0.589457 2.72783 0.647833 2.62134 0.753862C2.51485 0.859891 2.45309 1.00503 2.44945 1.1578L2.38701 5.85398C2.38337 6.00676 2.32161 6.15189 2.21512 6.25792C2.10863 6.36395 1.96599 6.42232 1.81813 6.42039C1.74162 6.41994 1.66597 6.40373 1.59559 6.37272C1.52521 6.34171 1.46152 6.29652 1.40823 6.23978C1.35495 6.18304 1.31314 6.1159 1.28524 6.04227C1.25735 5.96864 1.24393 5.89001 1.24578 5.81096L1.388 1.17931C1.39232 1.09993 1.38072 1.02049 1.35393 0.945959C1.32714 0.87143 1.28575 0.803425 1.23234 0.746207C1.17892 0.688989 1.11465 0.643791 1.04354 0.613449C0.972438 0.583107 0.896033 0.568274 0.819119 0.569881H0.756681C0.609856 0.571533 0.469187 0.631092 0.363443 0.736377C0.257699 0.841662 0.194869 0.984718 0.187801 1.13629L0.000487147 5.93643C-0.0254684 6.60434 0.156186 7.26329 0.518846 7.81678C0.881506 8.37027 1.40612 8.78921 2.01585 9.01225L1.32209 18.0856C1.3033 18.3286 1.33369 18.5731 1.41131 18.8034C1.48894 19.0336 1.6121 19.2447 1.77296 19.4231C1.93382 19.6014 2.12886 19.7433 2.34566 19.8395C2.56246 19.9357 2.79627 19.9842 3.0322 19.982C3.26663 19.979 3.49804 19.9269 3.71245 19.8289C3.92686 19.7309 4.11982 19.589 4.27966 19.4117C4.4395 19.2344 4.56291 19.0255 4.64243 18.7976C4.72195 18.5697 4.75593 18.3275 4.74231 18.0856L4.04855 9.01225C4.65589 8.78504 5.17817 8.36521 5.54076 7.81279C5.90335 7.26037 6.08767 6.60363 6.06739 5.93643L5.89741 1.1578C5.89054 1.00864 5.82966 0.867608 5.72685 0.762685C5.62403 0.657761 5.48681 0.596618 5.34241 0.59139V0.587805Z"
          fill="#173d7a"
        />
      </g>
      <defs>
        <clipPath id="clip0_2_216">
          <rect
            width="21"
            height="21"
            fill="white"
          />
        </clipPath>
      </defs>
    </svg>
    <h1 class="inline">
      {{ t('printList.title') + dateString }}
    </h1>
  </div>
  <div
    class="mb-20 px-4 sm:px-6 lg:px-8"
    data-cy="printTable"
  >
    <div class="mt-8 flex flex-col">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
          <div class="aoe-shadow overflow-hidden ring-1 ring-black/5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <thead class="bg-white">
                <tr class="divide-y divide-gray-200">
                  <th
                    scope="col"
                    class="px-4 py-3.5 text-left text-sm font-semibold text-primary"
                  />
                  <th
                    v-for="(mealName, index) in mealNames"
                    :key="index"
                    scope="col"
                    class="px-3 py-3.5 text-center text-sm font-semibold text-primary"
                  >
                    {{ mealName }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <template
                  v-for="(participant, slotName) in listData.data"
                  :key="String(slotName)"
                >
                  <tr class="border-t border-gray-200">
                    <th
                      colspan="5"
                      scope="colgroup"
                      class="bg-gray-50 px-4 py-2 text-left text-sm font-semibold text-gray-900 sm:px-6"
                    >
                      {{ String(slotName) }}
                    </th>
                  </tr>
                  <tr
                    v-for="(participations, participantName, index) in participant"
                    :key="index"
                    :class="[index === 0 ? 'border-gray-300' : 'border-gray-200', 'border-t']"
                  >
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-primary sm:pl-6">
                      {{ String(participantName) }}
                    </td>
                    <td
                      v-for="(meal, mealId) in listData.meals"
                      :key="`${index}${String(mealId)}`"
                      class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-500"
                    >
                      <svg
                        v-if="participations.booked.includes(parseInt(String(mealId)))"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="m-auto block size-6 text-primary"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </td>
                  </tr>
                </template>
                <tr class="border-t border-gray-200">
                  <th
                    scope="colgroup"
                    class="bg-gray-50 px-4 py-2 text-left text-sm font-semibold text-gray-900 sm:px-6"
                  >
                    {{ t('printList.participations') }}
                  </th>
                  <th
                    v-for="(totalSum, index) in participationCount"
                    :key="index"
                    scope="colgroup"
                    class="bg-gray-50 px-4 py-2 text-center text-sm font-semibold text-gray-900 sm:px-6"
                  >
                    {{ totalSum }}
                  </th>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <ActionButton
    :btn-text="t('printList.download')"
    :action="Action.DOWNLOAD"
    @click="pdfCreator?.downloadPdf()"
  />
  <PdfCreator
    ref="pdfCreator"
    filename="teilnehmerliste"
    :content-hidden="true"
  >
    <PrintListPdfTemplate
      :date-string="dateString"
      :list-data="
        // @ts-ignore
        listData as ListData
      "
      :meal-names="mealNames"
      :participation-count="participationCount"
      class="mt-10"
    />
  </PdfCreator>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { type ListData, usePrintableListData } from '@/api/getPrintableListData';
import { computed, ref } from 'vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import ActionButton from '@/components/misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import PrintListPdfTemplate from '@/components/printableList/PrintListPdfTemplate.vue';
import PdfCreator from '@/components/pdfCreator/PdfCreator.vue';

const progress = useProgress().start();
const { t, locale } = useI18n();

const { listData } = usePrintableListData();
const pdfCreator = ref(null);

const mealNames = computed(() => {
  const names: string[] = [];
  Object.values(listData.meals).forEach((meal) => {
    names.push(locale.value === 'en' ? meal.title.en : meal.title.de);
  });
  return names;
});

const participationCount = computed(() => {
  const count: number[] = [];
  Object.values(listData.meals).forEach((meal) => {
    count.push(meal.participations ? meal.participations : 0);
  });
  return count;
});

const dateString = computed(() =>
  new Date(Date.parse(listData.day.date)).toLocaleDateString(locale.value, {
    weekday: 'long',
    month: 'numeric',
    day: 'numeric'
  })
);

progress.finish();
</script>
