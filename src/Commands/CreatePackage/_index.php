<h1><?php echo $package_name; ?></h1>

@foreach ($items as $item)
	<div>
		<h2><a href="{{ route('<?php echo $route_prefix; ?>::slug::' . $item->slug) }}">{{ $item->{$item->getKeyName()} }}</a></h2>
		<strong>{{ date('d.m.Y', strtotime($item->updated_at)) }}</strong>
	</div>
@endforeach
