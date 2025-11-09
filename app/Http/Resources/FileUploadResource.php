<?php

namespace App\Http\Resources;

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
            'original_name' => $this->original_name,
            'path' => $this->path,
            'checksum' => $this->checksum,
            'status' => $this->status,
            'error' => $this->error,
            'uploaded_at' => optional($this->uploaded_at)->toDateTimeString(),
            'processed_at' => optional($this->processed_at)->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
