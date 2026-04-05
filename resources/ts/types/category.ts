export interface Category {
  id: number
  name: string
  slug: string
  api_keyword: string
  description: string | null
  icon: string | null
  sort_order: number
  status: '0' | '1' | '2'
  articles_count: number
  has_related_data: boolean
  created_at: string | null
}

export interface CategoryStatistic {
  total: number
  active: number
  inactive: number
  archive: number
}
