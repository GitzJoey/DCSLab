<script lang="ts" setup>
import { Chart, getColor } from "@/components/ui/chart";
</script>

<template>
  <div class="flex flex-col gap-20">
    <div class="grid grid-cols-2">
      <div
        class="justify-center items-center flex gap-2 border-b border-e border-foreground/10 p-5 flex-wrap"
      >
        <Chart
          class="max-w-100"
          :config="{
            type: 'bar',
            data: {
              labels: [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec',
              ],
              datasets: [
                {
                  label: 'Html Template',
                  maxBarThickness: 12,
                  data: [
                    60, 150, 30, 200, 180, 50, 180, 120, 230, 180, 250, 270,
                  ],
                  backgroundColor: () => getColor('--color-foreground', 0.3),
                  borderColor: () => getColor('--color-foreground'),
                  borderWidth: 1,
                },
              ],
            },
            options: {
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false,
                },
              },
              scales: {
                x: {
                  display: false,
                },
                y: {
                  display: false,
                },
              },
            },
          }"
        />
      </div>
    </div>
  </div>
</template>
