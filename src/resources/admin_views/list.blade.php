@extends('common::admin')

@section('head')
	<script src="{{ cached_asset('vendor/common/admin_assets/js/app/list.js') }}"></script>
	<script type="text/javascript">
	var trans = {
		errors: {
			slug_empty: {!! json_encode(trans('common::admin.add.errors.slug-empty')) !!}, 
			slug_already_exists: {!! json_encode(trans('common::admin.add.errors.slug-already-exists')) !!}
		}, 
		messages: {
			deleted: {!! json_encode(trans('common::admin.add.messages.deleted')) !!}
		}
	};
	var config = {
		delete_route: {!! json_encode($delete_route === null || !Route::has($delete_route) ? 
			null : route($delete_route)) !!}
	};
	
	list.init(trans, config);
	
	@php
		$col_count = sizeof(array_keys($fields));
		if ($edit_route != null && Route::has($edit_route)) {
			$col_count++;
		}
		if ($delete_route != null && Route::has($delete_route)) {
			$col_count++;
		}
		if (Route::has($package_name . '::admin::save-item-order')) {
			$col_count++;
		}
	@endphp
	
	@if (Route::has($package_name . '::admin::save-item-order'))
		function reinitItemsSortable()
		{
			$('.items-table > tbody').sortable({
				handle: '.items-order-handle',
				forcePlaceholderSize: true,
				placeholder: '<tr style="display: table-row;"><td colspan="{{ $col_count }}" style="background-color: #f2711c2b;"></td></tr>',
				items: 'tr',
			});
			$('.items-table > tbody').on('sortupdate', function() {
				$.post({!! json_encode(route($package_name . '::admin::save-item-order')) !!}, {
					ids: $('.items-table > tbody > tr').toArray().map(item => item.dataset.idItem).join(','),
				}, function(data) {});
			});
		}

		$(document).ready(function() {
			reinitItemsSortable();
		});
	@endif
	</script>
	
	<?php
	$unique_types = [];
	
	foreach ($fields as $field_name=>$field) {
		$type = (stripos($field['type'], '::') !== false ? $field['type'] : 
			'common_admin::list_fields.' . $field['type']);
		if (!in_array($type, $unique_types)) $unique_types[] = $type;
	}
	?>
	
	@foreach ($unique_types as $type)
		@if (view()->exists($type . '--head'))
			@include($type . '--head', [ 
				'package_name' => $package_name, 
				'route_prefix' => $route_prefix, 
			])
		@endif
	@endforeach
	
	<style type="text/css">
	.ui.button.items-order-handle {
		cursor: move;
	}
	</style>
@stop

@section('content')
	@if (Route::has($add_route))
		<a href="{{ route($add_route) }}" class="ui large label grey">
			<i class="plus icon"></i> {{ trans('common::admin.list.add-action') }}
		</a>
	@endif
	
	@foreach (($top_routes ?? []) as $top_route)
		<a href="{{ $top_route['route'] }}" class="ui large label grey">
			<i class="{{ $top_route['icon'] }}"></i> {{ $top_route['title'] }}
		</a>
	@endforeach

	<table class="ui striped padded table unstackable items-table">
		<thead>
			<tr>
				@if ($edit_route != null && Route::has($edit_route))
					<th>{{ trans('common::admin.list.edit-action') }}</th>
				@endif
				@if ($delete_route != null && Route::has($delete_route))
					<th>{{ trans('common::admin.list.delete-action') }}</th>
				@endif
				@foreach ($fields as $field_name=>$field)
					<?php
					$cls = (!array_key_exists('important', $field) || $field['important'] === true ? 
						'' : 'desktop-only');
					?>
					<th class="{{ $cls }}">{{ trans($package_name . '::admin.list.field-title.' . $field_name) }}</th>
				@endforeach
				@if (Route::has($package_name . '::admin::save-item-order'))
					<th>{{ trans('common::admin.list.field-title.ord') }}</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@foreach ($items as $item)
				<tr data-id-item="{{ $item->{$item->getKeyName()} }}">
					@if ($edit_route != null && Route::has($edit_route))
						<td class="collapsing">
							<a href="{{ route($edit_route, [ $item->{$item->getKeyName()} ]) }}" 
								class="ui label blue only-icon"><i class="write icon"></i></a>
						</td>
					@endif
					@if ($delete_route != null && Route::has($delete_route))
						<td class="collapsing">
							<a href="#" class="ui label red only-icon delete-item" 
								data-id-item="{{ $item->{$item->getKeyName()} }}"><i class="trash icon"></i></a>
						</td>
					@endif
					@foreach ($fields as $field_name=>$field)
						<?php
						$cls = (!array_key_exists('important', $field) || $field['important'] === true ? 
						'' : 'desktop-only');
						
						$type = (stripos($field['type'], '::') !== false ? $field['type'] : 
							'common_admin::list_fields.' . $field['type']);
						?>
						<td class="{{ $cls }}">
							@include($type, 
								[ 'item' => $item, 'field_name' => $field_name, 'field' => $field, 
									'route_prefix' => $route_prefix ])
						</td>
					@endforeach
					@if (Route::has($package_name . '::admin::save-item-order'))
						<td>
							<button class="ui icon button items-order-handle" type="button" draggable="true">&#x21C5;</button>
						</td>
					@endif
				</tr>
			@endforeach
		</tbody>
	</table>
	<div class="ui small modal delete-item-modal">
		<div class="content">
			{{ trans('common::admin.list.delete-confirmation-message') }}
		</div>
		<div class="actions">
			<div class="ui black deny button">
				{{ trans('common::admin.list.delete-confirmation-deny') }}
			</div>
			<div class="ui ok right labeled icon button red">
				{{ trans('common::admin.list.delete-confirmation-confirm') }}
				<i class="checkmark icon"></i>
			</div>
		</div>
	</div>
@stop
