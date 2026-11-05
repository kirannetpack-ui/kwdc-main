<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HydrateDirectUploads
{
    public function handle(Request $request, Closure $next)
    {
        $encoded = $request->input('_direct_uploads');
        if ($encoded === null) {
            return $next($request);
        }

        abort_unless(config('filesystems.direct_uploads') && ! config('kwdc.demo')
            && config('filesystems.disks.private_uploads.driver') === 'r2', 404);
        $tokens = is_string($encoded) && strlen($encoded) <= 32000 ? json_decode($encoded, true) : null;
        if (! is_array($tokens) || ! array_is_list($tokens) || count($tokens) > 20 || $request->files->count()) {
            $this->invalidUpload();
        }

        $disk = Storage::disk('private_uploads');
        $temporaryPaths = [];
        $files = [];
        $total = 0;
        try {
            foreach ($tokens as $token) {
                try {
                    $upload = is_string($token) ? json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR) : null;
                } catch (\Illuminate\Contracts\Encryption\DecryptException | \JsonException) {
                    $this->invalidUpload();
                }

                if (! is_array($upload) || ! isset($upload['user'], $upload['expires'], $upload['field'], $upload['size'], $upload['key'], $upload['name'])
                    || $upload['user'] !== $request->user()->id || $upload['expires'] < now()->timestamp
                    || Arr::has($files, $upload['field']) || ($total += (int) $upload['size']) > 64 * 1024 * 1024) {
                    $this->invalidUpload();
                }

                $key = $upload['key'];
                if (! Cache::add('direct-upload:'.$key, true, 660) || ! $disk->exists($key)) {
                    $this->invalidUpload();
                }
                $temporaryPath = tempnam(sys_get_temp_dir(), 'kwdc-upload-');
                if ($temporaryPath === false) {
                    throw new \RuntimeException('Cannot create a temporary upload file.');
                }
                $temporaryPaths[] = $temporaryPath;
                $source = $disk->readStream($key);
                if (! is_resource($source)) {
                    $this->invalidUpload();
                }
                try {
                    $target = fopen($temporaryPath, 'wb');
                    if ($target === false) {
                        throw new \RuntimeException('Cannot open a temporary upload file.');
                    }
                    try {
                        $size = stream_copy_to_stream($source, $target, (int) $upload['size'] + 1);
                    } finally {
                        fclose($target);
                    }
                } finally {
                    fclose($source);
                }
                if ($size !== (int) $upload['size']) {
                    $this->invalidUpload();
                }

                Arr::set($files, $upload['field'], new UploadedFile($temporaryPath, $upload['name'], test: true));
                $disk->delete($key);
            }
            $request->files->replace($files);
            $request->request->remove('_direct_uploads');

            return $next($request);
        } finally {
            foreach ($temporaryPaths as $temporaryPath) {
                unlink($temporaryPath);
            }
        }
    }

    private function invalidUpload(): never
    {
        throw ValidationException::withMessages(['_direct_uploads' => 'A staged file was invalid or expired. Please select your files and submit again.']);
    }
}
