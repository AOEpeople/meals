<template>
  <GuestCompletion
    v-if="result !== ''"
    :result="result"
  />
  <div v-else>
    <div>
      <h2 class="text-primary">{{ t('guest.title') }} | {{ localeDate }}</h2>
      <p class="whitespace-pre-line text-[18px] leading-[24px] text-primary-1">
        {{ t('guest.description') }}
      </p>
    </div>
    <Day
      :guestData="invitation"
    />
    <GuestForm
      v-model:firstName="form.firstName"
      v-model:lastName="form.lastName"
      v-model:company="form.company"
      :filled="filled"
      @submitForm="submitForm"
    />
  </div>
</template>

<script setup>
import {useProgress} from '@marcoschulte/vue3-progress'
import {useRoute} from 'vue-router'
import {useInvitationData} from '@/api/getInvitationData'
import useEventsBus from 'tools/eventBus'
import {ref, computed} from 'vue'
import {useJoinMealGuest} from '@/api/postJoinMealGuest'
import {useI18n} from 'vue-i18n'
import GuestCompletion from '@/components/guest/GuestCompletion.vue'
import GuestForm from '@/components/guest/GuestForm.vue'
import Day from '@/components/dashboard/Day.vue'

const progress = useProgress().start()
const route = useRoute()
const { invitation, error } = await useInvitationData(route.params.hash)
const result = ref(error.value === true ? 'data_error' : '')
const { receive } = useEventsBus()
const { t, locale } = useI18n()
const form = ref({
  firstName: '',
  lastName: '',
  company: '',
  chosenSlot: 0,
  chosenMeals: [],
  combiDishes: []
})
const filled = computed(() =>
    form.value.firstName !== ''
    && form.value.lastName !== ''
    && form.value.company !== ''
    && form.value.chosenMeals.length !== 0
);

receive('guestChosenMeals', (slug) => {
  const index = form.value.chosenMeals.indexOf(slug)
  if (index !== -1) {
    form.value.chosenMeals.splice(index, 1)
  } else {
    form.value.chosenMeals.push(slug)
  }
})

receive('guestChosenSlot', (slot) => {
  form.value.chosenSlot = slot
})

receive('guestChosenCombi', (dishes) => {
  form.value.combiDishes = dishes
})

const date = new Date(invitation.value.date.date)
const localeDate = computed(() => date.toLocaleDateString(locale.value, { weekday: 'long', month: 'numeric', day: 'numeric' }))

async function submitForm() {
  if (filled.value) {
    const { error } = await useJoinMealGuest(JSON.stringify(form.value))
    if (error.value === false) {
      result.value = "resolve_success"
    } else {
      result.value = "resolve_error"
    }
  }
}

progress.finish()
</script>
