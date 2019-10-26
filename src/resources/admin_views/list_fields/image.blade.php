@if ($item->$field_name != null && $item->$field_name != '')
	<img src="{!! Croppa::url_resize(
		'uploads/' . $route_prefix . '/' . $item->$field_name,
		array_key_exists('width', $field) ? $field['width'] : 180
	) !!}" />
@endif
