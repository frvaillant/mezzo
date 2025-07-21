<script setup>
import {ref, watch} from "vue";

    const props = defineProps({
        product: {
            type: Object,
            required: true
        }
    })

    const total = ref( 0);
    const quantity = ref(props.product.quantity ?? 0)
    const hasConsigne = ref(false)
    const consigneButton = ref(null)

    const emit = defineEmits(['update-total'])

    watch(total, (newTotal) => {
        emit('update-total', {
            id: props.product.id,
            quantity: quantity,
            hasConsigne: hasConsigne,
            total: newTotal,
            totalProduct: quantity.value * props.product.unitPrice.toFixed(2)
        })
    })

    const handleClickAdd = (element => {
        element.classList.add('clicked')
        void element.offsetWidth;
        element.style.transition = '0.25s ease'
        const delay = 250
        setTimeout(() => {
            element.classList.remove('clicked')
        }, 100)

        setTimeout(() => {
            element.removeAttribute('style')
        }, delay * 2)
    })

    const increase = (e) => {
        handleClickAdd(e.currentTarget)
        quantity.value += 1
        calculate()
    }

    const decrease = () => {
        if(quantity.value === 0) {
            return
        }
        quantity.value -= 1
        calculate()
    }

    const reset = () => {
        quantity.value = 0
        hasConsigne.value = false
        consigneButton?.value?.classList.remove('active')
        calculate()
    }

    defineExpose({ reset })

    const calculate = () => {
        total.value = quantity.value * props.product.unitPrice.toFixed(2)
        const consigneTotalValue = (quantity.value * props.product.unitPrice.toFixed(2)) + quantity.value
        if(hasConsigne.value) {
            total.value = consigneTotalValue
        }
    }

    const toggleConsigne = (e) => {
        e.target.classList.toggle('active')
        hasConsigne.value = !hasConsigne.value
        calculate()
    }

</script>

<template>
    <div class="flex flex-col items-start justify-between p-3 bg-gray-100 rounded-lg w-full">

        <div class="text-lg font-medium flex justify-between items-center w-full">

            <div class="text-xl font-bold flex-1">
                {{ props.product.name }}
            </div>

            <div class="flex items-center gap-4">

                <button class="button flex justify-center items-center icon-square text-black" @click="increase">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>

                <div
                    class="button icon-square font-bold flex justify-center items-center"
                    :class="quantity === 0 ? 'text-gray-200 bg-white' : 'bg-teal-500 text-white text-xl'"
                >
                    {{ quantity }}
                </div>

                <button class="button flex justify-center items-center icon-square text-black text-lg" @click="decrease">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                    </svg>
                </button>
            </div>

        </div>

        <div class="flex items-center justify-between w-full mt-2">

            <div>
                <button
                    ref="consigneButton"
                    @click="toggleConsigne"
                    v-if="props.product.withConsigne"
                    class="button consigne flex items-center"
                >
                    Consigne
                </button>
            </div>

        </div>

    </div>

</template>

<style scoped>

</style>
