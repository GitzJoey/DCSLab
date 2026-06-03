import { cva, type VariantProps } from 'class-variance-authority'

export const boxVariants = cva(
  'shadow-md/5 bg-background bg-gradient-to-b from-transparent to-foreground/[.03] dark:to-foreground/5 border border-foreground/10 rounded-xl p-5 outline-none relative before:absolute after:absolute',
  {
    variants: {
      raised: {
        single: [
          'mb-3',
          'before:inset-x-2.5 before:h-[10px] before:bg-background before:-bottom-[11px] before:rounded-b-xl before:border-x before:border-b before:border-foreground/10 before:z-[-1] before:shadow-md/5 before:opacity-60 dark:before:opacity-100',
        ],
        double: [
          'mb-5',
          'before:inset-x-2.5 before:h-[10px] before:bg-background before:-bottom-[11px] before:rounded-b-xl before:border-x before:border-b before:border-foreground/10 before:z-[-1] before:shadow-md/5 before:opacity-60 dark:before:opacity-100',
          'after:inset-x-5 after:h-[10px] after:bg-background after:-bottom-[21px] after:rounded-b-xl after:border-x after:border-b after:border-foreground/10 after:z-[-2] after:shadow-md/5 after:opacity-50 dark:after:opacity-90',
        ],
      },
    },
  },
)

export type BoxVariants = {
  raised?: VariantProps<typeof boxVariants>['raised']
}
