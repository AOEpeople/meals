<template>
  <header class="bg-white drop-shadow-[0_15px_25px_rgba(0,0,0,0.10)]">
    <nav class="grid grid-cols-3 items-center py-5 mx-auto max-w-screen-aoe xl:grid-cols-10" aria-label="Top">
      <div class="justify-self-center xl:hidden" id="dropdown">
        <button type="button" class="inline-flex justify-center items-center p-2 text-gray-400 rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg id="open-menu" class="block w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg id="close-menu" class="hidden w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="justify-self-center inline-block xl:justify-self-start xl:col-span-2">
        <a :href="logoLink">
          <img class="w-auto h-10" src="../../../../public/images/meals-logo.svg" alt="AOE Logo" />
        </a>
      </div>
      <div class="hidden col-span-4 space-x-3 xl:inline-block">
        <a v-for="link in navigation"
           :key="link.name"
           :href="link.href"
           class="text-base font-semibold text-primary hover:text-highlight"
        >
          {{ link.name }}
        </a>
      </div>
      <div class=" justify-self-end inline-block col-span-4 space-x-4">
        <div class="hidden self-center space-x-2 text-right xl:inline-block">
          <img class="inline-block w-6" src="../../../../public/images/person.svg" alt="person" />
          <a class="text-base font-semibold text-black">
            {{ user.name }}
          </a>
        </div>
        <div class="hidden text-right xl:inline-block">
          <a class="text-base font-semibold text-black hover:text-highlight" :href="user.href">
            Balance:
            <a class="text-primary-2">
              € {{ user.balance }}
            </a>
          </a>
        </div>
        <div class="hidden xl:inline-block justify-self-end">
          <img class="inline-block w-6 cursor-pointer" src="../../../../public/images/logout.svg" alt="logout" />
        </div>
      </div>
    </nav>
    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="xl:hidden" hidden id="mobile-menu">
      <div class="pt-2 pb-3 space-y-1">
        <!-- Current: "bg-indigo-50 border-indigo-500 text-indigo-700", Default: "border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800" -->
        <a href="#" class="block py-2 pr-4 pl-3 text-base font-medium text-indigo-700 bg-indigo-50 border-l-4 border-indigo-500">Main</a>
        <a href="#" class="block py-2 pr-4 pl-3 text-base font-medium text-gray-600 border-l-4 border-transparent hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">Link1</a>
        <a href="#" class="block py-2 pr-4 pl-3 text-base font-medium text-gray-600 border-l-4 border-transparent hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">Link2</a>
        <a href="#" class="block py-2 pr-4 pl-3 text-base font-medium text-gray-600 border-l-4 border-transparent hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">Link3</a>
      </div>
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <img class="w-10 h-10" src="../../../../public/images/person.svg" alt="Person">
          </div>
          <div class="ml-3">
            <div class="text-base font-medium text-gray-800">surname.firstname</div>
          </div>
        </div>
        <div class="mt-3 space-y-1">
          <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">German version</a>
          <a href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">Sign out</a>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
const navigation = [
  { name: 'Menu',       href: '#', current: false },
  { name: 'Dishes',     href: '#', current: false },
  { name: 'Categories', href: '#', current: false },
  { name: 'Time Slots', href: '#', current: false },
  { name: 'Costs',      href: '#', current: false },
  { name: 'Finance',    href: '#', current: false },
];
const user = {
  name:     'firstname.surname',
  balance:  '1337.42',
  href:     '#',
  logout:   '#',
};
const logoLink = '#';
</script>