<script setup>
import {ref, onMounted, onBeforeUnmount, nextTick} from "vue";
import Product from "./Product.vue";
import Purchase from "./Purchase.vue";


const quantity = ref( 1);
const sellingList = ref( []);
const accountNames = ref( []);


onMounted(() => {
    nextTick(() => {

        try {
            const cashbox = document.getElementById("cashbox");
            if (!cashbox) {
                console.error('Élément avec l\'ID "cashbox" introuvable');
                return;
            }

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

    <Purchase :selling-list="sellingList" :account-names="accountNames" />

</template>

<style scoped>

</style>
