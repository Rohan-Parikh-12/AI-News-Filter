export interface Article {
  id: number
  title: string
  description: string | null
  url: string
  image_url: string | null
  source_name: string | null
  author: string | null
  published_at: string | null
  category: string | null
  category_id: number
  summary: string | null
  is_saved: boolean
  status: '0' | '1' | '2'
}

export interface ArticleStatistic {
  total: number
  today: number
  saved: number
  summarized: number
}
