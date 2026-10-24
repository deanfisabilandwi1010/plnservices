@props(['name' => 'zap'])
{{-- Lucide icon by name; falls back to "zap" so admin-entered names never break a page. --}}
@php
    try {
        $svg = svg('lucide-'.($name ?: 'zap'), $attributes->get('class', ''), $attributes->except('class')->getAttributes());
    } catch (\Throwable) {
        $svg = svg('lucide-zap', $attributes->get('class', ''));
    }
@endphp
{!! $svg->toHtml() !!}
