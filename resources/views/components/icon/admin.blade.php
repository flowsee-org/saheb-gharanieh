{{-- Panel UI icons. One file, one switch: the panel needs a couple of dozen
     small marks, and thirty near-identical component files would only obscure
     that they are all the same 24-grid line art as the menu's glyphs. --}}
@props(['name'])

<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" {{ $attributes }}>
    <g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        @switch($name)
            @case('dashboard')
                <path d="M4 10.5 12 4l8 6.5V19a1 1 0 0 1-1 1h-4v-6H9v6H5a1 1 0 0 1-1-1v-8.5Z" />
                @break

            @case('items')
                <path d="M4 7h16M4 12h16M4 17h10" />
                <circle cx="18.5" cy="17" r="1.4" fill="currentColor" stroke="none" />
                @break

            @case('categories')
                <path d="M12 3.5 20.5 8 12 12.5 3.5 8 12 3.5Z" />
                <path d="M4.5 12.5 12 16.5l7.5-4" opacity=".6" />
                <path d="M4.5 16.5 12 20.5l7.5-4" opacity=".35" />
                @break

            @case('settings')
                <path d="M5 7h14M5 12h14M5 17h14" opacity=".5" />
                <circle cx="9" cy="7" r="2.1" />
                <circle cx="15" cy="12" r="2.1" />
                <circle cx="8" cy="17" r="2.1" />
                @break

            @case('account')
                <circle cx="12" cy="8.5" r="3.6" />
                <path d="M4.8 20c.9-3.6 3.7-5.6 7.2-5.6s6.3 2 7.2 5.6" />
                @break

            @case('eye')
                <path d="M2.6 12S6 6.4 12 6.4 21.4 12 21.4 12S18 17.6 12 17.6 2.6 12 2.6 12Z" />
                <circle cx="12" cy="12" r="2.8" />
                @break

            @case('logout')
                <path d="M14.5 7.5V5.6a1.6 1.6 0 0 0-1.6-1.6H6.1A1.6 1.6 0 0 0 4.5 5.6v12.8A1.6 1.6 0 0 0 6.1 20h6.8a1.6 1.6 0 0 0 1.6-1.6v-1.9" />
                <path d="M10 12h10M17 8.8 20.2 12 17 15.2" />
                @break

            @case('plus')
                <path d="M12 5.5v13M5.5 12h13" />
                @break

            @case('edit')
                <path d="M4.5 19.5h4l10-10a2.1 2.1 0 0 0-3-3l-10 10v3Z" />
                <path d="M14.5 5.5l3 3" opacity=".6" />
                @break

            @case('trash')
                <path d="M4.8 7h14.4" />
                <path d="M9.6 7V5.4A1.4 1.4 0 0 1 11 4h2a1.4 1.4 0 0 1 1.4 1.4V7" />
                <path d="M6.6 7l.8 11.6A1.5 1.5 0 0 0 8.9 20h6.2a1.5 1.5 0 0 0 1.5-1.4L17.4 7" />
                <path d="M10.4 10.6v6M13.6 10.6v6" opacity=".55" />
                @break

            @case('image')
                <rect x="3.6" y="5" width="16.8" height="14" rx="2" />
                <circle cx="9" cy="10" r="1.6" />
                <path d="M4.2 17.2 9.4 12l3.4 3.4 2.6-2.4 4.2 4" />
                @break

            @case('upload')
                <path d="M12 16V4.8M8.4 8.4 12 4.8l3.6 3.6" />
                <path d="M4.5 15v3.4A1.6 1.6 0 0 0 6.1 20h11.8a1.6 1.6 0 0 0 1.6-1.6V15" />
                @break

            @case('up')
                <path d="M6.5 14.5 12 9l5.5 5.5" />
                @break

            @case('down')
                <path d="M6.5 9.5 12 15l5.5-5.5" />
                @break

            @case('right')
                <path d="M14.5 6.5 8 12l6.5 5.5" />
                @break

            @case('search')
                <circle cx="10.8" cy="10.8" r="6.2" />
                <path d="M15.4 15.4 20 20" />
                @break

            @case('filter')
                <path d="M4 6.5h16M7 12h10M10 17.5h4" />
                @break

            @case('check')
                <path d="M5 12.8 9.4 17 19 6.8" />
                @break

            @case('close')
                <path d="M6.6 6.6l10.8 10.8M17.4 6.6 6.6 17.4" />
                @break

            @case('grip')
                <g fill="currentColor" stroke="none">
                    <circle cx="9" cy="6.5" r="1.3" /><circle cx="15" cy="6.5" r="1.3" />
                    <circle cx="9" cy="12" r="1.3" /><circle cx="15" cy="12" r="1.3" />
                    <circle cx="9" cy="17.5" r="1.3" /><circle cx="15" cy="17.5" r="1.3" />
                </g>
                @break

            @case('price')
                <path d="M12.6 3.8H19a1.2 1.2 0 0 1 1.2 1.2v6.4a1.2 1.2 0 0 1-.35.85l-8 8a1.2 1.2 0 0 1-1.7 0l-6.4-6.4a1.2 1.2 0 0 1 0-1.7l8-8a1.2 1.2 0 0 1 .85-.35Z" />
                <circle cx="16.2" cy="7.8" r="1.5" />
                @break

            @case('warning')
                <path d="M12 4.4 21 19.6H3L12 4.4Z" />
                <path d="M12 10v4.2" />
                <circle cx="12" cy="17" r=".95" fill="currentColor" stroke="none" />
                @break

            @case('hidden')
                <path d="M4 4l16 16" />
                <path d="M9.6 6.9A9.5 9.5 0 0 1 12 6.6c6 0 9.4 5.4 9.4 5.4a17 17 0 0 1-2.9 3.4" />
                <path d="M6.4 8.3A16.8 16.8 0 0 0 2.6 12S6 17.4 12 17.4c1 0 1.9-.15 2.7-.4" />
                <path d="M10.2 10.3a2.6 2.6 0 0 0 3.5 3.5" />
                @break

            @case('sparkle')
                <path d="M12 3.5q1 8.5 8.5 9.5-7.5 1-8.5 9.5-1-8.5-8.5-9.5 7.5-1 8.5-9.5Z" />
                @break

            {{-- Map pin, for the Balad/Neshan addresses. --}}
            @case('pin')
                <path d="M12 21c-4.2-4.4-6.3-7.7-6.3-10.4a6.3 6.3 0 0 1 12.6 0C18.3 13.3 16.2 16.6 12 21Z" />
                <circle cx="12" cy="10.3" r="2.4" />
                @break

            @default
                <circle cx="12" cy="12" r="7.5" />
        @endswitch
    </g>
</svg>
