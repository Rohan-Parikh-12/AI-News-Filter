export default [
  {
    title: 'Dashboard',
    icon: { icon: 'ri-home-smile-line' },
    to: 'root',
  },
  { heading: 'Content' },
  {
    title: 'Categories',
    icon: { icon: 'ri-price-tag-3-line' },
    to: 'categories-listing',
  },
  {
    title: 'Articles',
    icon: { icon: 'ri-newspaper-line' },
    to: 'articles-listing',
  },
  { heading: 'Digest' },
  {
    title: 'Digest',
    icon: { icon: 'ri-mail-send-line' },
    children: [
      { title: 'Today',    to: 'digest-listing'  },
      { title: 'Settings', to: 'digest-settings' },
    ],
  },
  { heading: 'Account' },
  {
    title: 'Preferences',
    icon: { icon: 'ri-settings-3-line' },
    to: 'preferences',
  },
]
