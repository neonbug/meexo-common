<tr class="top aligned field-single-line-text">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field">
			<select class="ui search dropdown" name="field[{{ $id_language }}][{{ $field['name'] }}]">
				@if (!array_key_exists('required', $field) || $field['required'] === false)
					<option value="">{{ trans('common::admin.add.dropdown.empty-value') }}</option>
				@endif
				@foreach ($field['values'] as $key=>$title)
					<option value="{{ $key }}" 
						{{ array_key_exists('value', $field) && $key == $field['value'] ? 'selected' : '' }}>
						{{ $title }}
					</option>
				@endforeach
			</select>
		</div>
	</td>
</tr>
