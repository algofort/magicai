@extends('panel.layout.app', [
    'disable_header' => true,
    'disable_titlebar' => true,
    'disable_navbar' => true,
    'disable_footer' => true,
    'disable_floating_menu' => true,
    'disable_mobile_bottom_menu' => true,
    'disable_tblr' => true,
    'layout_wide' => true,
])

@push('css')
    <link
        rel="stylesheet"
        href="{{ custom_theme_url('/assets/libs/picmo/picmo.min.css') }}"
    />
    <style>
        .lqd-emoji-picker .picmo__picker.picmo__picker {
            --background-color: hsl(var(--background));
            --secondary-background-color: hsl(var(--background));
            --border-color: hsl(0 0% 0% / 5%);
            --search-background-color: hsl(0 0% 0% / 5%);
            --search-height: 40px;
            --accent-color: hsl(var(--primary));
            --ui-font-size: 14px;
            --emoji-size: 1.75rem;
        }

        .lqd-emoji-picker .picmo__picker .picmo__searchContainer .picmo__searchField {
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            background-color: var(--search-background-color) !important;
        }

        .lqd-emoji-picker .picmo__picker .picmo__emojiButton {
            border-radius: 6px;
        }

        .lqd-emoji-picker .picmo__picker .picmo__preview {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div
        class="lqd-generator-v2 group/generator [--editor-bb-h:40px] [--editor-tb-h:50px] [--sidebar-w:min(440px,90vw)]"
        :class="{ 'lqd-generator-sidebar-collapsed': sideNavCollapsed }"
        x-data="generatorV2"
    >
        @include('panel.user.generator.components.sidebar')
        @include('panel.user.generator.components.editor')
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/picmo/picmo.min.js') }}"></script>
@endpush
