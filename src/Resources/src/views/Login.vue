<template>
  <form
    class="mx-auto w-[300px] rounded-lg bg-[rgb(244,247,249)] p-4 shadow-lg ring-1 ring-black/5 sm:w-[450px] md:w-[550px]"
    @submit.prevent="login"
  >
    <h3 class="w-full text-center">
      {{ t('login') }}
    </h3>
    <InputLabel
      v-model="username"
      :label-text="t('username')"
    />
    <InputLabel
      v-model="password"
      :type="'password'"
      :label-text="t('password')"
    />
    <SubmitButton :btn-text="t('login')" />
  </form>
</template>

<script setup lang="ts">
import InputLabel from '@/components/misc/InputLabel.vue';
import SubmitButton from '@/components/misc/SubmitButton.vue';
import { ref } from 'vue';
import { postLogin } from '@/api/postLogin';
import { useRouter } from 'vue-router';
import { userDataStore } from '@/stores/userDataStore';
import { environmentStore } from '@/stores/environmentStore';
import { useI18n } from 'vue-i18n';

const username = ref('');
const password = ref('');

const { t } = useI18n();
const router = useRouter();

async function login() {
  if (username.value !== '' && password.value !== '') {
    const { error } = await postLogin(username.value, password.value);
    if (error.value === false) {
      Promise.all([userDataStore.fillStore(), environmentStore.fillStore()]).then(() => {
        router.push({ name: 'Dashboard' });
      });
    }
  }
}
</script>
