<template>
  <div class="field">
    <label class="label">{{ label }}</label>
    <div class="control" :class="{ 'has-icons-left': icone, 'has-icons-right': sucesso }">
      <slot></slot>
      
      <span v-if="icone" class="icon is-small is-left">
        <i :class="icone"></i>
      </span>

      <span v-if="sucesso" class="icon is-small is-right has-text-success">
        <i class="fas fa-check"></i>
      </span>
    </div>
    <slot name="ajuda"></slot>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string;
  icone?: string;
  sucesso?: boolean;
}>();
</script>