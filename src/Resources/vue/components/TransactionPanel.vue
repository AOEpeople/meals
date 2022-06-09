<template>
  <div class="w-[500px] h-[500px]">
    <input type="text" placeholder="0,00 €" v-model="input" v-on:input="parseInput" />
    {{ amount }}
    <PayPal />
  </div>
</template>

<script>
import PayPal from '@/components/PayPal.vue';
export default {
  components: {
    PayPal
  },
  data() {
    return {
      input: "",
      amount: 0,
    };
  },
  methods: {
    parseInput() {
      this.amount = parseFloat(this.input.replace(/,/g, '.'));
    },
  },
};
</script>
