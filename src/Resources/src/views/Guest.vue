<template>
  <GuestCompletion v-if="result !== ''" :result="result" />
  <div v-else>
    <div class="mx-4">
      <h2>{{ t('guest.title') }}</h2>
      <p class="whitespace-pre-line">{{ t('guest.description') }}</p>
    </div>
    <GuestSelection
        :invitation="invitation"
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
import {useInvitationData} from '@/hooks/getInvitationData'
import useEventsBus from '@/hooks/eventBus'
import {ref, computed} from 'vue'
import {useJoinMealGuest} from '@/hooks/postJoinMealGuest'
import {useI18n} from 'vue-i18n'
import GuestCompletion from '@/components/guest/GuestCompletion.vue'
import GuestSelection from '@/components/guest/GuestSelection.vue'
import GuestForm from '@/components/guest/GuestForm.vue'

const progress = useProgress().start()
const route = useRoute()
const { invitation, error } = await useInvitationData(route.params.hash)
const result = ref(error.value === true ? 'data_error' : '')
const { receive } = useEventsBus()
const { t } = useI18n()

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

setTimeout(function () {
    progress.finish()
}, 500)
</script>
