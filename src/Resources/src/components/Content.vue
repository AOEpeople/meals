<template>
  <main class="bg-[rgb(244,247,249)]">
    <div class="max-w-screen-aoe mx-auto my-10">
      <Suspense>
        <template #default>
          <router-view v-slot="{ Component }">
            <transition name="fade">
              <component :is="Component" />
            </transition>
          </router-view>
        </template>
        <template #fallback>
          <div> loading </div>
        </template>
      </Suspense>
    </div>
  </main>
</template>

<script setup>

</script>
