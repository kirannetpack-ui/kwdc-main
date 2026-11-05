<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DirectUploadController
{
    public function __invoke(Request $request)
    {
        abort_unless(config('filesystems.direct_uploads') && ! config('kwdc.demo')
            && config('filesystems.disks.private_uploads.driver') === 'r2', 404);

        $file = $request->validate([
            'field' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z0-9_]+)*$/D'],
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:10485760'],
        ]);
        $key = 'staged-uploads/'.Str::uuid();
        $expires = now()->addMinutes(10);
        $upload = Storage::disk('private_uploads')->temporaryUploadUrl($key, $expires);

        return response()->json($upload + [
            'token' => Crypt::encryptString(json_encode($file + [
                'key' => $key,
                'user' => $request->user()->id,
                'expires' => $expires->timestamp,
            ], JSON_THROW_ON_ERROR)),
        ])->header('Cache-Control', 'no-store');
    }
}
