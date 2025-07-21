<script setup >
    import Product from "./Product.vue";
    import {computed, nextTick, ref} from "vue";
    import Notifier from "../services/Notifier";

    const props = defineProps({
        sellingList: {
            type: Object,
            required: true
        }
    })

    const notifier = new Notifier()

    const total = ref( 0);
    const buyingList = ref({})
    const productRefs = ref([])

    const handleTotalUpdate = ({ id, total, quantity, hasConsigne, totalProduct }) => {
        buyingList.value[id] = {
            total: total,
            totalProduct: totalProduct,
            quantity: quantity,
            hasConsigne: hasConsigne
        }
    }

    const resetAll = async () => {
        // On attend que le DOM soit mis à jour
        await nextTick()
        productRefs.value.forEach(child => {
            child?.reset?.()
        })
    }

    const globalTotal = computed(() =>
        Object.values(buyingList.value).reduce((sum, val) => sum + val.total, 0)
    )

    const purchase = async (mode) => {

        const purchaseEvent = new CustomEvent('Purchase')
        const data = {
            purchase: buyingList.value,
            mode: mode
        }
        const response = await fetch('/purchase', {
            method: 'POST',
            body: JSON.stringify(data)
        })

        if(response.status === 200) {
            await resetAll()
            notifier.success('Achat enregistré')
            document.dispatchEvent(purchaseEvent)
            return
        }
        notifier.error('Une erreur s\'est produite')

    }

</script>

<template>
    <div class="rounded-lg bg-gray-100">

        <div>

            <div class="py-3 px-4 rounded-t-lg bg-teal-500 flex items-center justify-between text-white" @click="increase">

                <button
                    @click="resetAll"
                    class="button flex justify-center items-center icon-square text-black"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </button>


                <span class="text-xl font-bold text-white">
                    {{ globalTotal }} €
                </span>
            </div>

        </div>

        <div class="pb-3">

            <div class="selling-list mt-4 flex flex-wrap gap-[4%]">
                <Product
                    v-for="product in sellingList"
                    :key="product.id"
                    :product="product"
                    @update-total="handleTotalUpdate"
                    ref="productRefs"
                />
            </div>

            <div class="px-2 flex justify-between">
                <button
                    @click="purchase('espèces')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    Espèces
                </button>
                <button
                    @click="purchase('chèque')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    Chèque
                </button>
                <button
                    @click="purchase('CB')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    CB
                </button>
            </div>

        </div>

    </div>

</template>

<style scoped>

</style>
