<script setup >
    import Product from "./Product.vue"
    import {computed, nextTick, onMounted, ref} from "vue"
    import Notifier from "../services/Notifier"

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

    const total       = ref( 0)
    const buyingList  = ref({})
    const productRefs = ref([])
    const canPurchase = ref(true)
    const canReturnReturnable = ref(true)
    const mustShowAccount = ref(false)
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

        if(globalTotal.value === 0) {
            Notifier.error('Aucun produit saisi')
            return
        }

        if(mode === null && account && accountRef?.value?.value === '') {
            Notifier.error('Saisir un nom ou choisir un mode de paiement')
            return
        }

        if(
            canPurchase.value
            && (mode !== null || account)
        ) {

            const route = account ? `/purchase/${accountRef?.value?.value}` : '/purchase'

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
                showAccount(false)

                return
            }
            canPurchase.value = true
            showAccount(false)
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

    const returnableReturn = async (e) => {
        e.preventDefault()
        const target = e.currentTarget

        if(!canReturnReturnable.value) {
            return
        }

        target.classList.add('opacity-50')
        canReturnReturnable.value = false
        const response = await fetch('/return-glass', {
            method: 'POST'
        })

        if (response.status === 200) {
            const purchaseEvent = new CustomEvent('Purchase')
            document.dispatchEvent(purchaseEvent)
            Notifier.success('Retour de consigne enregistré')
        }
        setTimeout(() => {
            target.classList.remove('opacity-50')
            canReturnReturnable.value = true
        }, 750)

    }

    const showAccount = (val) => {
        if(globalTotal.value === 0) {
            Notifier.error('Aucun produit saisi')
            return
        }
        mustShowAccount.value = val
    }

</script>

