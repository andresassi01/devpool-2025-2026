import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

interface ProductData {
  image: string;
  inStock: boolean;
}

interface ProductsData {
  [key: string]: ProductData;
}

export const useProductStore = defineStore('product', () => {
  // Estado
  const selectedColor = ref<string>('azul')

  // Dados dos produtos
  const productData: ProductsData = {
    azul: {
      image: 'https://www.comerciomix.com.br/media/catalog/product/cache/39699d3c43c18428eb057c8e614b0843/c/a/camiseta-azul-royal-para-sublima_o-tradicional_1.jpg',
      inStock: true,
    },
    preto: {
      image: 'https://img.irroba.com.br/fit-in/600x600/filters:fill(fff):quality(80)/estampar/catalog/camisetas/preto-verso.jpg',
      inStock: false,
    },
  }

  // Getters (computed)
  const currentImage = computed(() => productData[selectedColor.value].image)
  const isButtonDisabled = computed(() => !productData[selectedColor.value].inStock)

  // Actions
  function setSelectedColor(color: string) {
    selectedColor.value = color
  }

  return {
    selectedColor,
    currentImage,
    isButtonDisabled,
    setSelectedColor,
  }
})

