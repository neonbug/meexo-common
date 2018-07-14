<div class="ui inverted vertical menu left sticky fluid borderless">
	<?php
	$route = route('admin-home');
	$active = ($route == Request::url());
	?>
	<a class="item {{ ($active ? 'active' : '') }}" href="{{ $route }}">
		<i class="home icon"></i>
		{{ trans('common::admin.menu.dashboard') }}
	</a>
	
	@if (isSet($compact_admin_menu) && $compact_admin_menu === true)
		@foreach ($menu_items as $group_item)
			<?php
			if (sizeof($group_item['items']) == 0) continue;
			
			$route = route($group_item['items'][0]['route']);
			$active = ($route == Request::url());
			?>
			
			<a class="header item {{ ($active ? 'active' : '') }}" href="{{ $route }}">
				<i class="{{ $group_item['icon'] }} icon"></i>
				{{ trans($group_item['title'] . '::admin.menu.main') }}
			</a>
		@endforeach
	@else
		@foreach ($menu_items as $group_item)
			<div class="header item">
				<i class="{{ $group_item['icon'] }} icon"></i>
				{{ trans($group_item['title'] . '::admin.menu.main') }}
			</div>
			@foreach ($group_item['items'] as $menu_item)
				<?php
				$route = route($menu_item['route']);
				$active = ($route == Request::url());
				?>
				
				<a class="item {{ ($active ? 'active' : '') }} level-two" href="{{ $route }}">
					{{ trans($group_item['title'] . '::admin.menu.' . $menu_item['title']) }}
				</a>
			@endforeach
		@endforeach
	@endif
</div>
