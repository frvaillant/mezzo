<script setup>

/**
 * Composant principal de Mezzo
 */

import {ref, onMounted, onBeforeUnmount, nextTick} from "vue";
import Purchase from "./Purchase.vue";

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


</script>

<template>

    <Purchase :selling-list="sellingList" :account-names="accountNames" :returnables="returnables"/>

</template>

<style scoped>

</style>
