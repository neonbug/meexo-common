<tr class="top aligned field-integer">
	<th class="collapsing">
		{{ $field_title }}
		@if (array_key_exists('required', $field) && $field['required'] === true)
			<i class="orange small asterisk icon" 
				title="{{ trans('common::admin.add.errors.validation.required') }}"></i>
		@endif
	</th>
	<td>
		<div class="field" data-name="field[{{ $id_language }}][{{ $field['name'] }}]">
			<input type="text" name="field[{{ $id_language }}][{{ $field['name'] }}]" 
				value="{{ $field['value'] }}" data-name="{{ $field['name'] }}" 
				class="validation-int {{ array_key_exists('required', $field) && $field['required'] === true ? 
					'validation-required' : '' }}" />
			<div class="error-label ui pointing red basic label"></div>
		</div>
	</td>
</tr>
