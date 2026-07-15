<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * A single media item (used by the builder to resolve a stored id).
     */
    public function show(Media $medium): MediaResource
    {
        return new MediaResource($medium);
    }

    /**
     * Update the editable, searchable metadata.
     */
    public function update(UpdateMediaRequest $request, Media $medium): MediaResource
    {
        $medium->update($request->validated());

        return new MediaResource($medium);
    }

    /**
     * Delete a file — unless it is still attached somewhere, in which case
     * return 409 with the list of usages so the UI can explain why.
     */
    public function destroy(Media $medium): JsonResponse
    {
        $usages = $this->usagesFor($medium);

        if ($usages->isNotEmpty()) {
            return response()->json([
                'message' => 'This file is still in use and cannot be deleted.',
                'usages' => $usages->values(),
            ], 409);
        }

        Storage::disk($medium->disk)->delete($medium->path);
        $medium->forceDelete();

        return response()->json(status: 204);
    }

    /**
     * Rows in `mediables` that reference this media item, as {type,label}.
     *
     * @return \Illuminate\Support\Collection<int, array{type: string, label: string}>
     */
    protected function usagesFor(Media $medium): \Illuminate\Support\Collection
    {
        return DB::table('mediables')
            ->where('media_id', $medium->id)
            ->get()
            ->map(fn ($row): array => [
                'type' => class_basename($row->mediable_type),
                'label' => Str::of(class_basename($row->mediable_type))
                    ->headline()
                    ->append(' #'.$row->mediable_id)
                    ->toString(),
            ]);
    }
}
