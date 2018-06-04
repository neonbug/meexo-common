@if (mb_strlen(trim($item->slug)) == 0)
	<span>/</span>
@else
	<a href="{{ route($route_prefix . '::slug::' . $item->slug) }}">{{ trans('common::admin.list.content-link') }}</a>
@endif
