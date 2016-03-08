<tr class="top aligned field-single-line-text">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field" data-name="field[{{ $id_language }}][{{ $field['name'] }}]">
			<input type="text" name="field[{{ $id_language }}][{{ $field['name'] }}]" value="{{ $field['value'] }}"
				data-name="{{ $field['name'] }}" 
				placeholder="{{ array_key_exists('placeholder', $field) ? trans($field['placeholder']) : '' }}" 
				class="{{ array_key_exists('required', $field) && $field['required'] === true ? 
					'validation-required' : '' }}" />
			<div class="error-label ui pointing red basic label"></div>
			
			@if (array_key_exists('note', $field) && $field['note'] != '')
				<div class="ui pointing label">{{ trans($field['note']) }}</div>
			@endif
		</div>
	</td>
</tr>
