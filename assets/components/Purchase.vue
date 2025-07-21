<script setup >
    import Product from "./Product.vue";
    import {computed, nextTick, onMounted, ref} from "vue";
    import Notifier from "../services/Notifier";

    const props = defineProps({
        sellingList: {
            type: Object,
            required: true
        },
        accountNames: {
            type: Array,
            required: false
        }
    })

    const total       = ref( 0);
    const buyingList  = ref({})
    const productRefs = ref([])
    const canPurchase = ref(true)
    const accountRef  = ref(null)
    const localAccountNames = ref([...props.accountNames])

    onMounted(() => {
        nextTick(() => {
            updateAccountNames()
        });
    })

    const handleTotalUpdate = ({ id, total, quantity, hasConsigne, totalProduct }) => {
        buyingList.value[id] = {
            total: total,
            totalProduct: totalProduct,
            quantity: quantity,
            hasConsigne: hasConsigne
        }
    }

    const resetAll = async () => {
        await nextTick()
        productRefs.value.forEach(child => {
            child?.reset?.()
        })
    }

    const globalTotal = computed(() =>
        Object.values(buyingList.value).reduce((sum, val) => sum + val.total, 0)
    )

    const purchase = async (mode, account = null) => {
        console.log(mode, account)
        if(globalTotal.value === 0) {
            Notifier.error('Aucun produit saisi')
            return
        }

        if(mode === null && account && accountRef?.value?.value === '') {
            Notifier.error('Saisir un nom ou choisir un mode de paiement')
            return;
        }

        if(
            canPurchase.value
            && (mode !== null || account)
        ) {

            const route = account ? `/purchase/${accountRef?.value?.value}` : '/purchase';

            canPurchase.value = false

            const purchaseEvent = new CustomEvent('Purchase')

            const data = {
                purchase: buyingList.value,
                mode: mode
            }

            const response = await fetch(route, {
                method: 'POST',
                body: JSON.stringify(data)
            })

            if (response.status === 200) {
                await resetAll()
                Notifier.success('Achat enregistré')
                if (accountRef && accountRef.value) {
                    accountRef.value.value = ''
                }
                await updateAccountNames()
                document.dispatchEvent(purchaseEvent)
                canPurchase.value = true
                return
            }
            canPurchase.value = true
            Notifier.error('Une erreur s\'est produite')

        }

    }


    const setAccountName = (name) => {
        accountRef.value.value = name
    }

    const updateAccountNames = async () => {
        const response = await fetch('/account-names')
        localAccountNames.value = await response.json()
    }

</script>

<template>
    <div class="">

        <div>

            <div class="py-3 px-4 rounded-lg bg-teal-500 flex items-center justify-between text-white" @click="increase">

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

            <div class="selling-list mt-4 flex flex-col gap-4">
                <Product
                    v-for="product in sellingList"
                    :key="product.id"
                    :product="product"
                    @update-total="handleTotalUpdate"
                    ref="productRefs"
                />
            </div>

            <div class="flex justify-between">
                <button
                    @click="purchase('espèces')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                    Espèces
                </button>
                <button
                    @click="purchase('chèque')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Chèque
                </button>
                <button
                    @click="purchase('CB')"
                    class="button text-lg bg-teal-500 text-white shadow-md mt-3"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    CB
                </button>

            </div>

        </div>

    </div>

    <div class="mt-1 flex items-center flex-col w-full justify-start">
        <div class="inline-flex items-center space-x-2 bg-gray-800 text-white px-4 py-4 rounded-md shadow-md w-full">
            <input
                ref="accountRef"
                type="text"
                placeholder="Nom"
                class="flex-1 bg-gray-100 text-gray-800 px-2 py-3 rounded text-sm w-24  focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
                class="text-lg font-bold"
                @click="purchase(null, true)"
            >
                CPTE
            </button>
        </div>

        <div class="flex items-center flex-wrap justify-start w-full mt-2 gap-2">
            <button
                v-for="(name, index) in localAccountNames"
                :key="index"
                class="inline-block bg-gray-200 text-gray-800 px-2 py-1 rounded mr-1 mb-1"
                @click="setAccountName(name)"
            >
              {{ name }}
            </button>
        </div>
    </div>

    <div class="mt-3 flex items-center w-full justify-end">

        <button class="button text-lg bg-gray-800 text-white shadow-md">
            Retour consigne
        </button>
    </div>

</template>

<style scoped>

</style>
