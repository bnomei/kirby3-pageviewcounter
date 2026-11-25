<template>
  <k-text-field :value="String(count)" :label="label" disabled />
</template>

<script setup>
defineOptions({ inheritAttrs: false });
defineProps({ count: Number, label: String });
</script>
