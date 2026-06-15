<script lang="ts" setup>
import { Form } from '@primevue/forms'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import Button from 'primevue/button'
</script>

<template>
  <Form v-slot="$form" class="flex flex-col gap-4 w-full sm:w-56">
    <div class="flex flex-col gap-1">
      <InputText name="username" type="text" placeholder="Username" fluid />
      <Message v-if="$form.username?.invalid" severity="error" size="small" variant="simple">{{
        $form.username.error?.message
      }}</Message>
    </div>
    <Button type="submit" label="Submit" />
  </Form>
</template>
