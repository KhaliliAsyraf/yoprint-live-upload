<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">CSV Uploads</h1>

        <div class="flex items-center space-x-3">
            @if(session()->has('message'))
                <div class="text-sm text-green-600">{{ session('message') }}</div>
            @endif

            <div>
                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded">
                    <span>Upload File</span>
                    <input type="file" class="hidden" wire:model="file" />
                </label>
                @error('file') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <!-- Poll every 2 seconds for changes (near realtime) -->
    <div wire:poll.2s="refresh">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="px-2 py-2">Uploaded At</th>
                    <th class="px-2 py-2">Filename</th>
                    <th class="px-2 py-2">Status</th>
                    <th class="px-2 py-2">Processed At</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($uploads as $u)
                    <tr>
                        <td class="px-2 py-2 text-sm">{{ $u['uploaded_at'] . ' (' . $u['uploaded_at_human'] . ')' }}</td>
                        <td class="px-2 py-2 text-sm">{{ $u['original_name'] }}</td>
                        <td class="px-2 py-2 text-sm">
                            <span class="px-2 py-1 rounded {{ $u['status'] === 'completed' ? 'bg-green-100' : ($u['status'] === 'failed' ? 'bg-red-100' : 'bg-yellow-100') }}">
                                {{ ucfirst($u['status']) }}
                            </span>
                        </td>
                        <td class="px-2 py-2 text-sm">{{ $u['processed_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
