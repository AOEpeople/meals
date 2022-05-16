<template>
  <header class="bg-white drop-shadow-[0_15px_25px_rgba(0,0,0,0.10)]">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Top">
      <div class="w-full py-6 flex items-center justify-between">
        <div class="flex items-center">
          <a :href="logoLink">
            <img class="h-10 w-auto" src="../../../../public/images/aoe-logo.svg" alt="AOE Logo" />
          </a>
          <div class="hidden ml-32 space-x-8 lg:block">
            <a v-for="link in navigation"
               :key="link.name"
               :href="link.href"
               class="text-base font-semibold text-[#173D7A] hover:text-[#FF890E]"
            >
              {{ link.name }}
            </a>
          </div>
        </div>
        <div class="contents space-x-4">
          <img class="-mr-16 max-w-[1.5%]" src="../../../../public/images/person.svg" alt="person" />
          <a class="mr-20 text-base font-medium text-black">
            {{ user.name }}
          </a>
          <a class="ml-10 text-base font-medium text-black hover:text-[#FF890E]" :href="user.href">
            Balance:
            <a class="text-[#029DF7]">
              € {{ user.balance }}
            </a>
          </a>
          <img class="max-w-[1.5%] cursor-pointer" src="../../../../public/images/logout.svg" alt="logout" />
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
const navigation = [
  { name: 'Menu',       href: '#' },
  { name: 'Dishes',     href: '#' },
  { name: 'Categories', href: '#' },
  { name: 'Time Slots', href: '#' },
  { name: 'Costs',      href: '#' },
  { name: 'Finance',    href: '#' },
];
const user = {
  name:     'firstname.surname',
  balance:  '1337.42',
  href:     '#',
};
const logoLink = '#';
</script>