<?php

class SecteurPublishService
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DATA_READY = 'data_ready';
    public const STATUS_AI_GENERATED = 'ai_generated';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_DATA_READY,
            self::STATUS_AI_GENERATED,
            self::STATUS_REVIEWED,
            self::STATUS_PUBLISHED,
            self::STATUS_ARCHIVED,
        ];
    }

    public function normalizeStatus(string $status): string
    {
        return in_array($status, self::allowedStatuses(), true) ? $status : self::STATUS_DRAFT;
    }

    public function isPublished(string $status): bool
    {
        return $status === self::STATUS_PUBLISHED;
    }

    public function computePublishedAt(string $previousStatus, string $nextStatus, ?string $existingPublishedAt): ?string
    {
        if ($nextStatus === self::STATUS_PUBLISHED) {
            return $existingPublishedAt ?: date('Y-m-d H:i:s');
        }

        if ($previousStatus === self::STATUS_PUBLISHED && $nextStatus !== self::STATUS_PUBLISHED) {
            return null;
        }

        return $existingPublishedAt;
    }
}
