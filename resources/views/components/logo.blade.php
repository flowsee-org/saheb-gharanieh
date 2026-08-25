{{-- The Saheb Gharaniyeh mark. Two files, one per theme: the artwork is
     two-tone, so the calligraphy inside the cartouche is transparent and shows
     the page through it — a single file cannot serve both themes. Which one is
     painted is decided in CSS off html[data-theme], see .sg-logo in app.css.

     Painted as a background rather than an <img> so only the active theme's
     file is fetched, and swapping themes is a repaint rather than a request.

     Pass label="" where the mark sits inside something that already names it —
     a link with its own aria-label, say — so a screen reader hears the name
     once instead of twice. --}}
@props(['label' => 'کافه صاحبقرانیه'])

@if (filled($label))
    <span role="img" aria-label="{{ $label }}" {{ $attributes->class('sg-logo') }}></span>
@else
    <span aria-hidden="true" {{ $attributes->class('sg-logo') }}></span>
@endif
