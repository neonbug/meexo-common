
$p = '{{ $lowercase_package_name }}::admin.';
return [
	$p . 'title.main' => [ 'en' => '{{ $package_name }}' ], 
	$p . 'title.list' => [ 'en' => 'List' ], 
	$p . 'title.add'  => [ 'en' => 'Add' ], 
	$p . 'title.edit' => [ 'en' => 'Edit' ], 
	
	$p . 'menu.main' => [ 'en' => '{{ $package_name }}' ], 
	$p . 'menu.list' => [ 'en' => 'List' ], 
	$p . 'menu.add'  => [ 'en' => 'Add' ], 
	
	$p . 'list.field-title.id_{{ $lowercase_package_name }}' => [ 'en' => 'Id' ], 
	$p . 'list.field-title.title'      => [ 'en' => 'Title' ], 
	$p . 'list.field-title.slug'       => [ 'en' => 'Url' ], 
	$p . 'list.field-title.updated_at' => [ 'en' => 'Updated' ], 
	
	$p . 'add.field-title.title'            => [ 'en' => 'Title' ], 
	$p . 'add.field-title.slug'             => [ 'en' => 'Url' ], 
	$p . 'add.field-title.meta_description' => [ 'en' => 'Meta description (for search engines)' ], 
];
