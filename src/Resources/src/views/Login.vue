<template>
  <form
    @submit.prevent="login"
  >
    <h3 class="w-full text-center">
      {{ t('login') }}
    </h3>
    <InputLabel
      v-model="username"
      :label-text="t('username')"
      :required="true"
    />
    <InputLabel
      v-model="password"
      :type="password"
      :label-text="t('password')"
      :required="true"
    />
    <SubmitButton
      :btn-text="t('login')"
    />
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
  const { error } = await postLogin(username.value, password.value);
  if (error.value === false) {
    Promise.all([
      userDataStore.fillStore(),
      environmentStore.fillStore()
    ])
    .then(() => {
      router.push({ name: 'Dashboard' });
    });
  }

}
</script>