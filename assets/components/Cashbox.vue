<script setup>
import {ref, onMounted, onBeforeUnmount, nextTick} from "vue";
import Product from "./Product.vue";
import Purchase from "./Purchase.vue";


const quantity = ref(1);
const sellingList = ref([]);
const accountNames = ref([]);
const returnables = ref(0);


onMounted(() => {
    nextTick(() => {

        try {
            const cashbox = document.getElementById("cashbox");
            if (!cashbox) {
                console.error('Élément avec l\'ID "cashbox" introuvable');
                return;
            }
            returnables.value = JSON.parse(cashbox.dataset.returnables)
            sellingList.value = JSON.parse(cashbox.dataset.sellingList)
            accountNames.value = JSON.parse(cashbox.dataset.accountNames)

        } catch (error) {
            console.error("Erreur lors de l'initialisation :", error);
        }
    });
})

onBeforeUnmount(() => {

});


const increase = () => {
    quantity.value += 1
    console.log(quantity.value)
}


</script>

<template>

    <Purchase :selling-list="sellingList" :account-names="accountNames" :returnables="returnables"/>

</template>

<style scoped>

</style>
