@php
	$selectedAiList = $selectedAiList ?: old('openaiItems', []);

	 if (\App\Helpers\Classes\MarketplaceHelper::isRegistered('ai-music-pro')) {
		 $openAiList->push(
			(object)[
				'id' => 999998,
				'slug' => 'ext_ai_music_pro',
				'title' => 'AI Music Pro',
				'filters' => 'music',
			],
			(object)[
				'id' => 999999,
				'slug' => 'ai_presentation',
				'title' => 'AI Presentation',
				'filters' => 'presentation',
			]
		);
		 if (in_array('ext_ai_music_pro', $selectedAiList, true)) {
			$checkedGroups['music'] = true;
		 }
		 if (in_array('ai_presentation', $selectedAiList, true)) {
			$checkedGroups['presentation'] = true;
		 }
	 }
@endphp

@extends('panel.layout.settings')
@section('title', __('Trial Feature'))

@section('settings')
	<form
		class="flex flex-col gap-5"
		action="{{ route('dashboard.admin.finance.free.feature') }}"
		method="POST"
	>
		@csrf
		@foreach ($openAiList->groupBy('filters') as $key => $items)
			<x-forms.input
				class:container="mb-4"
				id="{{ $key }}"
				data-filter="check"
				type="checkbox"
				label="{{ ucfirst($key) }}"
				name="display_word"
				:checked="isset($checkedGroups[$key]) && $checkedGroups[$key]"
				switcher
			/>
			<div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3">
				@foreach ($items as $keyItem => $item)
					<x-forms.input
						class:container="h-full bg-input-background"
						class:label="w-full border h-full rounded px-3 py-4 hover:bg-foreground/5 transition-colors"
						class="checked-item"
						id="flex_check_{{ $item->id }}"
						data-filter="{{ $key }}"
						:checked="in_array($item->slug, $selectedAiList)"
						type="checkbox"
						name="openaiItems[]"
						value="{{ $item->slug }}"
						label="{{ $item->title }}"
						custom
					/>
				@endforeach
			</div>
		@endforeach
		<x-button
			size="lg"
			type="submit"
		>
			{{ __('Save') }}
		</x-button>
	</form>
@endsection
@push('script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {

			$('[data-filter="check"]').on('change', function () {

				if ($(this).is(':checked')) {
					$('[data-filter="' + $(this).attr('id') + '"]').prop('checked', true);
				} else {
					$('[data-filter="' + $(this).attr('id') + '"]').prop('checked', false);
				}
			});
		});
	</script>
@endpush
