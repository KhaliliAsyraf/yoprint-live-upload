<?php

namespace App\Livewire;

use App\Http\Resources\FileUploadResource;
use App\Jobs\ProcessCsvUpload;
use App\Models\FileUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadManager extends Component
{
    use WithFileUploads;

    public $file;
    public $uploads; // latest uploads list for table

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:102400', // max 10 MB
    ];

    public function mount()
    {
        $this->loadUploads();
    }

    public function loadUploads()
    {
        $this->uploads = FileUploadResource::collection($this->fileUploadQuery())->resolve();
    }

    public function updatedFile()
    {
        $this->validate();

        /** @var UploadedFile $file */
        $file = $this->file;

        // compute checksum for idempotency
        $tmpPath = $file->getRealPath();
        $contents = file_get_contents($tmpPath);
        $checksum = hash('sha256', $contents . $file->getClientOriginalName() . request()->ip());

        // if identical file already processed completed => do not create duplicate
        $existing = FileUpload::where('checksum', $checksum)->first();
        if ($existing && $existing->status === 'completed') {
            // optionally you can flash message or just reload uploads
            session()->flash('message', 'This file was already uploaded and processed.');
            $this->reset('file');
            $this->loadUploads();
            return;
        }

        // store file
        $filename = now()->format('Ymd_His_') . $file->getClientOriginalName();
        $path = $file->storeAs('uploads', $filename);

        $upload = FileUpload::create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'checksum' => $checksum,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        // Dispatch job
        ProcessCsvUpload::dispatch($upload->id)->onQueue('default');

        session()->flash('message', 'File uploaded and queued for processing.');

        $this->reset('file');
        $this->loadUploads();
    }

    // called by wire:poll
    public function refresh()
    {
        $this->loadUploads();
    }

    /**
     * Render page
     * 
     * @return View
     */
    public function render(): View
    {
        return view('livewire.upload-manager');
    }

    /**
     * File query
     * 
     * @return Collection
     */
    public function fileUploadQuery(): Collection
    {
        return FileUpload::select('uploaded_at', 'original_name', 'status', 'processed_at')
            ->orderByDesc('created_at')
            ->get();
    }
}