<template>
    <div class="">

        <div>

            <div class="py-3 rounded-lg flex items-center justify-between text-white" @click="increase">

                <button @click="returnableReturn" class=" h-[50px] bg-black product-name flex items-center text-xl font-bold rounded-lg relative w-max pe-4">
                    <span class="product-picto return">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" viewBox="0 0 256 256"><path d="M206,26.69A8,8,0,0,0,200,24H56a8,8,0,0,0-7.94,9l23.15,193A16,16,0,0,0,87.1,240h81.8a16,16,0,0,0,15.89-14.09L207.94,33A8,8,0,0,0,206,26.69ZM191,40,188.1,64H67.9L65,40ZM168.9,224H87.1L69.82,80H186.18Z"></path></svg>
                    </span>
                    <span class="ms-3 w-max">Retour consigne</span>
                </button>


                <div class="flex items-center">

                    <span class="text-xl font-bold text-black bg-gray-200 px-4 h-[50px] rounded-s-md flex items-center justify-center">
                        {{ globalTotal }} €
                    </span>

                    <button
                        @click="resetAll"
                        class="text-lg px-4 h-[50px] rounded-e-md flex justify-center items-center icon-square text-black bg-gray-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#000000" viewBox="0 0 256 256" class="">
                            <path d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z">
                            </path>
                        </svg>
                    </button>

                </div>
            </div>

        </div>

        <div class="pb-3">

            <div class="selling-list mt-6 flex flex-col gap-4 border-t-1 border-t-amber-500 p-4 rounded-lg bg-gray-200">
                <Product
                    v-for="product in sellingList"
                    :key="product.id"
                    :product="product"
                    @update-total="handleTotalUpdate"
                    ref="productRefs"
                />
            </div>

            <div class="flex justify-between mt-4 flex-wrap gap-4">
                <button
                    @click="purchase('espèces')"
                    class="button text-lg bg-black text-white mt-3  flex flex-col justify-center items-center w-[25%]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256"><path d="M184,89.57V84c0-25.08-37.83-44-88-44S8,58.92,8,84v40c0,20.89,26.25,37.49,64,42.46V172c0,25.08,37.83,44,88,44s88-18.92,88-44V132C248,111.3,222.58,94.68,184,89.57ZM232,132c0,13.22-30.79,28-72,28-3.73,0-7.43-.13-11.08-.37C170.49,151.77,184,139,184,124V105.74C213.87,110.19,232,122.27,232,132ZM72,150.25V126.46A183.74,183.74,0,0,0,96,128a183.74,183.74,0,0,0,24-1.54v23.79A163,163,0,0,1,96,152,163,163,0,0,1,72,150.25Zm96-40.32V124c0,8.39-12.41,17.4-32,22.87V123.5C148.91,120.37,159.84,115.71,168,109.93ZM96,56c41.21,0,72,14.78,72,28s-30.79,28-72,28S24,97.22,24,84,54.79,56,96,56ZM24,124V109.93c8.16,5.78,19.09,10.44,32,13.57v23.37C36.41,141.4,24,132.39,24,124Zm64,48v-4.17c2.63.1,5.29.17,8,.17,3.88,0,7.67-.13,11.39-.35A121.92,121.92,0,0,0,120,171.41v23.46C100.41,189.4,88,180.39,88,172Zm48,26.25V174.4a179.48,179.48,0,0,0,24,1.6,183.74,183.74,0,0,0,24-1.54v23.79a165.45,165.45,0,0,1-48,0Zm64-3.38V171.5c12.91-3.13,23.84-7.79,32-13.57V172C232,180.39,219.59,189.4,200,194.87Z"></path></svg>
                    Espèces
                </button>
                <button
                    @click="purchase('chèque')"
                    class="button text-lg bg-black text-white mt-3 flex flex-col justify-center items-center w-[25%]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256"><path d="M232,168H63.86c2.66-5.24,5.33-10.63,8-16.11,15,1.65,32.58-8.78,52.66-31.14,5,13.46,14.45,30.93,30.58,31.25,9.06.18,18.11-5.2,27.42-16.37C189.31,143.75,203.3,152,232,152a8,8,0,0,0,0-16c-30.43,0-39.43-10.45-40-16.11a7.67,7.67,0,0,0-5.46-7.75,8.14,8.14,0,0,0-9.25,3.49c-12.07,18.54-19.38,20.43-21.92,20.37-8.26-.16-16.66-19.52-19.54-33.42a8,8,0,0,0-14.09-3.37C101.54,124.55,88,133.08,79.57,135.29,88.06,116.42,94.4,99.85,98.46,85.9c6.82-23.44,7.32-39.83,1.51-50.1-3-5.38-9.34-11.8-22.06-11.8C61.85,24,49.18,39.18,43.14,65.65c-3.59,15.71-4.18,33.21-1.62,48s7.87,25.55,15.59,31.94c-3.73,7.72-7.53,15.26-11.23,22.41H24a8,8,0,0,0,0,16H37.41c-11.32,21-20.12,35.64-20.26,35.88a8,8,0,1,0,13.71,8.24c.15-.26,11.27-18.79,24.7-44.12H232a8,8,0,0,0,0-16ZM58.74,69.21C62.72,51.74,70.43,40,77.91,40c5.33,0,7.1,1.86,8.13,3.67,3,5.33,6.52,24.19-21.66,86.39C56.12,118.78,53.31,93,58.74,69.21Z"></path></svg>
                    Chèque
                </button>
                <button
                    @click="purchase('CB')"
                    class="button text-lg bg-black text-white mt-3  flex flex-col justify-center items-center w-[25%]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256"><path d="M224,48H32A16,16,0,0,0,16,64V192a16,16,0,0,0,16,16H224a16,16,0,0,0,16-16V64A16,16,0,0,0,224,48Zm0,16V88H32V64Zm0,128H32V104H224v88Zm-16-24a8,8,0,0,1-8,8H168a8,8,0,0,1,0-16h32A8,8,0,0,1,208,168Zm-64,0a8,8,0,0,1-8,8H120a8,8,0,0,1,0-16h16A8,8,0,0,1,144,168Z"></path></svg>
                    CB
                </button>

                <button
                    @click="showAccount(true)"
                    class="button text-lg bg-zinc-600 text-white mt-3  flex flex-col justify-center items-center w-[25%]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256"><path d="M152,80a8,8,0,0,1,8-8h88a8,8,0,0,1,0,16H160A8,8,0,0,1,152,80Zm96,40H160a8,8,0,0,0,0,16h88a8,8,0,0,0,0-16Zm0,48H184a8,8,0,0,0,0,16h64a8,8,0,0,0,0-16Zm-96.25,22a8,8,0,0,1-5.76,9.74,7.55,7.55,0,0,1-2,.26,8,8,0,0,1-7.75-6c-6.16-23.94-30.34-42-56.25-42s-50.09,18.05-56.25,42a8,8,0,0,1-15.5-4c5.59-21.71,21.84-39.29,42.46-48a48,48,0,1,1,58.58,0C129.91,150.71,146.16,168.29,151.75,190ZM80,136a32,32,0,1,0-32-32A32,32,0,0,0,80,136Z"></path></svg>
                    CPTE
                </button>

            </div>

        </div>

    </div>

    <div v-if="mustShowAccount">
        <div class="mt-5 text-xl font-bold text-gray-900">
            Mise en attente
        </div>

        <div class="mt-5 flex items-center flex-col w-full justify-start">
        <div class="inline-flex items-center space-x-2 bg-gray-800 text-white px-4 py-4 rounded-md shadow-dark w-full">
            <input
                ref="accountRef"
                type="text"
                placeholder="Nom"
                class="flex-1 bg-gray-100 text-gray-800 px-2 py-3 rounded text-sm w-24  focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
                class="text-lg font-bold shadow-dark button icon-square circle bg-gray-600  flex flex-col justify-center items-center"
                @click="purchase(null, true)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>

            </button>
        </div>



        <div class="flex items-center flex-wrap justify-start w-full mt-2 gap-2">
            <button
                v-for="(name, index) in localAccountNames"
                :key="index"
                class="bg-gray-200 text-gray-800 px-2 py-1 rounded mr-1 mb-1 flex items-center gap-1 w-max"
                @click="setAccountName(name)"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>

                {{ name }}
            </button>
        </div>
    </div>
    </div>


</template>

<style scoped>

</style>
