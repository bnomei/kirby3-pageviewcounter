<template>
  <k-text-field :value="$library.dayjs(timestamp).format(format)" :label="label" disabled />
</template>

<script setup>
defineOptions({ inheritAttrs: false });
defineProps({ timestamp: String, format: String, label: String });
</script>
