<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Category;
use App\Models\DigestSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends ApiBaseController
{
    public function getUserPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $allCategories     = Category::where('status', '1')->orderBy('sort_order')->get();
        $selectedCategoryIds = $user->categories()->pluck('categories.id')->toArray();

        $digestSetting = DigestSetting::firstOrCreate(
            ['user_id' => $user->id],
            ['digest_enabled' => true, 'frequency' => 'daily', 'send_time' => '07:00:00', 'timezone' => 'UTC', 'max_articles' => 5]
        );

        return $this->success([
            'categories'     => $allCategories->map(fn($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'slug'     => $c->slug,
                'icon'     => $c->icon,
                'selected' => in_array($c->id, $selectedCategoryIds),
            ]),
            'digest_setting' => [
                'digest_enabled' => $digestSetting->digest_enabled,
                'frequency'      => $digestSetting->frequency,
                'send_time'      => $digestSetting->send_time,
                'timezone'       => $digestSetting->timezone,
                'max_articles'   => $digestSetting->max_articles,
            ],
        ]);
    }

    public function manageUserPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category_ids'                  => 'array',
            'category_ids.*'                => 'integer|exists:categories,id',
            'digest_setting'                => 'array',
            'digest_setting.digest_enabled' => 'boolean',
            'digest_setting.frequency'      => 'in:daily,weekly',
            'digest_setting.send_time'      => 'date_format:H:i',
            'digest_setting.timezone'       => 'string|max:100',
            'digest_setting.max_articles'   => 'integer|min:1|max:20',
        ]);

        // Sync categories
        if (isset($data['category_ids'])) {
            $user->categories()->sync($data['category_ids']);
        }

        // Update digest settings
        if (isset($data['digest_setting'])) {
            DigestSetting::updateOrCreate(
                ['user_id' => $user->id],
                $data['digest_setting']
            );
        }

        return $this->success(null, 'Preferences saved successfully.');
    }
}
