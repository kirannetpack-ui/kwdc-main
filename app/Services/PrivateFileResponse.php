<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PrivateFileResponse
{
    public function send(string $path, bool $inline = false, string $diskName = 'private_uploads')
    {
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($path), 404);

        $headers = ['Cache-Control' => 'private, no-store'];
        if (config('kwdc.serverless') && config("filesystems.disks.{$diskName}.driver") === 'r2') {
            $url = $disk->temporaryUrl($path, now()->addMinute(), [
                'ResponseCacheControl' => $headers['Cache-Control'],
                'ResponseContentDisposition' => $inline ? 'inline' : 'attachment',
            ]);

            return redirect()->away($url, 302, $headers);
        }

        return $inline ? $disk->response($path, null, $headers) : $disk->download($path, null, $headers);
    }
}
