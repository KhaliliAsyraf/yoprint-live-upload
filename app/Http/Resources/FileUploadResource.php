<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name ?? null,
            'path' => $this->path ?? null,
            'checksum' => $this->checksum ?? null,
            'status' => $this->status ?? null,
            'error' => $this->error ?? null,
            'uploaded_at' => Carbon::parse($this->uploaded_at)->toDateTimeString() ?? null,
            'processed_at' => Carbon::parse($this->processed_at)->toDateTimeString() ?? null,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString() ?? null,
            'uploaded_at_human' => Carbon::parse($this->uploaded_at)->diffForHumans() ?? null
        ];
    }
}
