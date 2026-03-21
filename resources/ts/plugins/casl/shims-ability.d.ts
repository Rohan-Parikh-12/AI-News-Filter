import type { Actions, Subjects } from './ability'
import type { MongoAbility } from '@casl/ability'

declare module '@casl/vue' {
  interface ProvidedAbility extends MongoAbility<[Actions, Subjects]> {}
}
