import { createMongoAbility } from '@casl/ability'

export type Actions = 'create' | 'read' | 'update' | 'delete' | 'manage'

export type Subjects =
  | 'Dashboard'
  | 'Categories'
  | 'Articles'
  | 'Digest'
  | 'Preferences'
  | 'Settings'
  | 'all'

export interface Rule { action: Actions; subject: Subjects | string }

export const ability = createMongoAbility<[Actions, Subjects]>()
