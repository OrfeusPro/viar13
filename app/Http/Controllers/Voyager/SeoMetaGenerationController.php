<?php

namespace App\Http\Controllers\Voyager;

use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use App\Services\SeoMetaGeneration\SeoMetaGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class SeoMetaGenerationController extends VoyagerBaseController
{
    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaGenerator
     */
    private $generator;

    /**
     * @var \App\Services\SeoMetaGeneration\SeoMetaContextResolver
     */
    private $contextResolver;

    public function __construct(SeoMetaGenerator $generator, SeoMetaContextResolver $contextResolver)
    {
        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
    }

    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'model' => 'required|string',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Invalid SEO meta generation request.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['model', 'id']);
        $modelClass = (string) $data['model'];
        $target = $this->contextResolver->targetForModel($modelClass);

        if ($target === null || !class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            return new JsonResponse(['ok' => false, 'message' => 'Unsupported SEO meta model.'], 422);
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $entity = $modelClass::query()->find((int) $data['id']);

        if (!$entity instanceof Model) {
            return new JsonResponse(['ok' => false, 'message' => 'Entity was not found.'], 404);
        }

        if (!$this->canEditBread($target)) {
            return new JsonResponse(['ok' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $result = $this->generator->generate($entity, $this->contextResolver->supportedLocales(), true);
            $this->saveGeneratedMeta($entity, $target, $result['locales']);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return new JsonResponse([
            'ok' => true,
            'cached' => $result['cached'],
            'saved' => true,
            'title_field' => (string) $target['title_field'],
            'description_field' => (string) $target['description_field'],
            'locales' => $result['locales'],
        ]);
    }

    /**
     * @param array<string, mixed> $target
     */
    private function canEditBread(array $target): bool
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }

        $dataType = Voyager::model('DataType')->where('slug', (string) $target['voyager_slug'])->first();

        return $dataType && $user->hasPermission('edit_' . $dataType->name);
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, array{meta_title?: string|null, meta_description?: string|null}> $locales
     */
    private function saveGeneratedMeta(Model $entity, array $target, array $locales): void
    {
        $titleField = (string) $target['title_field'];
        $descriptionField = (string) $target['description_field'];
        $titleTranslations = [];
        $descriptionTranslations = [];

        foreach ($locales as $locale => $values) {
            if (isset($values['meta_title'])) {
                $titleTranslations[(string) $locale] = (string) $values['meta_title'];
            }

            if (isset($values['meta_description'])) {
                $descriptionTranslations[(string) $locale] = (string) $values['meta_description'];
            }
        }

        DB::transaction(function () use ($entity, $titleField, $descriptionField, $titleTranslations, $descriptionTranslations) {
            $this->saveFieldTranslations($entity, $titleField, $titleTranslations);
            $this->saveFieldTranslations($entity, $descriptionField, $descriptionTranslations);
        });
    }

    /**
     * @param array<string, string> $translations
     */
    private function saveFieldTranslations(Model $entity, string $field, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        if (method_exists($entity, 'setAttributeTranslations')) {
            $entity->setAttributeTranslations($field, $translations, true);
            $entity->save();

            return;
        }

        $defaultLocale = (string) config('voyager.multilingual.default', 'en');

        if (array_key_exists($defaultLocale, $translations)) {
            $entity->setAttribute($field, $translations[$defaultLocale]);
            $entity->save();
        }
    }
}
