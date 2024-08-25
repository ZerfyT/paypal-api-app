@props(['type', 'data'])


@php
    if ($type === 'payment') {
        $class = $data->status === 'completed' ? 'bg-green-500' : 'bg-bittersweet-500';
    } elseif ($type === 'subscription') {
        $class =
            $data->status === 'ACTIVE'
                ? 'bg-green-500'
                : (in_array($data->status, ['APPROVAL_PENDING', 'APPROVED'])
                    ? 'bg-yellow-500'
                    : 'bg-bittersweet-500');
    }
@endphp

<span class="px-1 py-1 rounded text-white text-sm font-bold uppercase {{ $class }}">{{ $data->status }}</span>
