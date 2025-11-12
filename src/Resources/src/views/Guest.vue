<template>
  <GuestCompletion
    v-if="result !== ''"
    :result="result"
  />
  <div
    v-else
    class="mx-4 xl:mx-0"
  >
    <div>
      <h2 class="text-center text-primary xl:text-left">{{ t('guest.title') }} | {{ localeDate }}</h2>
      <p class="whitespace-pre-line text-[18px] leading-[24px] text-primary-1">
        {{ t('guest.description') }}
      </p>
    </div>
    <GuestDay
      v-if="invitation"
      :class="{ 'ring-2 ring-red': mealsMissing }"
      :guestData="invitation"
      :chosen-meals="form.chosenMeals"
    />
    <GuestForm
      v-model:firstName="form.firstName"
      v-model:lastName="form.lastName"
      v-model:company="form.company"
      :firstNameMissing="firstNameMissing"
      :lastNameMissing="lastNameMissing"
      :companyMissing="companyMissing"
      :filled="filled"
      @submitForm="submitForm"
    />
  </div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { useRoute } from 'vue-router';
import { useInvitationData } from '@/api/getInvitationData';
import useEventsBus from '@/tools/eventBus';
import { ref, computed, reactive } from 'vue';
import { useJoinMealGuest } from '@/api/postJoinMealGuest';
import { useI18n } from 'vue-i18n';
import GuestCompletion from '@/components/guest/GuestCompletion.vue';
import GuestForm from '@/components/guest/GuestForm.vue';
import GuestDay from '@/components/guest/GuestDay.vue';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

interface IForm {
  firstName: string;
  lastName: string;
  company: string;
  chosenSlot: number;
  chosenMeals: string[];
  combiDishes: string[];
}

const progress = useProgress().start();
const route = useRoute();
const { invitation, error } = await useInvitationData(route.params.hash as string);
const result = ref(error.value === true ? 'data_error' : '');
const { receive } = useEventsBus();
const { t, locale } = useI18n();
const { sendFlashMessage } = useFlashMessage();

const form = reactive<IForm>({
  firstName: '',
  lastName: '',
  company: '',
  chosenSlot: 0,
  chosenMeals: [],
  combiDishes: []
});

const filled = computed(
  () => form.firstName !== '' && form.lastName !== '' && form.company !== '' && form.chosenMeals.length !== 0
);
const firstNameMissing = ref(false);
const lastNameMissing = ref(false);
const companyMissing = ref(false);
const mealsMissing = ref(false);

const reachedLimit = computed(() => form.chosenMeals.length >= 2);

receive('guestChosenMeals', (mealId: string) => {
  const index = form.chosenMeals.indexOf(mealId);
  if (index !== -1) {
    form.chosenMeals.splice(index, 1);
  } else if (reachedLimit.value === true) {
    sendFlashMessage({
      type: FlashMessageType.INFO,
      message: 'meal.maxReached',
      hasLifetime: true
    });
  } else {
    form.chosenMeals.push(mealId);
  }
});

receive('guestChosenSlot', (slot: number) => {
  form.chosenSlot = slot;
});

receive('guestChosenCombi', (dishes: string[]) => {
  form.combiDishes = dishes;
});

const date = new Date(invitation.value?.date?.date ?? '');
const localeDate = computed(() =>
  date.toLocaleDateString(locale.value, { weekday: 'long', month: 'numeric', day: 'numeric' })
);

async function submitForm() {
  if (filled.value === true) {
    const { error } = await useJoinMealGuest(JSON.stringify(form));
    if (error.value === false) {
      result.value = 'resolve_success';
    } else {
      result.value = 'resolve_error';
    }
  } else {
    showFormErrors();
  }
}

function showFormErrors() {
  firstNameMissing.value = form.firstName === '';
  lastNameMissing.value = form.lastName === '';
  companyMissing.value = form.company === '';
  mealsMissing.value = form.chosenMeals.length === 0;
}

progress.finish();
</script>
