<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaController extends Controller
{
    /**
     * Cursor-paginated, searchable, kind-filterable list.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = 40;

        $paginator = Media::query()
            ->search($request->string('q'))
            ->kind($request->string('kind'))
            ->latest()
            ->cursorPaginate($perPage);

        return MediaResource::collection($paginator);
    }

    /**
     * Store one or more uploaded files.
     */
    public function store(StoreMediaRequest $request, MediaUploader $uploader): JsonResponse
    {
        $items = collect($request->file('files'))
            ->map(fn ($file) => $uploader->store($file, $request->user()?->id));

        return MediaResource::collection($items)
            ->response()
            ->setStatusCode(201);
    }
}
