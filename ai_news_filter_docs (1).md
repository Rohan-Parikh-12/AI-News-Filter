# AI News Filter — Complete Project Documentation

> **Intelligent Daily News Digest Platform**
> Laravel 12 · Vue 3 (TypeScript) · Gemini AI · Firebase · iOS · Android
> Version 1.0.0 · March 2026

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Project Purpose & Learning Goals](#2-project-purpose--learning-goals)
3. [Technology Stack](#3-technology-stack)
4. [System Architecture](#4-system-architecture)
5. [Database Design](#5-database-design)
6. [Folder Structure](#6-folder-structure)
7. [Skills — AI Code Generation System](#7-skills--ai-code-generation-system)
8. [Core Features](#8-core-features)
9. [API Design & Naming Convention](#9-api-design--naming-convention)
10. [DFD — Data Flow Design](#10-dfd--data-flow-design)
11. [Scheduler & Automation](#11-scheduler--automation)
12. [Current Build Scope v1.0](#12-current-build-scope-v10)
13. [Future Scope](#13-future-scope)
14. [Mobile App Architecture](#14-mobile-app-architecture)
15. [Environment Configuration](#15-environment-configuration)
16. [Development Workflow](#16-development-workflow)
17. [Advanced Future Technologies](#17-advanced-future-technologies)
18. [Skills Built by This Project](#18-skills-built-by-this-project)
19. [Quick Reference Card](#19-quick-reference-card)

---

## 1. Executive Summary

**AI News Filter** solves one simple but painful problem — people are overwhelmed by irrelevant news. Instead of scrolling through dozens of articles daily, users receive a single AI-curated digest tailored to their exact interests, delivered at their chosen time, readable on any device.

### Core Value Proposition

```
User selects topics  →  System fetches news daily  →  AI summarises  →  Email + Push sent
```

| Without AI News Filter | With AI News Filter |
|---|---|
| Scroll 50+ articles daily | Read 5–10 summaries in 2 minutes |
| Algorithm-driven, noisy | User-selected topics only |
| No time saved | 20–30 minutes saved per day |
| Browser only | Web + iOS + Android |
| Ad-heavy free services | Freemium, no ads |

### What Makes This Production-Grade

- **Repository-Interface pattern** — DB queries isolated, controllers stay thin
- **Observer auto-triggers** — side effects fire automatically on model events
- **Status system** — `STATUS_ARCHIVE='0'`, `STATUS_ACTIVE='1'`, `STATUS_INACTIVE='2'`
- **Deletion protection** — `HasRelatedDataTrait` + `dependency_checks.php` prevent broken foreign keys
- **Skills system** — 17 AI code-generation templates for consistent scaffolding
- **Full TypeScript frontend** — `lang="ts"` everywhere, typed interfaces, typed store actions
- **Permission-gated UI** — every button/action checks `authStore.has_permission()`
- **MCP Resources** — models exposed to Claude AI for intelligent querying
- **Mobile-ready API** — same endpoints serve web, iOS, Android

---

## 2. Project Purpose & Learning Goals

### Business Goal → Technical Skill Mapping

| Business Goal | Technical Skill Learned |
|---|---|
| Deliver personalised news | Laravel Scheduler + Queue Jobs |
| AI article summarisation | Laravel AI SDK + Gemini API |
| User auth (web + mobile) | Laravel Sanctum Bearer Tokens |
| Subscription / plan gating | Razorpay / Stripe + permission system |
| Admin control panel | Filament + Repository pattern |
| Mobile app support | REST API + Firebase Push |
| Import / Export data | Maatwebsite Excel |
| Scalable code | Interface-Repository-Service + Skills system |
| Audit trail | Observer + Cache invalidation |
| Permission-gated access | PermissionSeeder + `has_permission()` |
| AI-assisted development | 17 custom Skills for code scaffolding |
| TypeScript frontend | Typed Pinia stores + Vue components |

### Who This Is Built For

- Developers learning Laravel from scratch who want a **real enterprise project**
- Backend developers expanding into **SaaS + HRMS-style architecture**
- Teams wanting a **reference codebase** for Vue 3 + TypeScript + Laravel
- Anyone building the foundation for a future **iOS / Android mobile app**

---

## 3. Technology Stack

### Backend — Laravel 12

| Package / Tool | Purpose | Cost |
|---|---|---|
| Laravel 12 | Core framework | Open source |
| Laravel Sanctum | Mobile + web API token auth | Included |
| Laravel Horizon | Redis queue dashboard | Included |
| Laravel Telescope | Debug & monitoring | Included |
| Laravel AI SDK | Gemini / OpenAI calls | Included |
| Maatwebsite Excel | Import / Export XLSX | Open source |
| Kreait Firebase | Push notifications (FCM) | Open source |
| Filament v3 | Admin panel | Open source |
| Spatie Activity Log | Audit trail / history | Open source |
| SweetAlert2 (PHP side) | Triggered via frontend | Open source |

### Frontend — Vue 3 + TypeScript + Materio Template

| Tool | Role |
|---|---|
| Vue 3 + Vite + **TypeScript** | Frontend framework — all files use `lang="ts"` |
| Pinia | Global state — composition API style with `apiCall` wrapper |
| Vue Router | SPA routing with `definePage()` + permission meta |
| Vuetify 3 | Material Design UI components |
| **ApiService** (`@/@core/services/ApiService`) | HTTP client — wraps axios, handles headers |
| **SweetAlert2** | All destructive confirmations (delete, status change, archive) |
| **PerfectScrollbar** | Drawer scroll wrapper |
| **vue3-perfect-scrollbar** | PerfectScrollbar Vue plugin |
| **paginationMeta** | Utility for "Showing X–Y of Z" text |
| **Remixicon (`ri-*`)** | Icon library — all icons use `ri-` prefix |
| Materio Template | Professional dashboard shell |

### Infrastructure — 100% Free Tier

| Service | Used For | Free Allowance |
|---|---|---|
| Railway / Render | Laravel app hosting | Free container |
| Neon | PostgreSQL database | 512 MB storage |
| Upstash Redis | Queue + cache | 10,000 commands/day |
| Resend | Transactional email | 3,000 emails/month |
| Gemini 2.0 Flash | AI summarisation | Generous free quota |
| NewsAPI | Article fetching | Free dev plan |
| GNews | Article fetching (fallback) | 100 requests/day |
| Firebase FCM | Push notifications | Free forever |

---

## 4. System Architecture

### High-Level Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTS                                  │
│                                                                 │
│   Vue 3 + TS (Web)     Flutter (iOS)       Flutter (Android)    │
│   localhost:5173       App Store           Play Store           │
└───────────────┬─────────────────────────────────────────────────┘
                │  HTTP  /api/*   Bearer Token
                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL 12 API SERVER                        │
│                     localhost:8000                              │
│                                                                 │
│  Route → FormRequest → Controller → Repository → Resource      │
│                              ↓                                  │
│              Observer     Service      Job                      │
│              (cache)    (AI/Email)  (Queue)                     │
└──────┬─────────────┬───────────────────────┬────────────────────┘
       │             │                       │
       ▼             ▼                       ▼
  PostgreSQL      Redis Queue           External APIs
  (Neon)          (Upstash)         NewsAPI · Gemini · Resend
                                        Firebase FCM
```

> **Key rule:** Laravel never renders HTML for the main application. Pure JSON API only. Vue handles all UI rendering. Mobile apps consume the exact same API.

### Backend Layer Responsibilities

| Layer | File Location | Does Exactly One Thing |
|---|---|---|
| Route | `routes/api.php` | Maps URL + method to controller — explicit names only |
| FormRequest | `Http/Requests/` | `baseRules()`, `createRules()`, `updateRules()` via `BaseFormRequest` |
| Controller | `Http/Controllers/Api/` | Extends `ApiBaseController`, calls repo, `authorizeUser()` |
| Interface | `Interfaces/` | Contract — defines all repo method signatures |
| Repository | `Repositories/` | All DB queries, `executeQuery()` wrapper, `DataTableConfiguration` |
| Observer | `Observers/` | `clearCache()` on created/updated/deleted/restored |
| API Resource | `Http/Resources/` | Shapes every JSON response |
| Service | `Services/` | Complex external calls (AI, email, push) |
| Job | `Jobs/` | Background queue processing |
| Export | `Exports/` | Excel/CSV file generation (Maatwebsite) |
| Import | `Imports/` | Excel/CSV file processing |
| MCP Resource | `Mcp/Resources/` | Exposes model data to Claude AI |

### Request Flow — Full Chain

```
Vue Component (user clicks Save)
    ↓  calls store action
Pinia Store (TypeScript)
    ↓  apiCall('post', '/api/manageCategory', data)
ApiService  ←  setHeader() injects token automatically
    ↓  HTTP POST /api/manageCategory
Route (api.php)  ←  auth:sanctum middleware
    ↓
FormRequest  →  createRules() or updateRules() validates
    ↓
Controller  →  authorizeUser($user, 'categories-create')
    ↓
Repository  →  executeQuery() wraps the Eloquent call
    ↓
Observer    →  clearCache() fires automatically
    ↓
Swal.fire() confirmation (frontend, before POST)
```

---

## 5. Database Design

### All Tables

| Table | Purpose | Key Fields |
|---|---|---|
| `users` | Core auth + plan | `plan (free\|pro)`, `timezone`, `soft_delete` |
| `digest_settings` | Per-user email prefs (1:1) | `send_time`, `frequency`, `max_articles` |
| `categories` | News topics — seeded | `slug`, `api_keyword`, `status (0\|1\|2)`, `sort_order` |
| `user_categories` | User interests pivot | `unique(user_id, category_id)` |
| `news_articles` | Fetched raw articles | `external_id (unique)`, `source_api`, `status` |
| `news_summaries` | AI summary per article (1:1) | `ai_model`, `prompt_tokens`, `relevance_score` |
| `saved_articles` | User bookmarks pivot | `unique(user_id, article_id)` |
| `digest_logs` | Email history | `status (sent\|failed\|skipped)`, `article_ids (JSON)` |
| `device_tokens` | FCM/APNs push tokens | `platform (ios\|android)`, `is_active` |
| `personal_access_tokens` | Sanctum tokens | `expires_at`, `abilities` |
| `jobs` / `failed_jobs` | Laravel queue | Auto-created by artisan |

### Status Constants — Used on Every Model

```php
// Every model uses these 3 status constants
const STATUS_ARCHIVE  = '0';   // Not in use, can be deleted if no relations
const STATUS_ACTIVE   = '1';   // Visible and selectable
const STATUS_INACTIVE = '2';   // Disabled — exists but hidden from users

// Status flow:
// Active  → can be Disabled or Archived
// Disabled → can be Activated or Archived
// Archive  → can be Activated (prevents deletion if has_related_data)
```

### Model Requirements (from check-model skill)

Every model **must** have:

```php
class Category extends Model
{
    use SoftDeletes;           // ← required
    use HasRelatedDataTrait;   // ← required — deletion protection
    use CommonTrait;           // ← audit fields (created_by, updated_by, deleted_by)

    const STATUS_ARCHIVE  = '0';
    const STATUS_ACTIVE   = '1';
    const STATUS_INACTIVE = '2';

    protected $fillable = [...];
    protected $casts    = [...];
    protected $table    = 'categories';  // explicit

    public function scopeActive($q) { return $q->where('status', self::STATUS_ACTIVE); }

    public function getRelationshipChecks(): array
    {
        return [
            'articles' => 'News Articles',  // blocks delete if articles exist
        ];
    }
}
```

### Migration Requirements (from generate-module skill)

Every migration **must** include audit fields:

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    // ... your columns ...
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->unsignedBigInteger('deleted_by')->nullable();
    $table->softDeletes();
    $table->timestamps();
});
```

---

## 6. Folder Structure

### Laravel Backend — `app/`

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/                        ← all API controllers
│   │       ├── AuthController.php
│   │       ├── CategoryController.php  ← extends ApiBaseController
│   │       ├── ArticleController.php
│   │       ├── DigestController.php
│   │       └── UserPreferenceController.php
│   ├── Requests/                       ← all extend BaseFormRequest
│   │   ├── BaseFormRequest.php         ← base class with createRules/updateRules
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterRequest.php
│   │   └── Category/
│   │       └── CategoryRequest.php     ← baseRules + createRules + updateRules
│   └── Resources/                      ← shapes all JSON
│       ├── CategoryResource.php
│       ├── ArticleResource.php
│       └── DigestLogResource.php
│
├── Interfaces/                         ← contracts only
│   ├── CategoryRepositoryInterface.php
│   ├── ArticleRepositoryInterface.php
│   └── DigestRepositoryInterface.php
│
├── Repositories/                       ← all DB queries live here
│   ├── CategoryRepository.php          ← uses DataTableConfiguration + executeQuery()
│   ├── ArticleRepository.php
│   └── DigestRepository.php
│
├── Services/                           ← external calls
│   ├── NewsService.php
│   ├── AISummaryService.php
│   ├── DigestService.php
│   └── PushNotificationService.php
│
├── Observers/                          ← clearCache() on every event
│   ├── CategoryObserver.php
│   ├── ArticleObserver.php
│   └── UserObserver.php               ← auto-creates digest_settings
│
├── Jobs/
│   ├── FetchNewsJob.php
│   ├── SummarizeArticleJob.php
│   └── SendDigestJob.php
│
├── Console/Commands/
│   ├── FetchNewsCommand.php
│   ├── SummarizeArticlesCommand.php
│   ├── SendDailyDigestCommand.php
│   └── CleanupOldArticlesCommand.php
│
├── Exports/
│   ├── ArticlesExport.php
│   └── CategoriesExport.php
│
├── Imports/
│   └── CategoriesImport.php
│
├── Models/
│   ├── User.php                        ← SoftDeletes + HasRelatedDataTrait + CommonTrait
│   ├── Category.php
│   ├── NewsArticle.php
│   ├── NewsSummary.php
│   ├── DigestSetting.php
│   ├── DigestLog.php
│   ├── DeviceToken.php
│   └── SavedArticle.php
│
├── Traits/
│   ├── HasRelatedDataTrait.php         ← deletion protection
│   ├── CommonTrait.php                 ← audit fields (created_by etc.)
│   ├── CommonValidationTrait.php       ← shared validation helpers
│   └── DataTableConfiguration.php     ← applyFilters, applySorting, applyPagination
│
├── Mcp/
│   ├── Resources/
│   │   ├── CategoryResource.php        ← exposes categories to Claude AI
│   │   └── ArticleResource.php
│   └── Servers/
│       └── HrmsServer.php              ← registers all MCP resources
│
└── Providers/
    ├── AppServiceProvider.php          ← boot() registers all Observers
    └── RepositoryServiceProvider.php  ← binds all Interfaces → Repositories

config/
└── dependency_checks.php               ← defines which relations block deletion
```

### Vue Frontend — `resources/ts/`

```
resources/ts/
├── @core/
│   ├── services/
│   │   └── ApiService.ts               ← HTTP client used by all stores
│   └── utils/
│       └── validators.ts               ← requiredValidator, emailValidator, etc.
│
├── types/                              ← TypeScript interfaces per module
│   ├── category.ts
│   ├── article.ts
│   └── digest.ts
│
├── stores/                             ← Pinia — composition API + apiCall wrapper
│   ├── auth.ts
│   ├── category.ts                     ← 7 standard methods
│   ├── article.ts
│   ├── digest.ts
│   └── ui.ts                           ← snackbar, loader
│
├── pages/                              ← one file = one route
│   ├── index.vue                       ← dashboard (definePage + stats)
│   └── categories/
│       └── listing.vue                 ← 4 stat cards + ListingTable
│
├── views/                              ← table components (heavy logic)
│   └── categories/
│       └── CategoryListingTable.vue    ← VDataTableServer + Swal + permissions
│
├── components/
│   └── drawers/
│       └── CategoryDrawer.vue          ← PerfectScrollbar + AppDrawerHeaderSection
│
├── composables/
│   ├── useSnackbar.ts
│   └── useCategoryExport.ts            ← Excel/CSV/PDF export
│
├── navigation/
│   └── vertical/
│       └── navigation-with-permissions.ts  ← ri-* icons + permissions array
│
└── utils/
    └── paginationMeta.ts               ← "Showing X-Y of Z" helper
```

---

## 7. Skills — AI Code Generation System

The project includes **17 Skills** — pre-written AI prompt templates that tell Claude exactly how to generate code matching your codebase conventions. Instead of explaining patterns every time, you run a skill and Claude reads your existing reference files first.

### How Skills Work

```
You type:  generate-module NewsSource
              ↓
Claude reads:  app/Models/Department.php
               app/Repositories/DepartmentRepository.php
               resources/ts/stores/department.ts
               ... (all reference files)
              ↓
Claude generates: 19 files that exactly match your conventions
```

### All 17 Skills — Complete Reference

#### Generate Skills (Backend)

| Skill | Argument | Reads First | Creates |
|---|---|---|---|
| `generate-module` | `[ModuleName]` | Department.php + all patterns | All 19 files at once |
| `generate-form-request` | `[ModelName]` | BaseFormRequest.php, DepartmentRequest.php | `app/Http/Requests/{X}Request.php` |
| `generate-observer` | `[ModelName]` | DepartmentObserver.php | `app/Observers/{X}Observer.php` |
| `generate-command` | `[CommandName]` | ExpireSwipeRequests.php | `app/Console/Commands/{X}.php` |
| `generate-middleware` | `[MiddlewareName]` | CacheResponse.php | `app/Http/Middleware/{X}.php` |
| `generate-notification` | `[NotificationName]` | SwipeRequestSubmittedNotification.php | `app/Notifications/{X}.php` |
| `generate-export` | `[moduleName]` | useSwipeRequestExport.ts | `composables/use{X}Export.ts` + dialog |
| `generate-mcp-resource` | `[ModelName]` | DepartmentResource.php | `app/Mcp/Resources/{X}Resource.php` |

#### Generate Skills (Frontend)

| Skill | Argument | Reads First | Creates |
|---|---|---|---|
| `generate-drawer` | `[ModuleName]` | DepartmentDrawer.vue | `components/drawers/{X}Drawer.vue` |
| `generate-listing-page` | `[ModuleName]` | employee-departments/listing.vue | `pages/{module}/listing.vue` |
| `generate-listing-table` | `[ModuleName]` | DepartmentListingTable.vue | `views/{module}/{X}ListingTable.vue` |
| `generate-store` | `[moduleName]` | employeeMaster.ts | `stores/{module}.ts` |

#### Registration Skills (Wire Things Together)

| Skill | Argument | What It Does |
|---|---|---|
| `bind-repository` | `[ModuleName]` | Adds Interface→Repository binding in `RepositoryServiceProvider.php` alphabetically |
| `add-permissions` | `[module-name]` | Adds `['view','create','edit','delete','status']` to `PermissionSeeder.php` + navigation entry |
| `add-deletion-protection` | `[ModelName]` | Adds `HasRelatedDataTrait` + `getRelationshipChecks()` + `config/dependency_checks.php` entry |

#### Audit Skills (Verify Existing Code)

| Skill | Argument | What It Checks |
|---|---|---|
| `audit-module` | `[ModuleName]` | All 19 files exist + all registrations done — reports score like `14/19` |
| `check-model` | `[ModelName]` | SoftDeletes, HasRelatedDataTrait, CommonTrait, status constants, scopeActive, Observer registered |

### Recommended Workflow for Every New Feature

```bash
# 1. Generate all files at once
generate-module NewsSource

# 2. Audit what was created — catch anything missing
audit-module NewsSource

# 3. Fix any missing pieces
bind-repository NewsSource             # if RepositoryServiceProvider was missed
add-permissions news-sources           # adds to PermissionSeeder + navigation
add-deletion-protection NewsSource     # after you define model relationships

# 4. Verify the model is correct
check-model NewsSource

# 5. Run the seeder
php artisan db:seed --class=PermissionSeeder
```

### What generate-module Creates (19 Files)

```
Backend:
  app/Models/NewsSource.php
  database/migrations/XXXX_create_news_sources_table.php
  app/Interfaces/NewsSourceRepositoryInterface.php
  app/Repositories/NewsSourceRepository.php
  app/Http/Controllers/Api/NewsSourceController.php
  app/Http/Requests/NewsSourceRequest.php
  app/Observers/NewsSourceObserver.php

Registrations:
  → RepositoryServiceProvider.php     (binding added)
  → AppServiceProvider.php            (observer registered)
  → routes/api.php                    (route group added)
  → database/seeders/PermissionSeeder.php  (permissions added)
  → config/dependency_checks.php      (entry added)

Frontend:
  resources/ts/types/newsSource.ts
  resources/ts/stores/newsSource.ts
  resources/ts/pages/news-sources/listing.vue
  resources/ts/views/news-sources/NewsSourceListingTable.vue
  resources/ts/components/drawers/NewsSourceDrawer.vue
  → navigation-with-permissions.ts    (nav entry added)

MCP:
  app/Mcp/Resources/NewsSourceResource.php
```

---

## 8. Core Features

### User-Facing Features — Free vs Pro

| Feature | Free Plan | Pro Plan |
|---|---|---|
| Daily digest email | Up to 5 articles | Up to 20 articles |
| Topic categories | Up to 3 | All 8+ categories |
| AI summaries | Included | Included + relevance score |
| Send time | Fixed 07:00 | Any time + timezone |
| Article bookmarks | Last 30 days | Unlimited |
| Digest frequency | Daily only | Daily or weekly |
| Push notifications | Not available | Available |
| Digest history | Last 7 days | Last 90 days |

### Status System (All Features)

| Status Constant | Value | UI Color | Meaning |
|---|---|---|---|
| `STATUS_ARCHIVE` | `'0'` | warning (amber) | Not in use — can delete if no relations |
| `STATUS_ACTIVE` | `'1'` | success (green) | Active and visible |
| `STATUS_INACTIVE` | `'2'` | error (red) | Disabled — exists but hidden |

```
Status flow:
Active  →  Disable (STATUS_INACTIVE)  or  Archive (STATUS_ARCHIVE)
Disabled →  Activate (STATUS_ACTIVE)  or  Archive (STATUS_ARCHIVE)
Archive  →  Activate (STATUS_ACTIVE)
              ↑ blocked if has_related_data = true
```

### Deletion Protection System

```php
// In model:
public function getRelationshipChecks(): array
{
    return [
        'articles' => 'News Articles',   // if articles exist, block delete
        'users'    => 'User Interests',  // if users follow this, block delete
    ];
}

// In config/dependency_checks.php:
'Category' => [
    'articles' => true,
    'users'    => true,
],

// Result: has_related_data flag is appended to every listing row
// Table shows lock icon instead of delete icon when has_related_data = true
```

### Admin Features (Filament Panel)

- Full CRUD on all modules with import/export
- Permission management via PermissionSeeder
- Digest log analytics — sent/failed/skipped counts
- AI cost tracking — prompt tokens per summary
- Activity log via Spatie + Observer cache clearing

---

## 9. API Design & Naming Convention

### The Golden Rule — Same Name in Every Layer

```
Route name  =  Controller method  =  Interface method  =  Repository method
           =  Store action (TS)   =  Component call
```

### Standard 9 Functions Per Feature

| Function | HTTP | What It Does | Payload |
|---|---|---|---|
| `get{Feature}Data` | GET | Paginated listing | `?searchQuery=&page=&itemsPerPage=&sortBy=&orderBy=` |
| `get{Feature}Details` | GET | Single record | `?id=` |
| `manage{Feature}` | POST | Create (no id) OR Update (id present) | `{ id?, ...fields }` |
| `{feature}Delete` | POST | Single or bulk delete | `{ id }` or `{ ids: [] }` |
| `{feature}StatusChange` | POST | Change status | `{ id, newStatus: '0'\|'1'\|'2' }` |
| `{feature}Statistic` | GET | Counts for 4 stat cards | — |
| `{feature}History` | GET | Activity/audit log | `?id=&page=` |
| `{feature}Export` | GET | Download Excel/CSV/PDF | same filters as listing |
| `{feature}Import` | POST | Upload file | `multipart/form-data` |

### Full Trace — `manageCategory` Through Every File

```
routes/api.php
  Route::post('manageCategory', 'manageCategory')

CategoryRepositoryInterface.php
  public function manageCategory(array $data): Category;

CategoryRepository.php
  public function manageCategory(array $data): Category { ... }

CategoryController.php
  public function manageCategory(CategoryRequest $request): JsonResponse { ... }

resources/ts/stores/category.ts
  const manageCategory = async (value: any) =>
    await apiCall('post', '/api/manageCategory', value)

resources/ts/views/categories/CategoryListingTable.vue
  await store.manageCategory(formData.value)
```

### Standard Response Envelope

```json
{
  "success": true,
  "message": "Category created successfully",
  "data": { },
  "errors": null
}
```

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "slug": ["The slug has already been taken."]
  }
}
```

### All Routes — api.php

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/register',         [AuthController::class, 'register']);
    Route::post('auth/login',            [AuthController::class, 'login']);
    Route::post('auth/logout',           [AuthController::class, 'logout']);
    Route::get('auth/me',                [AuthController::class, 'me']);

    // Categories
    Route::controller(CategoryController::class)->group(function () {
        Route::get ('getCategoryData',     'getCategoryData');
        Route::get ('getCategoryDetails',  'getCategoryDetails');
        Route::post('manageCategory',      'manageCategory');
        Route::post('categoryDelete',      'categoryDelete');
        Route::post('categoryStatusChange','categoryStatusChange');
        Route::get ('categoryStatistic',   'categoryStatistic');
        Route::get ('categoryHistory',     'categoryHistory');
        Route::get ('categoryExport',      'categoryExport');
        Route::post('categoryImport',      'categoryImport');
    });

    // Articles
    Route::controller(ArticleController::class)->group(function () {
        Route::get ('getArticleData',      'getArticleData');
        Route::get ('getArticleDetails',   'getArticleDetails');
        Route::post('articleSave',         'articleSave');
        Route::post('articleUnsave',       'articleUnsave');
        Route::get ('articleStatistic',    'articleStatistic');
        Route::get ('articleExport',       'articleExport');
    });

    // Digest
    Route::controller(DigestController::class)->group(function () {
        Route::get ('getDigestToday',          'getDigestToday');
        Route::get ('getDigestData',           'getDigestData');
        Route::post('manageDigestSettings',    'manageDigestSettings');
        Route::get ('getDigestStatistic',      'getDigestStatistic');
    });

    // User
    Route::controller(UserPreferenceController::class)->group(function () {
        Route::get ('getUserPreferences',      'getUserPreferences');
        Route::post('manageUserPreferences',   'manageUserPreferences');
    });

    // Device tokens (push)
    Route::post('deviceTokenStore',  [DeviceController::class, 'deviceTokenStore']);
    Route::post('deviceTokenDelete', [DeviceController::class, 'deviceTokenDelete']);
});
```

---

## 10. DFD — Data Flow Design

### Level 0 — Context Diagram

```
                        ┌───────────────────────────────┐
  User / Mobile ───────►│                               │───────► Email Digest
  (register, prefs)     │                               │
                        │       AI NEWS FILTER          │
  Admin ───────────────►│          SYSTEM               │◄─────── News APIs
  (manage, analytics)   │                               │
                        │                               │
  AI Provider ─────────►│                               │◄─────── Firebase FCM
  (summaries)           │                               │
                        └───────────────────────────────┘
```

### Level 1 — Main Processes

| P# | Process | Reads From | Writes To |
|---|---|---|---|
| P1 | User Auth | — | `users`, `personal_access_tokens` |
| P2 | Manage Preferences | `categories` | `user_categories`, `digest_settings` |
| P3 | Admin Control | all tables | `categories`, `users` |
| P4 | Fetch News (cron) | `categories`, NewsAPI | `news_articles` |
| P5 | AI Summarise (queue) | `news_articles`, Gemini | `news_summaries` |
| P6 | Send Digest (cron) | `users`, `articles`, `summaries` | `digest_logs`, Firebase |

### Level 2 — P4: Fetch News

```
Scheduler (every 3hrs)  →  P4.1 Read active categories
    ↓
P4.2 HTTP fetch NewsAPI  →  fallback to GNews on failure
    ↓
P4.3 Check external_id against news_articles  →  skip duplicates
    ↓
P4.4 Store new articles  →  Observer fires  →  SummarizeJob dispatched
```

### Level 2 — P6: Send Digest

```
Scheduler (daily at user send_time)
    ↓
P6.1 Get users with digest_enabled=true
    ↓
P6.2 For each user: fetch articles matching user_categories
    ↓
P6.3 Attach summaries → render Blade email template
    ↓
P6.4 Send via Resend  →  Push via Firebase  →  Write to digest_logs
```

---

## 11. Scheduler & Automation

### Scheduled Commands

```php
// routes/console.php

Schedule::command('news:fetch')
    ->everyThreeHours()
    ->withoutOverlapping()       // prevents double-run on slow servers
    ->runInBackground();

Schedule::command('news:summarize')
    ->everyThirtyMinutes()
    ->withoutOverlapping();

Schedule::command('news:send-digest')
    ->dailyAt('07:00')
    ->onOneServer()              // prevents duplicate on multi-server
    ->runInBackground();

Schedule::command('news:cleanup --days=30')
    ->weekly()
    ->onOneServer();
```

### Server Cron — One Line Only

```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

### Queue Jobs — Production Patterns

```php
class SummarizeArticleJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;     // retry 3 times
    public int $backoff = 60;    // wait 60s between retries
    public int $timeout = 120;   // kill after 2 minutes

    public function handle(AISummaryService $ai): void
    {
        $ai->summarize($this->article);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Summarise failed #{$this->article->id}: " . $e->getMessage());
    }
}
```

---

## 12. Current Build Scope v1.0

| Feature | Status |
|---|---|
| User registration, login, logout | ✅ v1.0 |
| Sanctum token auth (web + mobile) | ✅ v1.0 |
| Category management — full CRUD + import/export | ✅ v1.0 |
| 3-status system (Active/Archive/Inactive) | ✅ v1.0 |
| Deletion protection (HasRelatedDataTrait) | ✅ v1.0 |
| User interest / topic preferences | ✅ v1.0 |
| Automated news fetching (NewsAPI + GNews) | ✅ v1.0 |
| AI article summarisation (Gemini 2.0 Flash) | ✅ v1.0 |
| Daily email digest (Resend) | ✅ v1.0 |
| Admin panel (Filament) | ✅ v1.0 |
| Article bookmarks (save/unsave) | ✅ v1.0 |
| Digest history log | ✅ v1.0 |
| Dashboard statistic cards | ✅ v1.0 |
| Push notification infrastructure (Firebase) | ✅ v1.0 |
| Import / Export Excel (Maatwebsite) | ✅ v1.0 |
| Repository-Interface pattern | ✅ v1.0 |
| Observer cache invalidation | ✅ v1.0 |
| Permission-gated UI | ✅ v1.0 |
| MCP Resources for AI querying | ✅ v1.0 |
| TypeScript frontend (all stores + components) | ✅ v1.0 |
| 17 Skills for code scaffolding | ✅ v1.0 |
| Consistent naming (all 9 layers) | ✅ v1.0 |

---

## 13. Future Scope

### Phase 2 — Enhanced AI (3–6 months)

| Feature | Description |
|---|---|
| AI Personalisation Engine | Analyse click patterns; fine-tune summaries per reading style |
| Smart Duplicate Detection | AI compares semantics — removes same-story from different URLs |
| Sentiment Analysis | Tag articles positive/negative/neutral; filter by sentiment |
| Auto-categorisation | AI assigns category to articles without a clear match |
| AI Chat with News | "What happened in AI this week?" using RAG over stored articles |
| Trending Topics Detection | Identify emerging topics before they have a category |

### Phase 3 — Native Mobile Apps (6–9 months)

> The API is already 100% mobile-ready. No backend changes needed.

| Platform | Technology |
|---|---|
| iOS (iPhone / iPad) | Flutter — same `/api/*` endpoints, Sanctum tokens, FCM via APNs |
| Android | Flutter — identical API, native FCM support |
| Cross-platform | Flutter recommended — single codebase, 70% code shared |

**Mobile-specific features:**
- Offline reading — SQLite cache (`drift` package)
- Swipe gestures — right to save, left to dismiss
- iOS Home Screen widget — today's top 3 headlines
- Deep linking — email digest links open in app
- Biometric auth — Face ID / fingerprint
- Reading time tracker — "You saved 18 minutes today"

### Phase 4 — Monetisation & SaaS (9–12 months)

| Feature | Description |
|---|---|
| Razorpay Subscriptions | Indian payments — monthly/annual Pro with webhooks |
| Stripe Subscriptions | International payments |
| Team Plans | Company accounts — 10 seats, shared categories |
| White-label API | Other companies use the news+AI pipeline |
| Referral System | Free Pro months for referrals |

### Phase 5 — Advanced Platform

| Feature | Description |
|---|---|
| Multi-language | Hindi, Gujarati, Spanish — Gemini supports 40+ languages |
| Podcast Digest | TTS API — listen during commute |
| Browser Extension | Chrome/Firefox — one-click save any article |
| Social Sharing | Share AI summary card to WhatsApp/Instagram |
| Newsletter Builder | Public shareable digest link |
| Slack / Teams Integration | Send digest to workspace channel |
| RSS Feed Support | Custom RSS feeds (company blog, etc.) |
| Apple Watch App | Glanceable headline digest |

---

## 14. Mobile App Architecture

### What the Mobile App Gets for Free

| Already Built | Where |
|---|---|
| All API endpoints work for mobile | Same `/api/*` routes |
| Push notification infrastructure | `device_tokens` table + `PushNotificationService` |
| Per-device token naming + expiry | Sanctum `device_name` + `expires_at` |
| CORS open for all origins | `config/cors.php` |
| Standard JSON envelope | `{ success, message, data, errors }` |
| Platform detection | `platform (ios\|android)` in `device_tokens` |

### Flutter Packages

| Package | Purpose |
|---|---|
| `dio` | HTTP client with interceptors |
| `flutter_riverpod` | State management |
| `flutter_secure_storage` | Store Sanctum token |
| `firebase_messaging` | FCM push notifications |
| `go_router` | Navigation + deep linking |
| `drift` | SQLite offline cache |
| `local_auth` | Face ID / fingerprint |
| `share_plus` | Share summaries |

---

## 15. Environment Configuration

### Laravel — `.env`

```env
APP_NAME="AI News Filter"
APP_ENV=production
APP_KEY=                              # php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app.railway.app

DB_CONNECTION=pgsql
DB_HOST=ep-xxx.us-east-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=your_password
DB_SSLMODE=require

QUEUE_CONNECTION=redis
REDIS_URL=rediss://default:xxx@fly-xxx.upstash.io:6379

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=digest@yourdomain.com
RESEND_API_KEY=re_xxxxxxxxxxxx

GEMINI_API_KEY=your_gemini_key
NEWS_API_KEY=your_newsapi_key
GNEWS_API_KEY=your_gnews_key

FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
SANCTUM_STATEFUL_DOMAINS=localhost:5173,yourdomain.com
```

### Vue — `.env`

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME="AI News Filter"
```

> All Vue env vars **must start with `VITE_`**. Access via `import.meta.env.VITE_xxx`.

### Local Dev Shortcuts

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
QUEUE_CONNECTION=database    # no Redis needed locally
MAIL_MAILER=log              # emails go to storage/logs/laravel.log
```

---

## 16. Development Workflow

### Daily Commands

```bash
# Terminal 1 — Laravel
cd laravel-version && php artisan serve       # :8000

# Terminal 2 — Vue
cd javascript-version && npm run dev          # :5173

# Terminal 3 — Queue worker
php artisan queue:work

# Terminal 4 — Horizon (optional)
php artisan horizon
```

### New Feature — Full 10-Step Checklist

```bash
# Step 1 — Use generate-module skill
generate-module NewsSource

# Step 2 — Audit immediately
audit-module NewsSource

# Step 3 — Fix any missed registrations
bind-repository NewsSource
add-permissions news-sources
add-deletion-protection NewsSource

# Step 4 — Verify model
check-model NewsSource

# Step 5 — Run migration
php artisan migrate

# Step 6 — Seed permissions
php artisan db:seed --class=PermissionSeeder

# Step 7 — Test API
php artisan route:list | grep NewsSource

# Step 8 — Frontend: verify store functions match API names
# Step 9 — Frontend: add to navigation/vertical/navigation-with-permissions.ts
# Step 10 — Frontend: add route to router
```

### Useful Artisan Commands

```bash
php artisan migrate:fresh --seed          # reset DB + seed (dev only)
php artisan schedule:list                 # see all scheduled tasks
php artisan schedule:run                  # test scheduler manually
php artisan queue:failed                  # list failed jobs
php artisan queue:retry all               # retry all failed
php artisan route:list                    # see all routes
php artisan tinker                        # REPL for testing
```

---

## 17. Advanced Future Technologies

| Technology | When Needed | What It Does |
|---|---|---|
| Laravel Octane | 10,000+ req/day | Doubles API throughput via Swoole/RoadRunner |
| Elasticsearch | 1M+ articles | Sub-ms search replacing MySQL full-text |
| Redis Pub/Sub | Real-time features | Live digest updates without polling |
| Laravel Reverb | Live notifications | WebSocket alerts to web dashboard |
| Vector Database | Semantic search | Find articles by meaning, not keywords |
| CDN (Cloudflare) | Global users | Cache API at edge, reduce server load |
| Kubernetes | 100k+ users | Auto-scale Laravel workers |
| OpenAI Fine-tuning | Hyper-personalisation | Train on user reading patterns |
| GraphQL (Lighthouse) | Complex mobile queries | Request exactly the fields needed |

---

## 18. Skills Built by This Project

### Laravel Skills

| Skill | Where You Learn It |
|---|---|
| Queue Jobs + Horizon | `SummarizeArticleJob`, `SendDigestJob` |
| Task Scheduler | `news:fetch`, `news:send-digest` commands |
| API Resources | `CategoryResource`, `ArticleResource` |
| FormRequest (3-method pattern) | `CategoryRequest` — baseRules/createRules/updateRules |
| Repository Pattern | `CategoryRepository` + Interface |
| Observer + Cache Clear | `CategoryObserver.clearCache()` |
| Sanctum Token Auth | Mobile login + per-device tokens |
| Excel Import/Export | `CategoriesExport`, `ArticlesExport` |
| AI SDK | Gemini calls in `AISummaryService` |
| MCP Resources | `CategoryResource` in `Mcp/Resources/` |
| Deletion Protection | `HasRelatedDataTrait` + `dependency_checks.php` |
| Status System | 3-value constants on every model |
| Permission System | `PermissionSeeder` + `authorizeUser()` |
| Soft Deletes | `deleted_by` audit field on all tables |
| Skills / Code Generation | 17 custom AI scaffolding templates |

### Vue / TypeScript Skills

| Skill | Where You Learn It |
|---|---|
| Typed Pinia Stores | `category.ts` — `apiCall` wrapper + typed methods |
| ApiService | `@/@core/services/ApiService` — header injection |
| TypeScript Interfaces | `resources/ts/types/category.ts` |
| Permission-gated UI | `authStore.has_permission()` in every table |
| SweetAlert2 | Swal.fire() for all destructive actions |
| VDataTableServer | Server-side pagination + sort in `ListingTable.vue` |
| PerfectScrollbar | Drawer scroll wrapper in `CategoryDrawer.vue` |
| definePage() | Permission meta on every listing page |
| Remixicon (`ri-*`) | Icon system in navigation + buttons |
| paginationMeta | "Showing X–Y of Z" utility |
| Export Composable | `useCategoryExport.ts` — ExcelJS + CSV + PDF |
| Deletion Protection UI | `has_related_data` flag — lock icon vs delete icon |

---

## 19. Quick Reference Card

### The 10 Naming Rules

```
1.  Route name         = Controller method  = Repo method  = Interface method
2.  Store action (TS)  = Component call     → all identical names
3.  manage{Feature}    = Create (no id) OR Update (id present)
4.  get{Feature}Data   = paginated listing with searchQuery/page/itemsPerPage/sortBy/orderBy
5.  get{Feature}Details = single record by ?id=
6.  {feature}Delete    = POST with { id } or { ids: [] } for bulk
7.  {feature}StatusChange = POST with { id, newStatus: '0'|'1'|'2' }
8.  {feature}Statistic = GET for 4 stat cards (total, active, archive, disabled)
9.  {feature}Export    = GET with same params as getData, responseType: blob
10. {feature}Import    = POST multipart/form-data with file field
```

### Status Values — Memorise These

```
'0' = STATUS_ARCHIVE  = warning colour  = amber  = not in use
'1' = STATUS_ACTIVE   = success colour  = green  = active
'2' = STATUS_INACTIVE = error colour    = red    = disabled
```

### Skill Command Quick Reference

```bash
generate-module NewsSource          # all 19 files at once
generate-drawer NewsSource          # just the drawer component
generate-listing-page NewsSource    # just the listing page
generate-listing-table NewsSource   # just the table component
generate-store newsSource           # just the Pinia store
generate-form-request NewsSource    # just the FormRequest
generate-observer NewsSource        # just the Observer
generate-command ProcessNewsSource  # just the Artisan command
generate-notification NewsSent      # just the Notification
generate-export newsSource          # export composable + dialog
generate-mcp-resource NewsSource    # MCP resource for Claude AI
bind-repository NewsSource          # register in ServiceProvider
add-permissions news-sources        # seeder + nav entry
add-deletion-protection NewsSource  # protect model from delete
audit-module NewsSource             # check all 19 files
check-model NewsSource              # verify model conventions
```

### Free Services Cheat Sheet

| Service | Free Limit | URL |
|---|---|---|
| Railway | Free container | railway.app |
| Neon PostgreSQL | 512 MB | neon.tech |
| Upstash Redis | 10,000 cmds/day | upstash.com |
| Resend | 3,000 emails/month | resend.com |
| Gemini 2.0 Flash | Generous quota | aistudio.google.com |
| NewsAPI | Free dev plan | newsapi.org |
| GNews | 100 req/day | gnews.io |
| Firebase FCM | Free forever | firebase.google.com |

### 3 Production-Grade Patterns

```php
// 1. withoutOverlapping() — prevents disaster on slow servers
Schedule::command('news:fetch')->everyThreeHours()->withoutOverlapping();

// 2. failed() on every Job — always know when something breaks
public function failed(\Throwable $e): void {
    Log::error("Job failed: " . $e->getMessage());
}

// 3. Deletion protection — never break foreign keys silently
public function getRelationshipChecks(): array {
    return ['articles' => 'News Articles'];
}
```

---

*AI News Filter — Project Documentation · Version 1.0.0 · March 2026*
*Laravel 12 + Vue 3 + TypeScript + Gemini AI · 17 Skills · Web + iOS + Android*
