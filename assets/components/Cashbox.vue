<script setup>
import {ref, onMounted, onBeforeUnmount, nextTick} from "vue";
import Product from "./Product.vue";
import Purchase from "./Purchase.vue";


const quantity = ref( 1);
const sellingList = ref( []);


onMounted(() => {
    nextTick(() => {

        try {
            const cashbox = document.getElementById("cashbox");
            if (!cashbox) {
                console.error('Élément avec l\'ID "cashbox" introuvable');
                return;
            }

           sellingList.value = JSON.parse(cashbox.dataset.sellingList)

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

    <Purchase :selling-list="sellingList" />

</template>

<style scoped>

</style>
