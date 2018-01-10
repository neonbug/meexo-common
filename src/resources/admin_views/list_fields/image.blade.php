@if ($item->$field_name != null && $item->$field_name != '')
	<img src="{!! Croppa::url_resize('uploads/' . $route_prefix . '/' . $item->$field_name, 180) !!}" />
@endif
