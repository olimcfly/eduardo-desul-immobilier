<?php

namespace App\Modules\ContentTraffic\Blog\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploader
{
    public function upload(UploadedFile $file): string
    {
        if (!$this->isAllowed($file)) {
            throw new \InvalidArgumentException('File type not allowed');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = config('blog.media.path') . '/' . date('Y/m');

        return Storage::disk(config('blog.media.disk'))
            ->putFileAs($path, $file, $filename);
    }

    public function delete(string $path): bool
    {
        return Storage::disk(config('blog.media.disk'))->delete($path);
    }

    private function isAllowed(UploadedFile $file): bool
    {
        $allowed = config('blog.media.allowed_extensions');
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, $allowed);
    }
}
