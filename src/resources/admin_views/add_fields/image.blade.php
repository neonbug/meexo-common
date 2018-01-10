<tr class="top aligned field-image">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field" data-name="field[{{ $id_language }}][{{ $field['name'] }}]">
			<input type="file" name="field[{{ $id_language }}][{{ $field['name'] }}]" data-name="{{ $field['name'] }}"
				class="{{ array_key_exists('required', $field) && $field['required'] === true ? 
					'validation-image-required' : '' }}" />
			<div class="error-label ui pointing red basic label"></div>
			
			@if (array_key_exists('note', $field) && $field['note'] != '')
				<div><div class="ui pointing label">{{ trans($field['note']) }}</div></div>
			@endif
		</div>
	</td>
</tr>

@if ($field['value'] != null && $field['value'] != '')
	<tr>
		<td class="collapsing">
		</td>
		<td>
			<div class="field">
				<div class="ui card">
					<div class="content">
						<div class="header">{{ trans('common::admin.add.current-image-title') }}</div>
					</div>
					<a class="image" href="{!! Croppa::url_resize('uploads/' . $prefix . '/' . $field['value']) !!}" 
						target="_blank">
						<img src="{!! Croppa::url_resize('uploads/' . $prefix . '/' . $field['value'], 290) !!}" />
					</a>
					<div class="content">
						<div class="description">
							{{ trans('common::admin.add.current-image-description') }}
						</div>
					</div>
					<div class="extra content">
						<div class="ui checkbox">
							<input type="checkbox" name="field[{{ $id_language }}][remove-file][{{ $field['name'] }}]" 
								value="true" class="current-image-remove" data-name="{{ $field['name'] }}" />
							<label>{{ trans('common::admin.add.current-image-remove') }}</label>
						</div>
					</div>
				</div>
			</div>
		</td>
	</tr>
@endif
