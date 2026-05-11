@if($msg->attachment)
    @if($msg->attachment_type === 'image')
        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank">
            <img src="{{ asset('storage/' . $msg->attachment) }}"
                class="max-w-xs rounded-xl mt-1 border border-gray-200 hover:opacity-90 transition cursor-pointer">
        </a>
    @else
        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank"
            class="flex items-center gap-2 mt-1 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs text-indigo-600 hover:bg-indigo-50 transition">
            <i class="fas fa-file-alt"></i>
            {{ basename($msg->attachment) }}
            <i class="fas fa-download ml-auto text-gray-400"></i>
        </a>
    @endif
@endif
