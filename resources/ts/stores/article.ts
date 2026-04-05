import { defineStore } from 'pinia'
import { $api } from '@/utils/api'
import type { Article, ArticleStatistic } from '@/types/article'

export const useArticleStore = defineStore('article', () => {
  const articles  = ref<Article[]>([])
  const total     = ref(0)
  const statistic = ref<ArticleStatistic>({ total: 0, today: 0, saved: 0, summarized: 0 })
  const loading   = ref(false)

  async function getArticleData(params: Record<string, any> = {}) {
    loading.value = true
    try {
      const res = await $api('getArticleData', { query: params })
      articles.value = res.data
      total.value    = res.total
    }
    finally { loading.value = false }
  }

  async function articleSave(articleId: number) {
    await $api('articleSave', { method: 'POST', body: { article_id: articleId } })
  }

  async function articleUnsave(articleId: number) {
    await $api('articleUnsave', { method: 'POST', body: { article_id: articleId } })
  }

  async function articleStatistic() {
    const res = await $api('articleStatistic')
    statistic.value = res.data
  }

  return { articles, total, statistic, loading, getArticleData, articleSave, articleUnsave, articleStatistic }
})
