<script lang="ts" setup>
import { computed } from 'vue'
import errorIllustrationUrl from '@/assets/images/error-illustration.svg'
import { Button } from '@/components/ui/button'

const errorCode = computed(() => history.state?.code || 500);
const errorMessage = computed(() => history.state?.message || "An unexpected error occurred.");
</script>

<template>
    <div :class="[
        'relative py-2',
        'before:bg-primary dark:before:bg-foreground/1 before:fixed before:inset-0 before:bg-noise',
        'after:bg-accent after:bg-contain after:fixed after:inset-0 after:blur-xl dark:after:opacity-20',
    ]">
        <div class="container relative z-10 mx-auto">
            <div class="flex h-screen flex-col items-center justify-center text-center lg:flex-row lg:text-left">
                <div class="lg:mr-20">
                    <img class="h-48 w-112.5 lg:h-auto" :src="errorIllustrationUrl" alt="DCSLab" />
                </div>
                <div class="mt-10 text-white lg:mt-0">
                    <div class="text-9xl [text-shadow:7px_7px_--alpha(var(--color-white)/20%)]">{{ errorCode }}</div>
                    <div class="mt-8 text-xl font-medium lg:text-2xl">Oops. Something went wrong.</div>
                    <div class="mt-3 text-base opacity-70">
                        {{ errorMessage }}
                    </div>
                    <Button class="box mt-10 border border-white bg-transparent px-7 py-6 text-white" variant="ghost">
                        Back to Home
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>