<template>
  <main>
    <div class="max-w-screen-aoe mx-auto my-10 z-0">
      <Suspense>
        <template #default>
          <router-view v-slot="{ Component }">
            <component :is="Component" />
          </router-view>
        </template>
        <template #fallback>
          <div> loading </div>
        </template>
      </Suspense>
    </div>
  </main>
</template>
