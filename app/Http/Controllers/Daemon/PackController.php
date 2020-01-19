<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Controllers\Daemon;

use Storage;
use Amghost\Models;
use Illuminate\Http\Request;
use Amghost\Http\Controllers\Controller;

class PackController extends Controller
{
    /**
     * Pulls an install pack archive from the system.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uuid
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function pull(Request $request, $uuid)
    {
        $pack = Models\Pack::where('uuid', $uuid)->first();

        if (! $pack) {
            return response()->json(['error' => 'No such pack.'], 404);
        }

        if (! Storage::exists('packs/' . $pack->uuid . '/archive.tar.gz')) {
            return response()->json(['error' => 'There is no archive available for this pack.'], 503);
        }

        return response()->download(storage_path('app/packs/' . $pack->uuid . '/archive.tar.gz'));
    }

    /**
     * Returns the hash information for a pack.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function hash(Request $request, $uuid)
    {
        $pack = Models\Pack::where('uuid', $uuid)->first();

        if (! $pack) {
            return response()->json(['error' => 'No such pack.'], 404);
        }

        if (! Storage::exists('packs/' . $pack->uuid . '/archive.tar.gz')) {
            return response()->json(['error' => 'There is no archive available for this pack.'], 503);
        }

        return response()->json([
            'archive.tar.gz' => sha1_file(storage_path('app/packs/' . $pack->uuid . '/archive.tar.gz')),
        ]);
    }

    /**
     * Pulls an update pack archive from the system.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function pullUpdate(Request $request)
    {
    }
}
